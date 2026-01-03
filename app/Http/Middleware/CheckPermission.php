<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Проверяем, есть ли у пользователя нужное разрешение или является ли он администратором
        if (!$user->hasPermission($permission) && !$user->isAdmin()) {
            abort(403, 'У вас нет необходимых прав для доступа к этой странице.');
        }

        return $next($request);
    }
}
