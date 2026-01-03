<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IdramTransaction;
use App\Models\ImIDUser;
use App\Services\IdramImIDService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IdramImIDController extends Controller
{
    protected IdramImIDService $idramImIDService;

    public function __construct(IdramImIDService $idramImIDService)
    {
        $this->idramImIDService = $idramImIDService;

        $this->middleware('auth');
        $this->middleware(function (Request $request, $next) {
            if (!Auth::user()->hasPermission('manage_settings') && !Auth::user()->isAdmin()) {
                abort(403, 'У вас нет доступа к настройкам интеграции');
            }
            return $next($request);
        });
    }

    /**
     * Отображает страницу управления интеграцией
     */
    public function index()
    {
        $transactions = IdramTransaction::with('user')->latest()->paginate(20);
        $verificationRequests = ImIDUser::with('user')->latest()->paginate(20);

        return view('admin.idram-imid.index', compact('transactions', 'verificationRequests'));
    }

    /**
     * Отображает страницу настроек интеграции
     */
    public function settings()
    {
        return view('admin.idram-imid.settings');
    }

    /**
     * Обновляет настройки интеграции
     */
    public function updateSettings(Request $request)
    {
        // В реальном приложении здесь будет сохранение настроек в конфигурацию
        // или в базу данных

        return redirect()->back()->with('success', 'Настройки интеграции успешно обновлены');
    }

    /**
     * Отображает список транзакций Idram
     */
    public function transactions()
    {
        $transactions = IdramTransaction::with('user')->latest()->paginate(20);

        return view('admin.idram-imid.transactions', compact('transactions'));
    }

    /**
     * Отображает список запросов верификации imID
     */
    public function verificationRequests()
    {
        $verificationRequests = ImIDUser::with('user')->latest()->paginate(20);

        return view('admin.idram-imid.verification-requests', compact('verificationRequests'));
    }

    /**
     * Обновляет статус верификации пользователя imID
     */
    public function updateVerificationStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,verified,rejected',
        ]);

        $imIdUser = ImIDUser::findOrFail($id);
        $imIdUser->update([
            'status' => $request->status,
            'verified_at' => $request->status === 'verified' ? now() : null,
        ]);

        return redirect()->back()->with('success', 'Статус верификации обновлен');
    }
}
