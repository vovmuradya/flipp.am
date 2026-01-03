<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ListingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function (Request $request, $next) {
            if (!Auth::user()->hasPermission('view_listings') && !Auth::user()->isAdmin()) {
                abort(403, 'У вас нет доступа к управлению объявлениями');
            }
            return $next($request);
        });
    }

    /**
     * Отображает список объявлений
     */
    public function index()
    {
        $listings = Listing::with('user')->orderBy('created_at', 'desc')->paginate(20);
        return view('admin.listings.index', compact('listings'));
    }

    /**
     * Показывает форму редактирования объявления
     */
    public function edit($id)
    {
        $listing = Listing::with('user')->findOrFail($id);
        return view('admin.listings.edit', compact('listing'));
    }

    /**
     * Обновляет информацию об объявлении
     */
    public function update(Request $request, $id)
    {
        $listing = Listing::findOrFail($id);
        
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'required|in:active,pending,rejected,suppressed,expired',
            'price' => 'required|numeric|min:0',
        ]);

        $listing->update($request->only(['title', 'description', 'status', 'price']));

        return redirect()->route('admin.listings.index')->with('success', 'Объявление успешно обновлено');
    }

    /**
     * Удаляет объявление
     */
    public function destroy($id)
    {
        $listing = Listing::findOrFail($id);
        $listing->delete();

        return redirect()->route('admin.listings.index')->with('success', 'Объявление успешно удалено');
    }

    /**
     * Отображает список объявлений на модерации
     */
    public function moderation()
    {
        $listings = Listing::with('user')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
            
        return view('admin.listings.moderation', compact('listings'));
    }

    /**
     * Одобряет объявление
     */
    public function approve($id)
    {
        $listing = Listing::findOrFail($id);
        $listing->update(['status' => 'active']);

        return redirect()->back()->with('success', 'Объявление одобрено');
    }

    /**
     * Отклоняет объявление
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string',
            'rejection_comment' => 'nullable|string',
        ]);

        $listing = Listing::findOrFail($id);

        $listing->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
            'rejection_comment' => $request->rejection_comment,
        ]);

        return redirect()->back()->with('success', 'Объявление отклонено');
    }
}