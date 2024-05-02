<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Services\RequestForwarder;




Route::group([
    'middleware' => ['api'],
    'namespace' => '',
], function ($router) {
    Route::post('/login', [AuthController::class, 'login']);
    // Route::post('/register', [AuthController::class, 'register']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'reset']);
    Route::group([
        'middleware' => ['auth:sanctum', 'check.banned'],
        'namespace' => '',
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
        'middleware' => ['auth:sanctum', 'role:admin'],
        'namespace' => '',
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

        //Depot
        Route::get('depot', function (Request $request, RequestForwarder $forwarder) {
            return $forwarder->forwardRequest($request, 'GET', 'http://host.docker.internal:82/api/admin/depot');
        });
        Route::post('depot', function (Request $request, RequestForwarder $forwarder) {
            return $forwarder->forwardRequest($request, 'POST', 'http://host.docker.internal:82/api/admin/depot');
        });
        Route::get('depot/{id}', function (Request $request, RequestForwarder $forwarder, $id) {
            return $forwarder->forwardRequest($request, 'GET', "http://host.docker.internal:82/api/admin/depot/{$id}");
        });
        Route::put('depot/{id}', function (Request $request, RequestForwarder $forwarder, $id) {
            return $forwarder->forwardRequest($request, 'PUT', "http://host.docker.internal:82/api/admin/depot/{$id}");
        });
        Route::delete('depot/{id}', function (Request $request, RequestForwarder $forwarder, $id) {
            return $forwarder->forwardRequest($request, 'DELETE', "http://host.docker.internal:82/api/admin/depot/{$id}");
        });

        //Order routes
        Route::get('order', function (Request $request, RequestForwarder $forwarder) {
            return $forwarder->forwardRequest($request, 'GET', 'http://host.docker.internal:82/api/admin/order');
        });
        Route::post('order', function (Request $request, RequestForwarder $forwarder) {
            return $forwarder->forwardRequest($request, 'POST', 'http://host.docker.internal:82/api/admin/order');
        });
        Route::get('order/{id}', function (Request $request, RequestForwarder $forwarder, $id) {
            return $forwarder->forwardRequest($request, 'GET', "http://host.docker.internal:82/api/admin/order/{$id}");
        });
        Route::put('order/{id}', function (Request $request, RequestForwarder $forwarder, $id) {
            return $forwarder->forwardRequest($request, 'PUT', "http://host.docker.internal:82/api/admin/order/{$id}");
        });
        Route::delete('order/{id}', function (Request $request, RequestForwarder $forwarder, $id) {
            return $forwarder->forwardRequest($request, 'DELETE', "http://host.docker.internal:82/api/admin/order/{$id}");
        });

        //Partner routes
        Route::get('partner', function (Request $request, RequestForwarder $forwarder) {
            return $forwarder->forwardRequest($request, 'GET', 'http://host.docker.internal:82/api/admin/partner');
        });
        Route::post('partner', function (Request $request, RequestForwarder $forwarder) {
            return $forwarder->forwardRequest($request, 'POST', 'http://host.docker.internal:82/api/admin/partner');
        });
        Route::get('partner/{id}', function (Request $request, RequestForwarder $forwarder, $id) {
            return $forwarder->forwardRequest($request, 'GET', "http://host.docker.internal:82/api/admin/partner/{$id}");
        });
        Route::put('partner/{id}', function (Request $request, RequestForwarder $forwarder, $id) {
            return $forwarder->forwardRequest($request, 'PUT', "http://host.docker.internal:82/api/admin/partner/{$id}");
        });
        Route::delete('partner/{id}', function (Request $request, RequestForwarder $forwarder, $id) {
            return $forwarder->forwardRequest($request, 'DELETE', "http://host.docker.internal:82/api/admin/partner/{$id}");
        });

        //Vehicle routes
        Route::get('vehicle', function (Request $request, RequestForwarder $forwarder) {
            return $forwarder->forwardRequest($request, 'GET', 'http://host.docker.internal:82/api/admin/vehicle');
        });
        Route::post('vehicle', function (Request $request, RequestForwarder $forwarder) {
            return $forwarder->forwardRequest($request, 'POST', 'http://host.docker.internal:82/api/admin/vehicle');
        });
        Route::get('vehicle/{id}', function (Request $request, RequestForwarder $forwarder, $id) {
            return $forwarder->forwardRequest($request, 'GET', "http://host.docker.internal:82/api/admin/vehicle/{$id}");
        });
        Route::put('vehicle/{id}', function (Request $request, RequestForwarder $forwarder, $id) {
            return $forwarder->forwardRequest($request, 'PUT', "http://host.docker.internal:82/api/admin/vehicle/{$id}");
        });
        Route::delete('vehicle/{id}', function (Request $request, RequestForwarder $forwarder, $id) {
            return $forwarder->forwardRequest($request, 'DELETE', "http://host.docker.internal:82/api/admin/vehicle/{$id}");
        });

        //Dashboard routes
        Route::get('dashboard/total-orders', function (Request $request, RequestForwarder $forwarder) {
            return $forwarder->forwardRequest($request, 'GET', 'http://host.docker.internal:82/api/admin/dashboard/total-orders');
        });
        Route::get('dashboard/total-revenue', function (Request $request, RequestForwarder $forwarder) {
            return $forwarder->forwardRequest($request, 'GET', 'http://host.docker.internal:82/api/admin/dashboard/total-revenue');
        });
        Route::get('dashboard/total-partners', function (Request $request, RequestForwarder $forwarder) {
            return $forwarder->forwardRequest($request, 'GET', 'http://host.docker.internal:82/api/admin/dashboard/total-partners');
        });
        Route::get('dashboard/total-vehicles', function (Request $request, RequestForwarder $forwarder) {
            return $forwarder->forwardRequest($request, 'GET', 'http://host.docker.internal:82/api/admin/dashboard/total-vehicles');
        });
        Route::get('dashboard/total-depots', function (Request $request, RequestForwarder $forwarder) {
            return $forwarder->forwardRequest($request, 'GET', 'http://host.docker.internal:82/api/admin/dashboard/total-depots');
        });

        Route::get('dashboard/top-partners-by-revenue', function (Request $request, RequestForwarder $forwarder) {
            return $forwarder->forwardRequest($request, 'GET', 'http://host.docker.internal:82/api/admin/dashboard/top-partners-by-revenue');
        });

        Route::get('dashboard/monthly-revenue', function (Request $request, RequestForwarder $forwarder) {
            return $forwarder->forwardRequest($request, 'GET', 'http://host.docker.internal:82/api/admin/dashboard/monthly-revenue');
        });
    });
});
