<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'assigned_to',
        'subject',
        'description',
        'priority',
        'status',
        'category',
        'resolution_notes',
        'resolved_at',
        'closed_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    /**
     * Связь с пользователем, который создал тикет
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Связь с пользователем, которому назначен тикет (агент поддержки)
     */
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Сквозной метод для поиска тикетов по статусу
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Сквозной метод для поиска тикетов по приоритету
     */
    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Сквозной метод для поиска тикетов по категории
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Сквозной метод для поиска тикетов по пользователю
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Проверяет, открыт ли тикет
     */
    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    /**
     * Проверяет, находится ли тикет в процессе решения
     */
    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    /**
     * Проверяет, решен ли тикет
     */
    public function isResolved(): bool
    {
        return $this->status === 'resolved';
    }

    /**
     * Проверяет, закрыт ли тикет
     */
    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    /**
     * Проверяет, ожидает ли тикет
     */
    public function isWaiting(): bool
    {
        return $this->status === 'waiting';
    }

    /**
     * Проверяет, является ли тикет срочным
     */
    public function isCritical(): bool
    {
        return $this->priority === 'critical';
    }

    /**
     * Проверяет, является ли тикет высокоприоритетным
     */
    public function isHighPriority(): bool
    {
        return $this->priority === 'high';
    }

    /**
     * Возвращает цветовую метку для приоритета
     */
    public function getPriorityBadgeAttribute(): string
    {
        return match($this->priority) {
            'critical' => 'bg-red-100 text-red-800',
            'high' => 'bg-orange-100 text-orange-800',
            'medium' => 'bg-yellow-100 text-yellow-800',
            'low' => 'bg-green-100 text-green-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Возвращает цветовую метку для статуса
     */
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'open' => 'bg-blue-100 text-blue-800',
            'in_progress' => 'bg-yellow-100 text-yellow-800',
            'resolved' => 'bg-green-100 text-green-800',
            'closed' => 'bg-gray-100 text-gray-800',
            'waiting' => 'bg-purple-100 text-purple-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Возвращает текстовое представление статуса
     */
    public function getStatusTextAttribute(): string
    {
        return match($this->status) {
            'open' => 'Открыт',
            'in_progress' => 'В процессе',
            'resolved' => 'Решен',
            'closed' => 'Закрыт',
            'waiting' => 'Ожидает',
            default => 'Неизвестно',
        };
    }

    /**
     * Возвращает текстовое представление приоритета
     */
    public function getPriorityTextAttribute(): string
    {
        return match($this->priority) {
            'critical' => 'Критический',
            'high' => 'Высокий',
            'medium' => 'Средний',
            'low' => 'Низкий',
            default => 'Неизвестно',
        };
    }

    /**
     * Возвращает текстовое представление категории
     */
    public function getCategoryTextAttribute(): string
    {
        return match($this->category) {
            'technical' => 'Технический',
            'billing' => 'Платежи',
            'account' => 'Аккаунт',
            'listing' => 'Объявления',
            'verification' => 'Верификация',
            'other' => 'Другое',
            default => 'Другое',
        };
    }
}
