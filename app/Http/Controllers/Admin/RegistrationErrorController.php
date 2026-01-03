<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RegistrationError;
use App\Models\User;
use App\Services\RegistrationErrorLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RegistrationErrorController extends Controller
{
    protected RegistrationErrorLogger $logger;

    public function __construct(RegistrationErrorLogger $logger)
    {
        $this->logger = $logger;

        $this->middleware('auth');
        $this->middleware(function (Request $request, $next) {
            if (!Auth::user()->hasPermission('view_analytics') && !Auth::user()->isAdmin()) {
                abort(403, 'У вас нет доступа к просмотру ошибок регистрации');
            }
            return $next($request);
        });
    }

    /**
     * Отображает список ошибок регистрации
     */
    public function index(Request $request)
    {
        $query = RegistrationError::with('resolver')->orderBy('occurred_at', 'desc');

        // Фильтрация по типу ошибки
        if ($request->filled('error_type')) {
            $query->byType($request->error_type);
        }

        // Фильтрация по статусу решения
        if ($request->filled('resolved')) {
            $resolved = $request->resolved === 'true';
            $query->where('is_resolved', $resolved);
        }

        // Фильтрация по email
        if ($request->filled('email')) {
            $query->byEmail($request->email);
        }

        // Фильтрация по IP
        if ($request->filled('ip_address')) {
            $query->byIp($request->ip_address);
        }

        $errors = $query->paginate(20);
        $statistics = $this->logger->getStatistics();

        return view('admin.registration-errors.index', compact('errors', 'statistics'));
    }

    /**
     * Отображает конкретную ошибку
     */
    public function show($id)
    {
        $error = RegistrationError::with('resolver')->findOrFail($id);
        return view('admin.registration-errors.show', compact('error'));
    }

    /**
     * Отображает страницу статистики ошибок
     */
    public function statistics()
    {
        $statistics = $this->logger->getStatistics();
        $topIssues = RegistrationError::selectRaw('error_type, COUNT(*) as count')
            ->groupBy('error_type')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get();

        $recentErrors = RegistrationError::orderBy('occurred_at', 'desc')->limit(10)->get();

        return view('admin.registration-errors.statistics', compact('statistics', 'topIssues', 'recentErrors'));
    }

    /**
     * Отмечает ошибку как решенную
     */
    public function resolve(Request $request, $id)
    {
        $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $success = $this->logger->markAsResolved($id, Auth::id(), $request->notes);

        if ($success) {
            return redirect()->back()->with('success', 'Ошибка успешно отмечена как решенная');
        }

        return redirect()->back()->with('error', 'Не удалось отметить ошибку как решенную');
    }

    /**
     * Отмечает ошибку как нерешенную
     */
    public function unresolve($id)
    {
        $error = RegistrationError::findOrFail($id);
        $error->update([
            'is_resolved' => false,
            'resolved_at' => null,
            'resolved_by' => null,
            'resolution_notes' => null,
        ]);

        return redirect()->back()->with('success', 'Ошибка отмечена как нерешенная');
    }

    /**
     * Очищает старые ошибки регистрации
     */
    public function cleanup(Request $request)
    {
        $days = $request->get('days', 30); // По умолчанию удаляем ошибки старше 30 дней
        $deletedCount = RegistrationError::where('occurred_at', '<', now()->subDays($days))->delete();

        return redirect()->back()->with('success', "Удалено {$deletedCount} старых ошибок регистрации");
    }
}
