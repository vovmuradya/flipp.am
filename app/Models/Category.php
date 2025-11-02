<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    use HasFactory, HasRecursiveRelationships;

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'icon',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'name' => 'array',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    protected $appends = ['localized_name'];

    /**
     * Получает название на текущем языке
     */
    public function getLocalizedNameAttribute(): string
    {
        $locale = app()->getLocale();
        $nameData = $this->getAttributeFromArray('name') ?? [];

        // Если name хранится как JSON-массив, берём локализованное значение
        if (is_array($nameData)) {
            return $nameData[$locale]
                ?? ($nameData['ru'] ?? null)
                ?? ($nameData['en'] ?? null)
                ?? $this->slug
                ?? 'Без названия';
        }

        // Фоллбек: если вдруг пришла строка
        return is_string($nameData) ? $nameData : ($this->slug ?? 'Без названия');
    }

    /**
     * АКСЕССОР: всегда возвращаем строку для $category->name,
     * чтобы Blade и прочие места не падали на htmlspecialchars(array ...)
     */
    public function getNameAttribute($value): string
    {
        // $value уже приведён кастом к массиву, но на всякий случай нормализуем
        $names = is_array($value)
            ? $value
            : (is_string($value) ? (json_decode($value, true) ?: []) : []);

        $locale = app()->getLocale();
        return $names[$locale]
            ?? ($names['ru'] ?? null)
            ?? ($names['en'] ?? null)
            ?? (is_string($value) ? $value : ($this->slug ?? 'Без названия'));
    }

    public function getParentKeyName(): string
    {
        return 'parent_id';
    }

    // Relations
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function listings()
    {
        return $this->hasMany(Listing::class);
    }

    public function fields(): BelongsToMany
    {
        return $this->belongsToMany(CategoryField::class, 'category_category_field');
    }
}
