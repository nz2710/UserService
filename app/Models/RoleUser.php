<?php

namespace App\Models;

use App\Traits\HasPermissions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoleUser extends Model
{
    use HasFactory, HasPermissions;

    protected $table = 'role_user';

    protected $fillable = [
        'user_id',
        'role_id'
    ];
}

