<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Visit extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'ip_address',
        'user_agent',
        'referrer',
        'page_url',
        'page_title',
        'country',
        'city',
        'device_type',
        'browser',
        'platform',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_term',
        'utm_content',
        'visited_at',
        'user_id',
    ];

    protected $casts = [
        'visited_at' => 'datetime',
    ];

    /**
     * Связь с пользователем
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Сквозной метод для получения статистики посещений
     */
    public function scopeForPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('visited_at', [$startDate, $endDate]);
    }

    /**
     * Сквозной метод для получения посещений по URL
     */
    public function scopeForUrl($query, $url)
    {
        return $query->where('page_url', $url);
    }

    /**
     * Сквозной метод для получения уникальных посещений
     */
    public function scopeUnique($query)
    {
        return $query->select('session_id')->distinct();
    }
}