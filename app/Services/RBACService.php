<?php

namespace App\Services;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;

class RBACService
{
    /**
     * Создает новую роль
     */
    public function createRole(string $name, string $displayName = null, string $description = null): Role
    {
        return Role::create([
            'name' => $name,
            'display_name' => $displayName,
            'description' => $description,
        ]);
    }

    /**
     * Создает новое разрешение
     */
    public function createPermission(string $name, string $displayName = null, string $description = null): Permission
    {
        return Permission::create([
            'name' => $name,
            'display_name' => $displayName,
            'description' => $description,
        ]);
    }

    /**
     * Назначает разрешение роли
     */
    public function assignPermissionToRole(string $permissionName, string $roleName): bool
    {
        $permission = Permission::where('name', $permissionName)->first();
        $role = Role::where('name', $roleName)->first();

        if (!$permission || !$role) {
            return false;
        }

        $role->givePermissionTo($permission);
        return true;
    }

    /**
     * Назначает роль пользователю
     */
    public function assignRoleToUser(User $user, string $roleName): bool
    {
        $role = Role::where('name', $roleName)->first();

        if (!$role) {
            return false;
        }

        $user->assignRole($role);
        return true;
    }

    /**
     * Проверяет, имеет ли пользователь определенную роль
     */
    public function userHasRole(User $user, string $roleName): bool
    {
        return $user->hasRole($roleName);
    }

    /**
     * Проверяет, имеет ли пользователь определенное разрешение
     */
    public function userHasPermission(User $user, string $permissionName): bool
    {
        return $user->hasPermission($permissionName);
    }

    /**
     * Получает все роли пользователя
     */
    public function getUserRoles(User $user): \Illuminate\Database\Eloquent\Collection
    {
        return $user->roles;
    }

    /**
     * Получает все разрешения роли
     */
    public function getRolePermissions(Role $role): \Illuminate\Database\Eloquent\Collection
    {
        return $role->permissions;
    }
}