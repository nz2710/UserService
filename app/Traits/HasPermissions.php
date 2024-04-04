<?php

namespace App\Traits;

use App\Models\Permission;
use App\Models\Role;

trait HasPermissions
{
    public function collectPermissions(...$permissions)
    {
        return collect($permissions)
            ->flatten()
            ->reduce(function ($array, $permission) {
                if (empty($permission)) {
                    return $array;
                }

                $permission = $this->getStoredPermission($permission);
                if (!$permission instanceof Permission) {
                    return $array;
                }

                $array[$permission->getKey()] = $permission->id;

                return $array;
            }, []);
    }

    protected function getStoredPermission($permissions)
    {

        if (is_numeric($permissions)) {
            return Permission::where('id', $permissions)->get()->first();
        }

        if (is_string($permissions)) {
            return Permission::where('name', $permissions)->get()->first();
        }

        return false;
    }

    public function givePermissionTo(...$permissions)
    {

        $permissions = $this->collectPermissions(...$permissions);

        $model = $this->getModel();

        if (!$permissions) {
            return false;
        }

        if ($model->exists) {
            $this->permissions()->sync($permissions, false);
            $model->load('permissions');
        }

        return $model;
    }

    public function revokePermissionTo($permission)
    {
        $this->permissions()->detach($this->getStoredPermission($permission));

        $this->load('permissions');

        return $this;
    }

    public function syncPermissions(...$permissions)
    {
        $this->permissions()->detach();

        return $this->givePermissionTo($permissions);
    }

    public function getModelPermissions()
    {
        return $this->roles
        ->loadMissing('permissions')
        ->pluck('permissions')
        ->collapse()
        ->pluck('name');
    }

    public function checkPermissionTo($ability) {
        $ability = str_replace('_', ' ', $ability);

        return $this->roles
        ->loadMissing('permissions')
        ->pluck('permissions')
        ->collapse()
        ->pluck('name')
        ->contains($ability);
    }

}
