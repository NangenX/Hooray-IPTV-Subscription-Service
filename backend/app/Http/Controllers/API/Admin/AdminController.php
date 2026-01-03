<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    /**
     * 获取管理员列表
     */
    public function index(Request $request)
    {
        $query = Admin::with('creator:id,username');

        // 搜索
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // 按角色过滤
        if ($request->has('role')) {
            $query->where('role', $request->input('role'));
        }

        // 按状态过滤
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        $perPage = $request->input('per_page', 15);
        $admins = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json($admins);
    }

    /**
     * 创建管理员
     */
    public function store(Request $request)
    {
        // 只有super_admin可以创建管理员
        if (!$request->user()->isSuperAdmin() && $request->input('role') === 'super_admin') {
            return response()->json([
                'message' => __('messages.no_permission'),
            ], 403);
        }

        $request->validate([
            'username' => 'required|string|max:255|unique:admins',
            'email' => 'required|email|max:255|unique:admins',
            'password' => 'required|string|min:8|confirmed',
            'role' => ['required', Rule::in(['super_admin', 'admin', 'moderator'])],
            'status' => ['sometimes', Rule::in(['active', 'inactive'])],
        ]);

        $admin = Admin::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'status' => $request->input('status', 'active'),
            'created_by' => $request->user()->id,
        ]);

        return response()->json([
            'message' => __('messages.admin_created'),
            'admin' => $admin,
        ], 201);
    }

    /**
     * 获取单个管理员
     */
    public function show($id)
    {
        $admin = Admin::with(['creator:id,username', 'createdAdmins'])->findOrFail($id);

        return response()->json(['admin' => $admin]);
    }

    /**
     * 更新管理员
     */
    public function update(Request $request, $id)
    {
        $admin = Admin::findOrFail($id);

        // 不能修改自己的角色和状态
        if ($admin->id === $request->user()->id) {
            return response()->json([
                'message' => __('messages.cannot_modify_self'),
            ], 422);
        }

        // 只有super_admin可以修改其他super_admin
        if ($admin->role === 'super_admin' && !$request->user()->isSuperAdmin()) {
            return response()->json([
                'message' => __('messages.no_permission'),
            ], 403);
        }

        $request->validate([
            'username' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('admins')->ignore($id)],
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('admins')->ignore($id)],
            'password' => 'sometimes|nullable|string|min:8|confirmed',
            'role' => ['sometimes', Rule::in(['super_admin', 'admin', 'moderator'])],
            'status' => ['sometimes', Rule::in(['active', 'inactive'])],
        ]);

        $data = $request->only(['username', 'email', 'role', 'status']);
        
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $admin->update($data);

        return response()->json([
            'message' => __('messages.admin_updated'),
            'admin' => $admin,
        ]);
    }

    /**
     * 删除管理员
     */
    public function destroy(Request $request, $id)
    {
        $admin = Admin::findOrFail($id);

        // 不能删除自己
        if ($admin->id === $request->user()->id) {
            return response()->json([
                'message' => __('messages.cannot_delete_self'),
            ], 422);
        }

        // 只有super_admin可以删除其他super_admin
        if ($admin->role === 'super_admin' && !$request->user()->isSuperAdmin()) {
            return response()->json([
                'message' => __('messages.no_permission'),
            ], 403);
        }

        $admin->delete();

        return response()->json([
            'message' => __('messages.admin_deleted'),
        ]);
    }
}
