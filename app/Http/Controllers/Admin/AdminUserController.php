<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Services\UserService;
use App\Http\Controllers\Controller;
use App\Http\Services\Admin\AdminService;
use App\Http\Services\Admin\AdminUserService;
use App\Http\Requests\AdminRequest\AdminRequest;

class AdminUserController extends Controller
{
    private $adminUserService;
    public function __construct(AdminUserService $adminUserService)
    {
        $this->adminUserService = $adminUserService;
    }

    public function create(Request $request) {
        $data = $this->adminUserService->create($request);

        return $this->apiResponse(0, __('Create user successfully.'), $data);
    }

    #region User
    public function getAllUser(Request $request) {
        $data = $this->adminUserService->getall($request);

        return $this->apiResponse(0, __('Get users successfully.'), $data);
    }

    public function getNewUsersCount(Request $request) {
        $data = $this->adminUserService->getNewUsersCount($request);

        return $this->apiResponse(0, __('Get new users count successfully.'), $data);
    }

    public function getTotalUsers(Request $request) {
        $data = $this->adminUserService->getTotalUsers($request);

        return $this->apiResponse(0, __('Get total users count successfully.'), $data);
    }

    public function ban(Request $request, $id) {
        $data = $this->adminUserService->ban($request, $id);

        return $this->apiResponse(0, __('Ban Successfully.'), $data);
    }

    public function unban(Request $request, $id) {
        $data = $this->adminUserService->unban($request, $id);

        return $this->apiResponse(0, __('Unban Successfully.'), $data);
    }
    #endregion

    #region Role
    public function getRole(Request $request) {

        $data = $this->adminUserService->getRole($request);

        return $this->apiResponse(0, __('Get role for user successfully'), $data);
    }

    public function createRole(Request $request) {

        $data = $this->adminUserService->createRole($request);

        return $this->apiResponse(0, __('Get role for user successfully'), $data);
    }


    public function setRole(AdminRequest $request, $id) {

        $data = $this->adminUserService->setRole($request, $id);

        return $this->apiResponse(0, __('Set role for user successfully'), $data);
    }

    public function revokeRole(Request $request, $user_id, $role_id) {

        $data = $this->adminUserService->revokeRole($request, $user_id, $role_id);

        return $this->apiResponse(0, __('Revoke role for user successfully'), $data);
    }
    #endregion

    #region Permission
    public function addPermisisonToRole(Request $request, $role_id) {
        $data = $this->adminUserService->addPermisisonToRole($request, $role_id);

        return $this->apiResponse(0, __('Add permissions to role successfully.'), $data);
    }

    public function getAllPermissionOfRole(Request $request, $role_id) {
        $data = $this->adminUserService->getAllPermissionOfRole($request, $role_id);

        return $this->apiResponse(0, __('Get all permissions of a role successfully.'), $data);
    }

    public function revokePermissionOfRole(Request $request, $role_id, $permission_id) {
        $data = $this->adminUserService->revokePermissionOfRole($request, $role_id, $permission_id);

        return $this->apiResponse(0, __('Revoke Permission successfully.'), $data);
    }
    #endregion
}
