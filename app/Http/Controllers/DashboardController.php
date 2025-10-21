<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Models\Listing;
use App\Models\Message;
use App\Models\User;

class DashboardController extends Controller
{
    /**
     * Показать объявления текущего пользователя.
     */
    public function myListings(): View
    {
        // 1. Получаем пользователя, который сейчас вошёл в систему
        $user = Auth::user();

        // 2. Загружаем все его объявления (включая неактивные)
        //    и сортируем их по дате (новые вверху)
        $listings = $user->listings()
            ->with('category', 'region')
            ->latest()
            ->paginate(10);

        // 3. Возвращаем вид и передаём в него объявления
        return view('dashboard.my-listings', compact('listings'));
    }
    public function favorites()
    {
        $listings = auth()->user()
            ->favorites()
            ->with(['category', 'region', 'media'])
            ->latest()
            ->paginate(10);

        return view('dashboard.favorites', compact('listings'));
    }
    public function messages()
    {
        $userId = auth()->id();

        // Находим все уникальные диалоги, группируя их
        $conversations = Message::where('sender_id', $userId)
            ->orWhere('receiver_id', $userId)
            ->with(['listing', 'sender', 'receiver'])
            ->latest('created_at') // Сортируем по дате последнего сообщения
            ->get()
            ->unique(function ($item) use ($userId) {
                // Создаем уникальный ключ для каждой пары (объявление + собеседник)
                $participantId = $item->sender_id == $userId ? $item->receiver_id : $item->sender_id;
                return $item->listing_id . ':' . $participantId;
            });

        return view('dashboard.messages', compact('conversations'));
    }
    public function showConversation(Listing $listing, User $participant)
    {
        $user = auth()->user();

        $messages = Message::where('listing_id', $listing->id)
            ->where(function ($query) use ($user, $participant) {
                $query->where('sender_id', $user->id)->where('receiver_id', $participant->id);
            })
            ->orWhere(function ($query) use ($user, $participant) {
                $query->where('sender_id', $participant->id)->where('receiver_id', $user->id);
            })
            ->with('sender')
            ->oldest()
            ->get();

        return view('dashboard.conversation-show', compact('listing', 'participant', 'messages'));
    }

    public function replyToConversation(Request $request, Listing $listing, User $participant)
    {
        $request->validate(['body' => ['required', 'string', 'min:1', 'max:2000']]);

        // Получателем ответа всегда будет другой участник
        $receiverId = (auth()->id() === $listing->user_id) ? $participant->id : $listing->user_id;

        Message::create([
            'listing_id' => $listing->id,
            'sender_id' => auth()->id(),
            'receiver_id' => $receiverId,
            'body' => $request->input('body'),
        ]);

        return back()->with('success', 'Ответ отправлен!');
    }
}
