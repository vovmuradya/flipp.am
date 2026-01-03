<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Message;
use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function (Request $request, $next) {
            if (!Auth::user()->hasPermission('view_support') && !Auth::user()->isAdmin()) {
                abort(403, 'У вас нет доступа к службе поддержки');
            }
            return $next($request);
        });
    }

    /**
     * Отображает список тикетов поддержки
     */
    public function index()
    {
        // В реальном приложении здесь будут тикеты поддержки
        // Для упрощения возвращаем пользователей с последними сообщениями
        $users = User::with(['messagesSent', 'messagesReceived'])->orderBy('created_at', 'desc')->paginate(20);
        return view('admin.support.index', compact('users'));
    }

    /**
     * Имперсонализирует пользователя (входит в его аккаунт)
     */
    public function impersonate($id)
    {
        $user = User::findOrFail($id);
        
        // Сохраняем текущего пользователя в сессию
        session(['original_user_id' => Auth::id()]);
        
        // Аутентифицируем текущего пользователя как выбранного
        Auth::login($user);
        
        return redirect('/')->with('success', 'Вы вошли как ' . $user->name);
    }

    /**
     * Выходит из аккаунта пользователя и возвращается к своему
     */
    public function stopImpersonate()
    {
        $originalUserId = session('original_user_id');
        
        if ($originalUserId) {
            $originalUser = User::findOrFail($originalUserId);
            Auth::login($originalUser);
            session()->forget('original_user_id');
        }
        
        return redirect()->route('admin.support.index')->with('success', 'Вы вернулись к своему аккаунту');
    }

    /**
     * Отображает форму отправки сообщения пользователю
     */
    public function showMessageForm($userId)
    {
        $user = User::findOrFail($userId);
        return view('admin.support.message-form', compact('user'));
    }

    /**
     * Отправляет сообщение пользователю от имени администратора
     */
    public function sendMessage(Request $request, $userId)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $user = User::findOrFail($userId);
        
        // Создаем сообщение от администратора к пользователю
        Message::create([
            'sender_id' => Auth::id(), // ID администратора
            'receiver_id' => $user->id,
            'subject' => $request->subject,
            'message' => $request->message,
            'read_at' => null,
        ]);

        return redirect()->route('admin.support.index')->with('success', 'Сообщение отправлено пользователю');
    }
}