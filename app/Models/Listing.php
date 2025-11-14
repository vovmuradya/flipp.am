<?php

namespace App\Models;

use App\Support\SearchQueryHelper;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Listing extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia, Searchable;

    private const PLACEHOLDER_IMAGE = 'https://placehold.co/300x200/e5e7eb/6b7280?text=No+Image';

    protected $fillable = [
        'user_id',
        'category_id',
        'listing_type',  // Новое поле для ТЗ v2.1
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
        'is_from_auction',  // Новое поле для разделения обычных и аукционных объявлений
        'auction_photo_urls',
    ];

    protected function casts(): array
    {
        return [
            'promoted_until' => 'datetime',
            'last_bumped_at' => 'datetime',
            'auction_photo_urls' => 'array',
        ];
    }

    public function searchableAs(): string
    {
        $customIndex = env('ALGOLIA_INDEX');

        if (is_string($customIndex) && trim($customIndex) !== '') {
            return trim($customIndex);
        }

        return 'listings';
    }

    /**
     * Boot метод для автоматической генерации уникального slug
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($listing) {
            if (empty($listing->slug)) {
                $listing->slug = static::generateUniqueSlug($listing->title);
            }
        });
    }

    /**
     * Генерирует уникальный slug на основе заголовка
     */
    public static function generateUniqueSlug(string $title): string
    {
        $slug = \Illuminate\Support\Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        // Проверяем существование slug и добавляем инкрементный суффикс при необходимости
        while (static::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    public function similar()
    {
        return Listing::query()
            ->with(['category', 'region', 'user']) // Загружаем основные связи
            ->active()
            ->where('id', '!=', $this->id) // Исключаем текущее объявление
            ->where('category_id', $this->category_id)
            ->where('region_id', $this->region_id)
            ->latest();
    }

    /**
     * Scope для объявлений, созданных с аукциона.
     */
    public function scopeFromAuction($query)
    {
        return $query->whereHas('vehicleDetail', function ($q) {
            $q->where('is_from_auction', true);
        });
    }

    /**
     * Scope для обычных объявлений (не с аукциона).
     */
    public function scopeRegular($query)
    {
        return $query->where(function ($q) {
            $q->whereDoesntHave('vehicleDetail')
              ->orWhereHas('vehicleDetail', function ($sub) {
                  $sub->where('is_from_auction', false);
              });
        });
    }

    /**
     * Проверяет, является ли объявление аукционным.
     */
    public function isFromAuction(): bool
    {
        return (bool) ($this->is_from_auction || optional($this->vehicleDetail)->is_from_auction);
    }

    /**
     * Настройка для полнотекстового поиска
     */
    public function toSearchableArray(): array
    {
        // Загружаем связи, чтобы избежать N+1
        $this->loadMissing(['customFieldValues.field', 'vehicleDetail']);

        $searchableData = [
            'id'                => $this->id,
            'title'             => $this->title,
            'description'       => $this->description,
            'price'             => (float) $this->price,
            'status'            => $this->status,
            'category_id'       => $this->category_id,
            'region_id'         => $this->region_id,
            'user_id'           => $this->user_id,
            'listing_type'      => $this->listing_type,
            'created_timestamp' => optional($this->created_at)->timestamp,
            'is_from_auction'   => $this->isFromAuction(),
        ];

        // Поля автомобиля (ТЗ v2.1)
        if ($this->vehicleDetail) {
            $searchableData = array_merge($searchableData, [
                'make'                   => $this->vehicleDetail->make,
                'model'                  => $this->vehicleDetail->model,
                'year'                   => (int) $this->vehicleDetail->year,
                'mileage'                => (int) $this->vehicleDetail->mileage,
                'body_type'              => $this->vehicleDetail->body_type,
                'transmission'           => $this->vehicleDetail->transmission,
                'fuel_type'              => $this->vehicleDetail->fuel_type,
                'engine_displacement_cc' => $this->vehicleDetail->engine_displacement_cc,
                'exterior_color'         => $this->vehicleDetail->exterior_color,
            ]);
        }

        $textBuckets = array_filter([
            $this->title,
            $this->description,
            optional($this->vehicleDetail)->make,
            optional($this->vehicleDetail)->model,
        ]);

        if (!empty($textBuckets)) {
            $normalizedTokens = [];
            foreach ($textBuckets as $bucket) {
                foreach (SearchQueryHelper::variants($bucket) as $variant) {
                    $normalizedTokens[] = $variant;
                }
            }

            if (!empty($normalizedTokens)) {
                $searchableData['search_tokens'] = implode(' ', array_unique($normalizedTokens));
            }
        }

        // Кастомные поля (оставляем для обратной совместимости)
        foreach ($this->customFieldValues as $value) {
            if ($value->field) {
                $key = $value->field->key;
                $fieldValue = $value->field->type === 'number' ? (int) $value->value : $value->value;
                $searchableData[$key] = $fieldValue;
            }
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

        $this->addMediaCollection('auction_photos')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/jpg']);
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
            ->nonQueued(); // синхронно

        $this->addMediaConversion('medium')
            ->width(600)
            ->height(450)
            ->format('webp')
            ->quality(85)
            ->nonQueued(); // синхронно

        $this->addMediaConversion('large')
            ->width(1200)
            ->height(900)
            ->format('webp')
            ->quality(90)
            ->nonQueued(); // синхронно
    }

    public function getPreviewUrl(string $conversion = 'thumb'): string
    {
        $collections = ['auction_photos', 'images'];

        foreach ($collections as $collection) {
            $url = $this->getFirstMediaUrl($collection, $conversion);
            if (!empty($url)) {
                return $url;
            }
        }

        return self::PLACEHOLDER_IMAGE;
    }

    public function getAuctionEndsAtAttribute(): ?Carbon
    {
        $detail = $this->vehicleDetail;
        if (!$detail || !$detail->auction_ends_at) {
            return null;
        }

        return $detail->auction_ends_at instanceof Carbon
            ? $detail->auction_ends_at
            : Carbon::parse($detail->auction_ends_at);
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

    // ==================== ТЗ v2.1: Связь с деталями автомобиля ====================

    public function vehicleDetail()
    {
        return $this->hasOne(VehicleDetail::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function favorites()
    {
        return $this->belongsToMany(User::class, 'favorites');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function customFieldValues()
    {
        return $this->hasMany(ListingFieldValue::class);
    }

    // ==================== Scopes ====================

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

    // ==================== ТЗ v2.1: Scope-методы для типов объявлений ====================

    public function scopeVehicles($query)
    {
        return $query->where('listing_type', 'vehicle');
    }

    public function scopeParts($query)
    {
        return $query->where('listing_type', 'parts');
    }
}
