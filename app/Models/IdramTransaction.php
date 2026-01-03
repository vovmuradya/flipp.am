<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IdramTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'transaction_id',
        'order_id',
        'status',
        'amount',
        'currency',
        'description',
        'payment_method',
        'response_data',
        'processed_at',
        'completed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'response_data' => 'array',
        'processed_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Связь с пользователем
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Сквозной метод для поиска транзакции по ID
     */
    public function scopeByTransactionId($query, $transactionId)
    {
        return $query->where('transaction_id', $transactionId);
    }

    /**
     * Сквозной метод для поиска транзакции по статусу
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Проверяет, завершена ли транзакция
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Проверяет, находится ли транзакция в ожидании
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Проверяет, не удалась ли транзакция
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Проверяет, отменена ли транзакция
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }
}
