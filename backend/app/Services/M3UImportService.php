<?php

namespace App\Services;

use App\Models\Channel;
use App\Models\ImportLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;

class M3UImportService
{
    private M3UParserService $parser;
    private SystemLogService $logService;
    private const BATCH_SIZE = 500;

    public function __construct(M3UParserService $parser, SystemLogService $logService)
    {
        $this->parser = $parser;
        $this->logService = $logService;
    }

    /**
     * 导入M3U文件到数据库
     *
     * @param string $filePath
     * @param string $fileName
     * @param int $fileSize
     * @param int $adminId
     * @return array
     */
    public function import(string $filePath, string $fileName, int $fileSize, int $adminId): array
    {
        $stats = [
            'total_processed' => 0,
            'imported' => 0,
            'skipped' => 0,
            'errors' => 0,
            'error_messages' => [],
            'duplicate_channels' => [],
        ];

        $logFileName = $this->createLogFile($fileName);
        $logFilePath = storage_path("logs/imports/{$logFileName}");

        try {
            $this->writeLog($logFilePath, "=== M3U Import Started ===");
            $this->writeLog($logFilePath, "File: {$fileName}");
            $this->writeLog($logFilePath, "Size: " . $this->formatBytes($fileSize));
            $this->writeLog($logFilePath, "Admin ID: {$adminId}");
            $this->writeLog($logFilePath, "Time: " . now()->toDateTimeString());
            $this->writeLog($logFilePath, "");

            $batch = [];
            $lineNumber = 0;

            foreach ($this->parser->parse($filePath) as $channelData) {
                $stats['total_processed']++;
                $lineNumber++;

                // 验证频道数据
                if (!$this->validateChannelData($channelData)) {
                    $stats['errors']++;
                    $errorMsg = "Line {$lineNumber}: Invalid channel data - " . $channelData['name'];
                    $stats['error_messages'][] = $errorMsg;
                    $this->writeLog($logFilePath, "[ERROR] {$errorMsg}");
                    continue;
                }

                // 准备数据库插入数据
                $insertData = $this->prepareChannelData($channelData);
                $batch[] = $insertData;

                // 批量插入
                if (count($batch) >= self::BATCH_SIZE) {
                    $result = $this->insertBatch($batch, $logFilePath);
                    $stats['imported'] += $result['imported'];
                    $stats['skipped'] += $result['skipped'];
                    $stats['duplicate_channels'] = array_merge($stats['duplicate_channels'], $result['duplicates']);
                    $batch = [];
                }
            }

            // 插入剩余数据
            if (!empty($batch)) {
                $result = $this->insertBatch($batch, $logFilePath);
                $stats['imported'] += $result['imported'];
                $stats['skipped'] += $result['skipped'];
                $stats['duplicate_channels'] = array_merge($stats['duplicate_channels'], $result['duplicates']);
            }

            // 写入摘要
            $this->writeLog($logFilePath, "");
            $this->writeLog($logFilePath, "=== Import Summary ===");
            $this->writeLog($logFilePath, "Total Processed: {$stats['total_processed']}");
            $this->writeLog($logFilePath, "Successfully Imported: {$stats['imported']}");
            $this->writeLog($logFilePath, "Skipped (Duplicates): {$stats['skipped']}");
            $this->writeLog($logFilePath, "Errors: {$stats['errors']}");
            $this->writeLog($logFilePath, "Completion Time: " . now()->toDateTimeString());

        } catch (Exception $e) {
            $stats['errors']++;
            $errorMsg = "Fatal Error: " . $e->getMessage();
            $stats['error_messages'][] = $errorMsg;
            $this->writeLog($logFilePath, "[FATAL ERROR] {$errorMsg}");
            Log::error('M3U Import Failed', [
                'file' => $fileName,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        // 保存导入日志到数据库
        $this->saveImportLog($fileName, $fileSize, $stats, $logFilePath, $adminId);

        // 返回结果包含日志文件路径
        $stats['log_file_path'] = "logs/imports/{$logFileName}";
        
        return $stats;
    }

    /**
     * 批量插入频道数据
     *
     * @param array $batch
     * @param string $logFilePath
     * @return array
     */
    private function insertBatch(array $batch, string $logFilePath): array
    {
        $imported = 0;
        $skipped = 0;
        $duplicates = [];

        try {
            // 使用 insertOrIgnore 跳过重复（基于 name + stream_url 唯一键）
            foreach ($batch as $channelData) {
                // 检查是否存在相同 name + stream_url 的频道
                $exists = Channel::where('name', $channelData['name'])
                    ->where('stream_url', $channelData['stream_url'])
                    ->exists();

                if ($exists) {
                    $skipped++;
                    $duplicates[] = $channelData['name'];
                    $this->writeLog($logFilePath, "[SKIPPED] Duplicate: {$channelData['name']}");
                } else {
                    Channel::create($channelData);
                    $imported++;
                }
            }

        } catch (Exception $e) {
            Log::error('Batch Insert Failed', [
                'error' => $e->getMessage(),
                'batch_size' => count($batch),
            ]);
            $this->writeLog($logFilePath, "[ERROR] Batch insert failed: " . $e->getMessage());
        }

        return [
            'imported' => $imported,
            'skipped' => $skipped,
            'duplicates' => $duplicates,
        ];
    }

    /**
     * 验证频道数据
     *
     * @param array $data
     * @return bool
     */
    private function validateChannelData(array $data): bool
    {
        return !empty($data['name']) && 
               !empty($data['stream_url']) &&
               filter_var($data['stream_url'], FILTER_VALIDATE_URL) !== false;
    }

    /**
     * 准备频道数据用于数据库插入
     *
     * @param array $data
     * @return array
     */
    private function prepareChannelData(array $data): array
    {
        return [
            'name' => $data['name'],
            'stream_url' => $data['stream_url'],
            'logo_url' => $data['logo_url'] ?? null,
            'tvg_id' => $data['tvg_id'] ?? null,
            'tvg_name' => $data['tvg_name'] ?? null,
            'tvg_logo' => $data['tvg_logo'] ?? null,
            'group_title' => $data['group_title'] ?? 'Uncategorized',
            'category' => $data['category'] ?? null,
            'country' => $data['country'] ?? null,
            'language' => $data['language'] ?? null,
            'is_active' => true,
            'sort_order' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * 创建日志文件
     *
     * @param string $fileName
     * @return string
     */
    private function createLogFile(string $fileName): string
    {
        $logDir = storage_path('logs/imports');
        
        if (!file_exists($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $timestamp = now()->format('Y-m-d_H-i-s');
        $baseName = pathinfo($fileName, PATHINFO_FILENAME);
        
        return "{$baseName}_{$timestamp}.txt";
    }

    /**
     * 写入日志文件
     *
     * @param string $filePath
     * @param string $message
     * @return void
     */
    private function writeLog(string $filePath, string $message): void
    {
        file_put_contents($filePath, $message . PHP_EOL, FILE_APPEND);
    }

    /**
     * 保存导入日志到数据库
     *
     * @param string $fileName
     * @param int $fileSize
     * @param array $stats
     * @param string $logFilePath
     * @param int $adminId
     * @return void
     */
    private function saveImportLog(
        string $fileName,
        int $fileSize,
        array $stats,
        string $logFilePath,
        int $adminId
    ): void {
        try {
            ImportLog::create([
                'file_name' => $fileName,
                'file_size' => $fileSize,
                'total_processed' => $stats['total_processed'],
                'imported' => $stats['imported'],
                'skipped' => $stats['skipped'],
                'errors' => $stats['errors'],
                'log_file_path' => $logFilePath,
                'error_details' => count($stats['error_messages']) > 0 ? [
                    'messages' => array_slice($stats['error_messages'], 0, 100), // 限制错误消息数量
                    'duplicates_count' => count($stats['duplicate_channels']),
                ] : null,
                'created_by' => $adminId,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to save import log', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * 格式化字节大小
     *
     * @param int $bytes
     * @return string
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
