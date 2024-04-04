<?php

namespace App\Models;

use App\Traits\HasPermissions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermissionRole extends Model
{
    use HasFactory, HasPermissions;

    protected $table = 'permission_role';

    protected $fillable = [
        'permission_id',
        'role_id'
    ];
}

