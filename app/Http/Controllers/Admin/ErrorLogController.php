<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ErrorLogController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function (Request $request, $next) {
            if (!Auth::user()->isAdmin()) {
                // Для логов ошибок требуем быть администратором
                abort(403, 'У вас нет доступа к логам ошибок');
            }
            return $next($request);
        });
    }

    /**
     * Отображает список ошибок регистрации
     */
    public function registrationErrors()
    {
        // В реальном приложении здесь будет логика для получения ошибок регистрации
        // из лог-файлов или специальной таблицы ошибок

        // Для демонстрации создадим фиктивные данные
        $errors = [
            [
                'id' => 1,
                'user_email' => 'test@example.com',
                'error_message' => 'Неверный формат email',
                'timestamp' => now()->subHours(2),
                'ip_address' => '192.168.1.1',
                'user_agent' => 'Mozilla/5.0...'
            ],
            [
                'id' => 2,
                'user_email' => 'invalid-email',
                'error_message' => 'Пароль слишком короткий',
                'timestamp' => now()->subHours(5),
                'ip_address' => '192.168.1.2',
                'user_agent' => 'Mozilla/5.0...'
            ],
            [
                'id' => 3,
                'user_email' => 'duplicate@example.com',
                'error_message' => 'Пользователь с таким email уже существует',
                'timestamp' => now()->subDay(),
                'ip_address' => '192.168.1.3',
                'user_agent' => 'Mozilla/5.0...'
            ]
        ];

        return view('admin.errors.registration', compact('errors'));
    }

    /**
     * Отображает общие ошибки приложения
     */
    public function applicationErrors()
    {
        // В реальном приложении здесь будет логика для получения общих ошибок приложения
        // из лог-файлов

        $errors = [
            [
                'id' => 1,
                'error_message' => 'SQLSTATE[23000]: Integrity constraint violation',
                'timestamp' => now()->subHours(1),
                'file' => 'app/Http/Controllers/ListingController.php:45',
                'level' => 'error'
            ],
            [
                'id' => 2,
                'error_message' => 'Trying to access array offset on value of type null',
                'timestamp' => now()->subHours(3),
                'file' => 'app/Models/Category.php:120',
                'level' => 'warning'
            ]
        ];

        return view('admin.errors.application', compact('errors'));
    }

    /**
     * Отображает общую страницу ошибок с выбором типа
     */
    public function index()
    {
        // В реальном приложении здесь будет общая статистика по ошибкам
        $totalErrors = 60; // Общее количество ошибок
        $registrationErrors = 24; // Ошибки регистрации
        $applicationErrors = 36; // Ошибки приложения

        return view('admin.errors.index', compact('totalErrors', 'registrationErrors', 'applicationErrors'));
    }

    /**
     * Очищает логи ошибок
     */
    public function clearLogs()
    {
        // В реальном приложении здесь будет логика для очистки лог-файлов
        // или записей в таблице ошибок
        
        // Для демонстрации просто возвращаемся с сообщением
        return redirect()->back()->with('success', 'Логи ошибок успешно очищены');
    }
}