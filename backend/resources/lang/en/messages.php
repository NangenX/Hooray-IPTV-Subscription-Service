<?php

return [
    // Auth messages
    'invalid_credentials' => 'Invalid username or password',
    'account_inactive' => 'Your account has been deactivated',
    'login_success' => 'Login successful',
    'logout_success' => 'Logout successful',
    'current_password_incorrect' => 'Current password is incorrect',
    'password_changed' => 'Password changed successfully',

    // Admin messages
    'admin_created' => 'Admin created successfully',
    'admin_updated' => 'Admin updated successfully',
    'admin_deleted' => 'Admin deleted successfully',
    'cannot_modify_self' => 'You cannot modify your own account',
    'cannot_delete_self' => 'You cannot delete your own account',
    'no_permission' => 'You do not have permission to perform this action',

    // Channel messages
    'channel_created' => 'Channel created successfully',
    'channel_updated' => 'Channel updated successfully',
    'channel_deleted' => 'Channel deleted successfully',
    'channels_deleted' => ':count channels deleted successfully',
    'channels_status_updated' => ':count channels status updated successfully',
    'channel_already_exists' => 'A channel with the same name and URL already exists',

    // M3U Import messages
    'm3u_import_completed' => 'M3U import completed',
    'm3u_import_failed' => 'M3U import failed',
    'm3u_invalid_format' => 'Invalid M3U file format',
    'm3u_too_many_channels' => 'M3U file contains :count channels, maximum is :max. Please split the file.',
    'log_file_not_found' => 'Log file not found',

    // General messages
    'not_found' => 'Resource not found',
    'validation_error' => 'Validation error',
    'server_error' => 'Internal server error',
];
