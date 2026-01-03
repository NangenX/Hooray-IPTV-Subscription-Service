<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Admin\AuthController;
use App\Http\Controllers\API\Admin\AdminController;
use App\Http\Controllers\API\Admin\ChannelController;
use App\Http\Controllers\API\Admin\M3UImportController;
use App\Http\Controllers\API\Admin\LogController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::post('/admin/login', [AuthController::class, 'login']);

// Protected admin routes
Route::middleware(['auth:sanctum'])->prefix('admin')->group(function () {
    
    // Auth routes
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);

    // Admin management
    Route::apiResource('admins', AdminController::class);

    // Channel management
    Route::get('/channels/groups', [ChannelController::class, 'groups']);
    Route::post('/channels/bulk-delete', [ChannelController::class, 'bulkDelete']);
    Route::post('/channels/bulk-update-status', [ChannelController::class, 'bulkUpdateStatus']);
    Route::apiResource('channels', ChannelController::class);

    // M3U Import
    Route::post('/m3u/import', [M3UImportController::class, 'import']);
    Route::get('/m3u/history', [M3UImportController::class, 'history']);
    Route::get('/m3u/download-log/{logId}', [M3UImportController::class, 'downloadLog']);

    // Logs
    Route::get('/logs', [LogController::class, 'index']);
    Route::get('/logs/export', [LogController::class, 'export']);
    Route::get('/logs/imports', [LogController::class, 'importLogs']);
});
