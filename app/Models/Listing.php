<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Listing extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia, Searchable;

    protected $fillable = [
        'user_id',
        'category_id',
        'region_id',
        'title',
        'slug',
        'description',
        'price',
        'currency',
        'status',
        'views_count',
        'promoted_until',
        'last_bumped_at',
        'language',
    ];

    protected function casts(): array
    {
        return [
            'promoted_until' => 'datetime',
            'last_bumped_at' => 'datetime',
        ];
    }

    /**
     * Настройка для полнотекстового поиска
     */
    public function toSearchableArray(): array
    {
        // Load custom fields with their names (assuming relation is set up)
        $this->load('customFieldValues.field');

        $searchableData = [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'price' => (float) $this->price, // Make sure price is a number
            'status' => $this->status,
            'category_id' => $this->category_id,
            'category_name' => $this->category?->name,
            'region_id' => $this->region_id,
            'region_name' => $this->region?->name,
            'user_id' => $this->user_id,
            'created_timestamp' => $this->created_at->timestamp,
        ];

        // Add custom fields to the searchable data
        foreach ($this->customFieldValues as $value) {
            // Use a simple key, e.g., 'year', 'mileage'
            $key = strtolower($value->field->name);
            // Try to convert to a number if the type is number
            $fieldValue = $value->field->type === 'number' ? (int)$value->value : $value->value;
            $searchableData[$key] = $fieldValue;
        }

        return $searchableData;
    }

    /**
     * Определяем, должно ли объявление быть индексируемо
     */
    public function shouldBeSearchable(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Регистрация коллекций медиа
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    /**
     * Регистрация конверсий изображений
     */
    public function registerMediaConversions(\Spatie\MediaLibrary\MediaCollections\Models\Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(150)
            ->height(150)
            ->sharpen(10)
            ->format('webp')
            ->quality(80)
            ->nonQueued();

        $this->addMediaConversion('medium')
            ->width(600)
            ->height(450)
            ->format('webp')
            ->quality(85)
            ->nonQueued();

        $this->addMediaConversion('large')
            ->width(1200)
            ->height(900)
            ->format('webp')
            ->quality(90)
            ->nonQueued();
    }

    // ========== RELATIONS ==========

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function customFieldValues()
    {
        return $this->hasMany(ListingFieldValue::class);
    }

    // ========== SCOPES ==========

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeModeration($query)
    {
        return $query->where('status', 'moderation');
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeByRegion($query, $regionId)
    {
        return $query->where('region_id', $regionId);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopePriceRange($query, $min, $max)
    {
        return $query->whereBetween('price', [$min, $max]);
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
