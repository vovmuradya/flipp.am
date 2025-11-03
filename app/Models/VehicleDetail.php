<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class VehicleDetail extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'listing_id',
        'make',
        'model',
        'year',
        'mileage',
        'body_type',
        'transmission',
        'fuel_type',
        'engine_displacement_cc',
        'exterior_color',
        'is_from_auction',
        'source_auction_url',
        'auction_ends_at',
    ];
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'year' => 'integer',
        'mileage' => 'integer',
        'engine_displacement_cc' => 'integer',
        'is_from_auction' => 'boolean',
        'auction_ends_at' => 'datetime',
    ];
    /**
     * Get the listing that owns the vehicle details.
     */
    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }
    /**
     * Scope для фильтрации только аукционных автомобилей
     */
    public function scopeFromAuction($query)
    {
        return $query->where('is_from_auction', true);
    }
    /**
     * Scope для поиска по марке
     */
    public function scopeByMake($query, string $make)
    {
        return $query->where('make', 'like', "%{$make}%");
    }
    /**
     * Scope для поиска по модели
     */
    public function scopeByModel($query, string $model)
    {
        return $query->where('model', 'like', "%{$model}%");
    }
    /**
     * Scope для фильтрации по году
     */
    public function scopeByYear($query, int $yearFrom = null, int $yearTo = null)
    {
        if ($yearFrom) {
            $query->where('year', '>=', $yearFrom);
        }
        if ($yearTo) {
            $query->where('year', '<=', $yearTo);
        }
        return $query;
    }
    /**
     * Scope для фильтрации по пробегу
     */
    public function scopeByMileage($query, int $mileageFrom = null, int $mileageTo = null)
    {
        if ($mileageFrom) {
            $query->where('mileage', '>=', $mileageFrom);
        }
        if ($mileageTo) {
            $query->where('mileage', '<=', $mileageTo);
        }
        return $query;
    }
    /**
     * Получить отформатированный пробег
     */
    public function getFormattedMileageAttribute(): string
    {
        return number_format($this->mileage, 0, '.', ' ') . ' км';
    }
    /**
     * Получить полное название автомобиля
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->make} {$this->model} ({$this->year})";
    }
}
