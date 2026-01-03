<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImIDUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'imid_user_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'country',
        'city',
        'document_type',
        'document_number',
        'document_front',
        'document_back',
        'status',
        'verified_at',
        'verification_data',
        'last_synced_at',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'last_synced_at' => 'datetime',
        'verification_data' => 'array',
    ];

    /**
     * Связь с пользователем нашей системы
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Сквозной метод для поиска пользователя по ID imID
     */
    public function scopeByImID($query, $imidUserId)
    {
        return $query->where('imid_user_id', $imidUserId);
    }

    /**
     * Сквозной метод для поиска пользователя по статусу
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Проверяет, верифицирован ли пользователь
     */
    public function isVerified(): bool
    {
        return $this->status === 'verified';
    }

    /**
     * Проверяет, ожидает ли пользователь верификации
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Проверяет, отклонен ли пользователь
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Полное имя пользователя
     */
    public function getFullNameAttribute(): string
    {
        $name = trim($this->first_name . ' ' . $this->last_name);
        return $name ?: 'Неизвестный пользователь';
    }
}
