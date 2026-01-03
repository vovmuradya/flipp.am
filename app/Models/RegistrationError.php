<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistrationError extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'username',
        'ip_address',
        'user_agent',
        'error_type',
        'error_code',
        'error_message',
        'request_data',
        'validation_errors',
        'occurred_at',
        'is_resolved',
        'resolved_at',
        'resolved_by',
        'resolution_notes',
    ];

    protected $casts = [
        'request_data' => 'array',
        'validation_errors' => 'array',
        'occurred_at' => 'datetime',
        'resolved_at' => 'datetime',
        'is_resolved' => 'boolean',
    ];

    /**
     * Связь с пользователем, который решил ошибку
     */
    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /**
     * Сквозной метод для поиска ошибок по типу
     */
    public function scopeByType($query, $type)
    {
        return $query->where('error_type', $type);
    }

    /**
     * Сквозной метод для поиска ошибок по статусу решения
     */
    public function scopeByResolutionStatus($query, $status)
    {
        return $query->where('is_resolved', $status);
    }

    /**
     * Сквозной метод для поиска ошибок по email
     */
    public function scopeByEmail($query, $email)
    {
        return $query->where('email', $email);
    }

    /**
     * Сквозной метод для поиска ошибок по IP-адресу
     */
    public function scopeByIp($query, $ip)
    {
        return $query->where('ip_address', $ip);
    }

    /**
     * Сквозной метод для поиска ошибок за определенный период
     */
    public function scopeForPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('occurred_at', [$startDate, $endDate]);
    }

    /**
     * Проверяет, решена ли ошибка
     */
    public function isResolved(): bool
    {
        return $this->is_resolved;
    }

    /**
     * Проверяет, не решена ли ошибка
     */
    public function isNotResolved(): bool
    {
        return !$this->is_resolved;
    }

    /**
     * Возвращает тип ошибки в читаемом виде
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->error_type) {
            'validation' => 'Ошибка валидации',
            'duplicate' => 'Дублирование данных',
            'technical' => 'Техническая ошибка',
            'email_verification' => 'Ошибка верификации email',
            'social_auth' => 'Ошибка социальной авторизации',
            'captcha' => 'Ошибка CAPTCHA',
            'rate_limit' => 'Превышение лимита запросов',
            'other' => 'Другая ошибка',
            default => 'Неизвестная ошибка',
        };
    }

    /**
     * Возвращает цветовую метку для типа ошибки
     */
    public function getTypeBadgeAttribute(): string
    {
        return match($this->error_type) {
            'validation' => 'bg-yellow-100 text-yellow-800',
            'duplicate' => 'bg-red-100 text-red-800',
            'technical' => 'bg-purple-100 text-purple-800',
            'email_verification' => 'bg-blue-100 text-blue-800',
            'social_auth' => 'bg-indigo-100 text-indigo-800',
            'captcha' => 'bg-pink-100 text-pink-800',
            'rate_limit' => 'bg-orange-100 text-orange-800',
            'other' => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}
