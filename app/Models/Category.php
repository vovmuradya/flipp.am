<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\App; // ❗️ Добавлен импорт App

class Category extends Model
{
    use HasFactory, HasRecursiveRelationships;

    protected $fillable = [
        'parent_id',
        'name', // Теперь это JSON
        'slug',
        'icon',
        'sort_order',
        'is_active',
    ];

    /**
     * ❗️ ИЗМЕНЕНИЕ: Добавлено преобразование 'name' в массив.
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'name' => 'array', // Laravel будет автоматически кодировать/декодировать JSON
        ];
    }

    public function getParentKeyName(): string
    {
        return 'parent_id';
    }

    // ========== RELATIONS ==========
    // (Ваши отношения parent, children, listings, fields)

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

    // ========== SCOPES ==========
    // (Ваши scopes: active, root, leaf)
    public function scopeActive($query) { /* ... */ }
    public function scopeRoot($query) { /* ... */ }
    public function scopeLeaf($query) { /* ... */ }

    // ========== ACCESSORS ==========

    /**
     * ✅ НОВЫЙ АКСЕССОР: Получает название категории на текущем языке.
     * Как использовать: $category->current_name
     */
    public function getCurrentNameAttribute(): string
    {
        $locale = App::getLocale(); // 'ru', 'en', или 'hy'

        // Пытаемся получить перевод для текущей локали из JSON-поля 'name'
        return $this->name[$locale]
            ?? $this->name['en'] // Если нет, пробуем 'en'
            ?? $this->name[array_key_first($this->name ?? [])] // Если нет, берем первый доступный
            ?? $this->slug; // В крайнем случае, показываем slug
    }
}
