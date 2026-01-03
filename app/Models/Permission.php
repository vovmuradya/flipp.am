<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'description',
    ];

    /**
     * Роли, которые имеют это разрешение
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_permission');
    }
}