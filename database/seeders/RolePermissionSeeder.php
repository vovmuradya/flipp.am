<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Создаем разрешения
        $permissions = [
            // Разрешения для пользователей
            ['name' => 'view_users', 'display_name' => 'Просмотр пользователей', 'description' => 'Просмотр списка пользователей'],
            ['name' => 'create_users', 'display_name' => 'Создание пользователей', 'description' => 'Создание новых пользователей'],
            ['name' => 'edit_users', 'display_name' => 'Редактирование пользователей', 'description' => 'Редактирование информации о пользователях'],
            ['name' => 'delete_users', 'display_name' => 'Удаление пользователей', 'description' => 'Удаление пользователей'],

            // Разрешения для объявлений
            ['name' => 'view_listings', 'display_name' => 'Просмотр объявлений', 'description' => 'Просмотр списка объявлений'],
            ['name' => 'edit_listings', 'display_name' => 'Редактирование объявлений', 'description' => 'Редактирование объявлений'],
            ['name' => 'delete_listings', 'display_name' => 'Удаление объявлений', 'description' => 'Удаление объявлений'],
            ['name' => 'moderate_listings', 'display_name' => 'Модерация объявлений', 'description' => 'Одобрение/отклонение объявлений'],

            // Разрешения для категорий
            ['name' => 'view_categories', 'display_name' => 'Просмотр категорий', 'description' => 'Просмотр списка категорий'],
            ['name' => 'create_categories', 'display_name' => 'Создание категорий', 'description' => 'Создание новых категорий'],
            ['name' => 'edit_categories', 'display_name' => 'Редактирование категорий', 'description' => 'Редактирование категорий'],
            ['name' => 'delete_categories', 'display_name' => 'Удаление категорий', 'description' => 'Удаление категорий'],

            // Разрешения для аналитики
            ['name' => 'view_analytics', 'display_name' => 'Просмотр аналитики', 'description' => 'Просмотр статистики и аналитики'],

            // Разрешения для настроек
            ['name' => 'manage_settings', 'display_name' => 'Управление настройками', 'description' => 'Изменение настроек приложения'],

            // Разрешения для поддержки
            ['name' => 'view_support', 'display_name' => 'Просмотр поддержки', 'description' => 'Просмотр запросов в службу поддержки'],
            ['name' => 'manage_support', 'display_name' => 'Управление поддержкой', 'description' => 'Ответы в службе поддержки'],
            ['name' => 'impersonate_users', 'display_name' => 'Вход как пользователь', 'description' => 'Возможность входить в аккаунты пользователей'],
        ];

        foreach ($permissions as $permissionData) {
            Permission::firstOrCreate(
                ['name' => $permissionData['name']],
                $permissionData
            );
        }

        // Создаем роли
        $adminRole = Role::firstOrCreate(
            ['name' => 'admin'],
            [
                'display_name' => 'Администратор',
                'description' => 'Полный доступ ко всем функциям системы'
            ]
        );

        $moderatorRole = Role::firstOrCreate(
            ['name' => 'moderator'],
            [
                'display_name' => 'Модератор',
                'description' => 'Доступ к модерации объявлений и базовому управлению'
            ]
        );

        $supportRole = Role::firstOrCreate(
            ['name' => 'support'],
            [
                'display_name' => 'Служба поддержки',
                'description' => 'Доступ к системе поддержки пользователей'
            ]
        );

        // Назначаем разрешения ролям
        // Администратор имеет все разрешения
        $allPermissions = Permission::all();
        $adminRole->permissions()->sync($allPermissions->pluck('id'));

        // Модератор может просматривать и модерировать объявления, просматривать пользователей
        $moderatorPermissions = Permission::whereIn('name', [
            'view_listings', 'moderate_listings', 'view_users', 'view_categories'
        ])->get();
        $moderatorRole->permissions()->sync($moderatorPermissions->pluck('id'));

        // Служба поддержки может просматривать пользователей и управлять поддержкой
        $supportPermissions = Permission::whereIn('name', [
            'view_users', 'view_support', 'manage_support', 'impersonate_users'
        ])->get();
        $supportRole->permissions()->sync($supportPermissions->pluck('id'));

        // Если существует пользователь с ID 1 (обычно суперадминистратор), назначаем ему роль администратора
        $superAdmin = User::find(1);
        if ($superAdmin) {
            $superAdmin->assignRole($adminRole);
        }
    }
}
