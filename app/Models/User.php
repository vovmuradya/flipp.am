<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'phone',
        'phone_verified_at',
        'role',
        'avatar',
        'timezone',
        'language',
        'notification_settings',
        'provider',
        'provider_id',
        'provider_token',
        'provider_refresh_token',
        'is_dealer',
        'seller_score',
        'seller_score_calculated_at',
        'kyc_status',
        'kyc_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'provider_token',
        'provider_refresh_token',
        'two_factor_secret',
        'two_factor_recovery_codes'
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_dealer' => 'boolean',
            'seller_score' => 'float',
            'seller_score_calculated_at' => 'datetime',
            'kyc_verified_at' => 'datetime',
        ];
    }

    protected function notificationSettings(): Attribute
    {
        $defaults = [
            'messages' => true,
            'auctions' => true,
            'listings' => true,
        ];

        return Attribute::make(
            get: function ($value) use ($defaults) {
                $decoded = is_array($value)
                    ? $value
                    : (json_decode($value ?? '[]', true) ?: []);

                return array_merge($defaults, $decoded);
            },
            set: function ($value) use ($defaults) {
                $incoming = is_array($value) ? $value : (array) $value;
                $normalized = array_merge($defaults, array_filter($incoming, fn ($v) => $v !== null));

                return $normalized;
            }
        );
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

    public function devices()
    {
        return $this->hasMany(Device::class);
    }

    public function dealerProfile()
    {
        return $this->hasOne(DealerProfile::class);
    }

    // ==================== Методы для работы с ролями (ТЗ v2.1) ====================

    public function isDealer(): bool
    {
        return (bool) ($this->is_dealer || $this->role === 'dealer');
    }

    public function isIndividual(): bool
    {
        return $this->role === 'individual';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Роли пользователя
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_role');
    }

    /**
     * Проверяет, имеет ли пользователь определенную роль
     */
    public function hasRole($role)
    {
        if (is_string($role)) {
            return $this->roles->contains('name', $role);
        }

        return $this->roles->contains($role->id);
    }

    /**
     * Проверяет, имеет ли пользователь определенное разрешение
     */
    public function hasPermission($permission)
    {
        if (is_string($permission)) {
            $permission = Permission::where('name', $permission)->first();
        }

        if (!$permission) {
            return false;
        }

        foreach ($this->roles as $role) {
            if ($role->hasPermission($permission)) {
                return true;
            }
        }

        // Проверяем прямые разрешения пользователя (если такая функция будет реализована)
        return false;
    }

    /**
     * Назначает роль пользователю
     */
    public function assignRole($role)
    {
        if (is_string($role)) {
            $role = Role::where('name', $role)->firstOrFail();
        }

        return $this->roles()->attach($role);
    }

    /**
     * Убирает роль у пользователя
     */
    public function removeRole($role)
    {
        if (is_string($role)) {
            $role = Role::where('name', $role)->firstOrFail();
        }

        return $this->roles()->detach($role);
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
