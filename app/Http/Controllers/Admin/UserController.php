<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function (Request $request, $next) {
            if (!Auth::user()->hasPermission('view_users') && !Auth::user()->isAdmin()) {
                abort(403, 'У вас нет доступа к управлению пользователями');
            }
            return $next($request);
        });
    }

    /**
     * Отображает список пользователей
     */
    public function index()
    {
        $query = User::query();

        // Фильтрация по основной роли
        if (request()->filled('role')) {
            $query->where('role', request('role'));
        }

        // Фильтрация по email
        if (request()->filled('email')) {
            $query->where('email', 'like', '%' . request('email') . '%');
        }

        // Фильтрация по имени
        if (request()->filled('name')) {
            $query->where('name', 'like', '%' . request('name') . '%');
        }

        // Фильтрация по дате регистрации
        if (request()->filled('date_from')) {
            $query->whereDate('created_at', '>=', request('date_from'));
        }

        if (request()->filled('date_to')) {
            $query->whereDate('created_at', '<=', request('date_to'));
        }

        $users = $query->with('roles')->orderBy('created_at', 'desc')->paginate(20);
        $roles = Role::all();

        return view('admin.users.index', compact('users', 'roles'));
    }

    /**
     * Показывает форму редактирования пользователя
     */
    public function edit($id)
    {
        $user = User::with('listings')->findOrFail($id);
        $roles = Role::all(); // Получаем все доступные роли
        return view('admin.users.edit', compact('user', 'roles'));
    }

    /**
     * Обновляет информацию о пользователе
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,dealer,individual',
            'is_dealer' => 'boolean',
            'roles' => 'array',
            'roles.*' => 'exists:roles,id',
        ]);

        $user->update($request->only(['name', 'email', 'role', 'is_dealer']));

        // Обновляем роли пользователя
        if ($request->has('roles')) {
            // Сначала удаляем все текущие роли
            $user->roles()->detach();

            // Назначаем новые роли
            foreach ($request->roles as $roleId) {
                $role = Role::find($roleId);
                if ($role) {
                    $user->assignRole($role);
                }
            }
        }

        return redirect()->route('admin.users.index')->with('success', 'Пользователь успешно обновлен');
    }

    /**
     * Удаляет пользователя
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if ($user->id === Auth::id()) {
            return redirect()->route('admin.users.index')->with('error', 'Нельзя удалить собственную учетную запись');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Пользователь успешно удален');
    }

}