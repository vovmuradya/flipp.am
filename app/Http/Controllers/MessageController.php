<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function reply(Request $request, Listing $listing, User $participant)
    {
        // Убедимся, что отвечает именно владелец объявления
        if (auth()->id() !== $listing->user_id) {
            abort(403);
        }

        // Валидация
        $request->validate([
            'body' => ['required', 'string', 'min:1', 'max:2000'],
        ]);

        // Создаём и сохраняем ответ
        Message::create([
            'listing_id' => $listing->id,
            'sender_id' => auth()->id(),      // Отправитель - владелец
            'receiver_id' => $participant->id, // Получатель - тот, кто начал чат
            'body' => $request->input('body'),
        ]);

        // Возвращаем пользователя обратно на страницу объявления
        return back()->with('success', 'Ответ отправлен!');
    }
    /**
     * Сохранить новое сообщение в базе данных.
     */
    public function store(Request $request, Listing $listing)
    {
        // 1. Проверяем, что пользователь не пишет самому себе
        if (Auth::id() === $listing->user_id) {
            return back()->with('error', 'Вы не можете отправить сообщение самому себе.');
        }

        // 2. Валидация
        $request->validate([
            'body' => ['required', 'string', 'min:1', 'max:2000'],
        ]);

        // 3. Создаём и сохраняем сообщение
        Message::create([
            'listing_id' => $listing->id,
            'sender_id' => Auth::id(),
            'receiver_id' => $listing->user_id,
            'body' => $request->input('body'),
        ]);

        // 4. Возвращаем пользователя обратно с сообщением об успехе
        return back()->with('success', 'Ваше сообщение успешно отправлено!');
    }
}
