<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\AdminUserController;




Route::group([
    'middleware' => ['api'],
	'namespace' => 'Api',
], function ($router) {
        Route::post('/login', [AuthController::class, 'login']);
        // Route::post('/register', [AuthController::class, 'register']);
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('/reset-password', [AuthController::class, 'reset']);
    Route::group([
        'middleware' => ['auth:sanctum','check.banned'],
        'namespace' => 'Api',
        'prefix' => ''
    ], function ($router) {
        Route::post('/logout',   [AuthController::class, 'logout']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
        Route::post('/user/confirm-password',   [AuthController::class, 'confirm']);
        Route::post('/user/update-info', [UserController::class, 'changeInfo']);
        Route::get('/user/info', [UserController::class, 'getInfo']);
        Route::post('/user/avatar/change', [UserController::class, 'changeAvatar']);

        Route::get('/user/notifications', [UserController::class, 'notify']);
        Route::post('/user/notifications/read', [UserController::class, 'notifyMarkRead']);
    });

    Route::group([
        'middleware' => ['auth:sanctum','role:admin'],
        'namespace' => 'Api',
        'prefix' => 'admin'
    ], function ($router) {
        // User
        Route::post('create', [AdminUserController::class, 'create']);
        Route::get('users', [AdminUserController::class, 'getAllUser']);
        Route::get('users/count/total', [AdminUserController::class, 'getTotalUsers']);
        Route::get('users/count/new', [AdminUserController::class, 'getNewUsersCount']);

        Route::get('ban/user/{id}', [AdminUserController::class, 'ban']);
        Route::get('unban/user/{id}', [AdminUserController::class, 'unban']);

        // Role
        Route::get('roles', [AdminUserController::class, 'getRole']);
        Route::post('roles', [AdminUserController::class, 'createRole']);
        Route::post('user/{id}/role', [AdminUserController::class, 'setRole']);
        Route::delete('user/{user_id}/role/{role_id}', [AdminUserController::class, 'revokeRole']);

        // Permission
        Route::post('roles/{role_id}/permission', [AdminUserController::class, 'addPermisisonToRole']);
        Route::get('roles/{role_id}/permission', [AdminUserController::class, 'getAllPermissionOfRole']);
        Route::delete('roles/{role_id}/permission/{permission_id}', [AdminUserController::class, 'revokePermissionOfRole']);

    });
});


