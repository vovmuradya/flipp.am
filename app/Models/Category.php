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
        $nameData = $this->name ?? [];

        return $nameData[$locale]
            ?? $nameData['ru']
            ?? $nameData['en']
            ?? $this->slug
            ?? 'Без названия';
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
