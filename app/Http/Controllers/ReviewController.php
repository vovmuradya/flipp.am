<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, Listing $listing)
    {
        $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        // Проверяем, что пользователь не оставляет отзыв на своё объявление
        if (auth()->id() === $listing->user_id) {
            return back()->with('error', 'Вы не можете оставить отзыв на своё объявление.');
        }

        // Проверяем, не оставлял ли пользователь уже отзыв на это объявление
        $existingReview = Review::where('listing_id', $listing->id)
            ->where('reviewer_id', auth()->id())
            ->exists();

        if ($existingReview) {
            return back()->with('error', 'Вы уже оставляли отзыв на это объявление.');
        }

        Review::create([
            'listing_id' => $listing->id,
            'reviewer_id' => auth()->id(),
            'reviewee_id' => $listing->user_id, // Отзыв оставляем на владельца объявления
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return back()->with('success', 'Спасибо за ваш отзыв!');
    }
}
