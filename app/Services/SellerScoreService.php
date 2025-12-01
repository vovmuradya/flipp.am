<?php

namespace App\Services;

use App\Models\Listing;
use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * Черновой расчёт Seller Score (заглушка до полноценной модели).
 * Основа: верификация, заполненность профиля, активность по объявлениям.
 */
class SellerScoreService
{
    public function calculate(User $user, ?Listing $recentListing = null): float
    {
        $score = 50; // базовый уровень доверия

        if ($user->phone_verified_at instanceof Carbon) {
            $score += 15;
        }

        if ($user->email_verified_at instanceof Carbon) {
            $score += 5;
        }

        if (!empty($user->avatar)) {
            $score += 5;
        }

        if ($user->isDealer()) {
            $score += 10;
        }

        $hasListings = $user->listings()->exists();
        if ($hasListings) {
            $score += 5;
        }

        if ($recentListing) {
            $photoCount = $recentListing->getMedia('images')->count() + $recentListing->getMedia('auction_photos')->count();
            if ($photoCount >= 6) {
                $score += 5;
            }
        }

        return (float) max(0, min(100, $score));
    }

    public function update(User $user, ?Listing $recentListing = null): float
    {
        $score = $this->calculate($user, $recentListing);

        $user->forceFill([
            'seller_score' => $score,
            'seller_score_calculated_at' => now(),
        ])->save();

        return $score;
    }
}
