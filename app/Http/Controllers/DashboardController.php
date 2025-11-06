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

        // 2. Загружаем все его ОБЫЧНЫЕ объявления
        $listings = $user->listings()
            ->regular() // Используем scope
            ->with(['category', 'region', 'media', 'vehicleDetail'])
            ->latest()
            ->paginate(10);

        // 3. Возвращаем вид и передаём в него объявления
        return view('dashboard.my-listings', compact('listings'));
    }

    /**
     * Показать аукционные объявления текущего пользователя.
     */
    public function myAuctions(Request $request)
    {
        $filters = [
            'brand' => trim($request->input('brand', '')),
            'model' => trim($request->input('model', '')),
            'price_from' => $request->input('price_from'),
            'price_to' => $request->input('price_to'),
            'year_from' => $request->input('year_from'),
            'year_to' => $request->input('year_to'),
        ];

        $query = Listing::query()
            ->where('listing_type', 'vehicle')
            ->fromAuction()
            ->with(['vehicleDetail', 'media', 'category', 'user'])
            ->latest();

        if ($filters['brand'] !== '') {
            $brandTerm = mb_strtolower($filters['brand']);
            $query->whereHas('vehicleDetail', function ($q) use ($brandTerm) {
                $q->whereRaw('LOWER(make) LIKE ?', ["%{$brandTerm}%"]);
            });
        }

        if ($filters['model'] !== '') {
            $modelTerm = mb_strtolower($filters['model']);
            $query->whereHas('vehicleDetail', function ($q) use ($modelTerm) {
                $q->whereRaw('LOWER(model) LIKE ?', ["%{$modelTerm}%"]);
            });
        }

        if (is_numeric($filters['price_from'])) {
            $query->where('price', '>=', (int) $filters['price_from']);
        }

        if (is_numeric($filters['price_to'])) {
            $query->where('price', '<=', (int) $filters['price_to']);
        }

        if (is_numeric($filters['year_from'])) {
            $query->whereHas('vehicleDetail', function ($q) use ($filters) {
                $q->where('year', '>=', (int) $filters['year_from']);
            });
        }

        if (is_numeric($filters['year_to'])) {
            $query->whereHas('vehicleDetail', function ($q) use ($filters) {
                $q->where('year', '<=', (int) $filters['year_to']);
            });
        }

        $listings = $query->paginate(12)->withQueryString();

        return view('dashboard.my-auctions', compact('listings', 'filters'));
    }

    /**
     * Показать избранные объявления пользователя
     */
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
