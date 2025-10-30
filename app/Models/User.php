<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role',
        'avatar',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes'
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function listings()
    {
        return $this->hasMany(Listing::class);
    }

    public function messagesSent()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function messagesReceived()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    public function favorites()
    {
        return $this->belongsToMany(Listing::class, 'favorites');
    }

    public function reviewsGiven()
    {
        return $this->hasMany(Review::class, 'reviewer_id');
    }

    public function reviewsReceived()
    {
        return $this->hasMany(Review::class, 'reviewee_id');
    }

    // ==================== Методы для работы с ролями (ТЗ v2.1) ====================

    public function isDealer(): bool
    {
        return $this->role === 'dealer';
    }

    public function isIndividual(): bool
    {
        return $this->role === 'individual';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    // Лимиты согласно ТЗ v2.1
    public function getMaxActiveListings(): int
    {
        return match($this->role) {
            'dealer' => 100,
            'individual' => 10,
            'admin' => PHP_INT_MAX,
            default => 10
        };
    }

    public function getMaxPhotosPerListing(): int
    {
        return match($this->role) {
            'dealer' => 12,
            'admin' => 12,
            default => 6
        };
    }

    public function getBumpIntervalDays(): int
    {
        return match($this->role) {
            'dealer' => 3,
            'individual' => 7,
            'admin' => 0,
            default => 7
        };
    }
}
