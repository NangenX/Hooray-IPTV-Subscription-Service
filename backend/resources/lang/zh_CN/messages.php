<?php

return [
    // 认证信息
    'invalid_credentials' => '用户名或密码错误',
    'account_inactive' => '您的账号已被停用',
    'login_success' => '登录成功',
    'logout_success' => '退出成功',
    'current_password_incorrect' => '当前密码不正确',
    'password_changed' => '密码修改成功',

    // 管理员信息
    'admin_created' => '管理员创建成功',
    'admin_updated' => '管理员更新成功',
    'admin_deleted' => '管理员删除成功',
    'cannot_modify_self' => '您不能修改自己的账号',
    'cannot_delete_self' => '您不能删除自己的账号',
    'no_permission' => '您没有权限执行此操作',

    // 频道信息
    'channel_created' => '频道创建成功',
    'channel_updated' => '频道更新成功',
    'channel_deleted' => '频道删除成功',
    'channels_deleted' => '成功删除 :count 个频道',
    'channels_status_updated' => '成功更新 :count 个频道状态',
    'channel_already_exists' => '已存在相同名称和URL的频道',

    // M3U导入信息
    'm3u_import_completed' => 'M3U导入完成',
    'm3u_import_failed' => 'M3U导入失败',
    'm3u_invalid_format' => '无效的M3U文件格式',
    'm3u_too_many_channels' => 'M3U文件包含 :count 个频道，最大支持 :max 个，请分割文件',
    'log_file_not_found' => '日志文件未找到',

    // 通用信息
    'not_found' => '资源未找到',
    'validation_error' => '验证错误',
    'server_error' => '服务器内部错误',
];
