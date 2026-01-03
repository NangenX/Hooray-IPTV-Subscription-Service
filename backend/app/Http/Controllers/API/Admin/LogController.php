<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Services\SystemLogService;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class LogController extends Controller
{
    private SystemLogService $logService;

    public function __construct(SystemLogService $logService)
    {
        $this->logService = $logService;
    }

    /**
     * 获取活动日志列表
     */
    public function index(Request $request)
    {
        $query = Activity::with(['causer', 'subject']);

        // 按日期过滤
        if ($request->has('date_from')) {
            $query->where('created_at', '>=', $request->input('date_from'));
        }

        if ($request->has('date_to')) {
            $query->where('created_at', '<=', $request->input('date_to'));
        }

        // 按操作类型过滤
        if ($request->has('log_name')) {
            $query->where('log_name', $request->input('log_name'));
        }

        // 按操作者过滤
        if ($request->has('causer_id')) {
            $query->where('causer_id', $request->input('causer_id'));
        }

        $perPage = $request->input('per_page', 15);
        $logs = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json($logs);
    }

    /**
     * 导出日志为TXT
     */
    public function export(Request $request)
    {
        $request->validate([
            'date' => 'sometimes|date_format:Y-m-d',
            'module' => 'sometimes|string',
        ]);

        $date = $request->input('date');
        $module = $request->input('module');

        $content = $this->logService->exportToTxt($date, $module);

        $filename = 'system_log_' . ($date ?? date('Y-m-d'));
        $filePath = $this->logService->saveExportedLog($content, $filename);

        return response()->download($filePath, basename($filePath))->deleteFileAfterSend();
    }

    /**
     * 获取导入日志列表
     */
    public function importLogs(Request $request)
    {
        $query = \App\Models\ImportLog::with('creator:id,username');

        if ($request->has('date_from')) {
            $query->where('created_at', '>=', $request->input('date_from'));
        }

        if ($request->has('date_to')) {
            $query->where('created_at', '<=', $request->input('date_to'));
        }

        $perPage = $request->input('per_page', 15);
        $logs = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json($logs);
    }
}
