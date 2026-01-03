<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\Channel;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ChannelController extends Controller
{
    /**
     * 获取频道列表
     */
    public function index(Request $request)
    {
        $query = Channel::query();

        // 搜索
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('tvg_id', 'like', "%{$search}%")
                  ->orWhere('group_title', 'like', "%{$search}%");
            });
        }

        // 按分组过滤
        if ($request->has('group_title')) {
            $query->where('group_title', $request->input('group_title'));
        }

        // 按状态过滤
        if ($request->has('is_active')) {
            $query->where('is_active', $request->input('is_active'));
        }

        // 按语言过滤
        if ($request->has('language')) {
            $query->where('language', $request->input('language'));
        }

        // 排序
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->input('per_page', 15);
        $channels = $query->paginate($perPage);

        return response()->json($channels);
    }

    /**
     * 获取所有分组
     */
    public function groups()
    {
        $groups = Channel::select('group_title')
            ->groupBy('group_title')
            ->orderBy('group_title')
            ->pluck('group_title');

        return response()->json(['groups' => $groups]);
    }

    /**
     * 创建频道
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'stream_url' => 'required|url',
            'description' => 'nullable|string',
            'logo_url' => 'nullable|url',
            'category' => 'nullable|string',
            'language' => 'nullable|string|max:50',
            'country' => 'nullable|string|size:2',
            'group_title' => 'nullable|string|max:255',
            'tvg_id' => 'nullable|string|max:255',
            'quality' => 'nullable|string|max:10',
            'is_active' => 'boolean',
        ]);

        // 检查是否存在相同name+url的频道
        $exists = Channel::where('name', $request->name)
            ->where('stream_url', $request->stream_url)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => __('messages.channel_already_exists'),
            ], 422);
        }

        $channel = Channel::create($request->all());

        return response()->json([
            'message' => __('messages.channel_created'),
            'channel' => $channel,
        ], 201);
    }

    /**
     * 获取单个频道
     */
    public function show($id)
    {
        $channel = Channel::with('packages')->findOrFail($id);

        return response()->json(['channel' => $channel]);
    }

    /**
     * 更新频道
     */
    public function update(Request $request, $id)
    {
        $channel = Channel::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'stream_url' => 'sometimes|required|url',
            'description' => 'nullable|string',
            'logo_url' => 'nullable|url',
            'category' => 'nullable|string',
            'language' => 'nullable|string|max:50',
            'country' => 'nullable|string|size:2',
            'group_title' => 'nullable|string|max:255',
            'tvg_id' => 'nullable|string|max:255',
            'quality' => 'nullable|string|max:10',
            'is_active' => 'boolean',
        ]);

        $channel->update($request->all());

        return response()->json([
            'message' => __('messages.channel_updated'),
            'channel' => $channel,
        ]);
    }

    /**
     * 删除频道
     */
    public function destroy($id)
    {
        $channel = Channel::findOrFail($id);
        $channel->delete();

        return response()->json([
            'message' => __('messages.channel_deleted'),
        ]);
    }

    /**
     * 批量删除频道
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:channels,id',
        ]);

        $count = Channel::whereIn('id', $request->ids)->delete();

        return response()->json([
            'message' => __('messages.channels_deleted', ['count' => $count]),
        ]);
    }

    /**
     * 批量更新频道状态
     */
    public function bulkUpdateStatus(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:channels,id',
            'is_active' => 'required|boolean',
        ]);

        $count = Channel::whereIn('id', $request->ids)
            ->update(['is_active' => $request->is_active]);

        return response()->json([
            'message' => __('messages.channels_status_updated', ['count' => $count]),
        ]);
    }
}
