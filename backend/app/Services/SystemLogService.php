<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SystemLogService
{
    /**
     * 记录系统操作日志
     *
     * @param string $action
     * @param string $module
     * @param array $details
     * @param int|null $userId
     * @param string $userType
     * @return void
     */
    public function log(
        string $action,
        string $module,
        array $details = [],
        ?int $userId = null,
        string $userType = 'admin'
    ): void {
        $logEntry = [
            'timestamp' => now()->toDateTimeString(),
            'action' => $action,
            'module' => $module,
            'user_id' => $userId,
            'user_type' => $userType,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'details' => $details,
        ];

        Log::channel('daily')->info($action, $logEntry);
    }

    /**
     * 导出系统日志为TXT格式
     *
     * @param string|null $date
     * @param string|null $module
     * @return string
     */
    public function exportToTxt(?string $date = null, ?string $module = null): string
    {
        $date = $date ?? now()->format('Y-m-d');
        $logFile = storage_path("logs/laravel-{$date}.log");

        if (!file_exists($logFile)) {
            return "No logs found for date: {$date}";
        }

        $content = file_get_contents($logFile);
        
        // 如果指定了模块，过滤日志
        if ($module) {
            $lines = explode(PHP_EOL, $content);
            $filteredLines = array_filter($lines, function($line) use ($module) {
                return str_contains($line, "\"module\":\"{$module}\"");
            });
            $content = implode(PHP_EOL, $filteredLines);
        }

        return $content;
    }

    /**
     * 保存导出的日志到临时文件
     *
     * @param string $content
     * @param string $filename
     * @return string
     */
    public function saveExportedLog(string $content, string $filename): string
    {
        $exportDir = storage_path('logs/exports');
        
        if (!file_exists($exportDir)) {
            mkdir($exportDir, 0755, true);
        }

        $timestamp = now()->format('Y-m-d_H-i-s');
        $exportFileName = "{$filename}_{$timestamp}.txt";
        $exportPath = "{$exportDir}/{$exportFileName}";

        file_put_contents($exportPath, $content);

        return $exportPath;
    }

    /**
     * 清理过期的导出日志文件
     *
     * @param int $days
     * @return int
     */
    public function cleanupExportedLogs(int $days = 7): int
    {
        $exportDir = storage_path('logs/exports');
        
        if (!file_exists($exportDir)) {
            return 0;
        }

        $files = glob($exportDir . '/*.txt');
        $deleted = 0;
        $cutoffTime = now()->subDays($days)->timestamp;

        foreach ($files as $file) {
            if (filemtime($file) < $cutoffTime) {
                unlink($file);
                $deleted++;
            }
        }

        return $deleted;
    }
}
