<?php

namespace App\Models;

use App\Traits\HasPermissions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory, HasPermissions;

    protected $fillable = [
        'name',
    ];


    public function users()
    {
        return $this->belongsToMany(User::class, 'user_id', 'id');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, PermissionRole::class, 'role_id', 'permission_id');
    }
}

