<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Services\M3UParserService;
use App\Services\M3UImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Exception;

class M3UImportController extends Controller
{
    private M3UParserService $parser;
    private M3UImportService $importService;

    public function __construct(M3UParserService $parser, M3UImportService $importService)
    {
        $this->parser = $parser;
        $this->importService = $importService;
    }

    /**
     * 上传并导入M3U文件
     */
    public function import(Request $request)
    {
        $request->validate([
            'm3u_file' => [
                'required',
                'file',
                'mimes:m3u,m3u8,txt',
                'max:51200', // 50MB
            ],
        ]);

        try {
            $file = $request->file('m3u_file');
            $fileName = $file->getClientOriginalName();
            $fileSize = $file->getSize();

            // 临时存储文件
            $tempPath = $file->store('temp');
            $fullPath = storage_path('app/' . $tempPath);

            // 验证文件并统计频道数
            try {
                $channelCount = $this->parser->countChannels($fullPath);
                
                if ($channelCount > M3UParserService::getMaxChannels()) {
                    Storage::delete($tempPath);
                    return response()->json([
                        'message' => __('messages.m3u_too_many_channels', [
                            'max' => M3UParserService::getMaxChannels(),
                            'count' => $channelCount,
                        ]),
                    ], 422);
                }
            } catch (Exception $e) {
                Storage::delete($tempPath);
                return response()->json([
                    'message' => __('messages.m3u_invalid_format'),
                    'error' => $e->getMessage(),
                ], 422);
            }

            // 执行导入
            $result = $this->importService->import(
                $fullPath,
                $fileName,
                $fileSize,
                $request->user()->id
            );

            // 清理临时文件
            Storage::delete($tempPath);

            return response()->json([
                'message' => __('messages.m3u_import_completed'),
                'result' => [
                    'total_processed' => $result['total_processed'],
                    'imported' => $result['imported'],
                    'skipped' => $result['skipped'],
                    'errors' => $result['errors'],
                    'error_messages' => array_slice($result['error_messages'], 0, 20), // 限制返回的错误消息数量
                    'log_file_path' => $result['log_file_path'],
                ],
            ]);

        } catch (Exception $e) {
            return response()->json([
                'message' => __('messages.m3u_import_failed'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 下载导入日志文件
     */
    public function downloadLog($logId)
    {
        $importLog = \App\Models\ImportLog::findOrFail($logId);

        if (!file_exists($importLog->log_file_path)) {
            return response()->json([
                'message' => __('messages.log_file_not_found'),
            ], 404);
        }

        return response()->download(
            $importLog->log_file_path,
            basename($importLog->log_file_path)
        );
    }

    /**
     * 获取导入历史记录
     */
    public function history(Request $request)
    {
        $perPage = $request->input('per_page', 15);

        $logs = \App\Models\ImportLog::with('creator:id,username')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json($logs);
    }
}
