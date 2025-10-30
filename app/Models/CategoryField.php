<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryField extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'category_id',
        'name',
        'key',
        'type',
        'options',
        'is_required',
        'sort_order',
    ];

    /**
     * Исправлено: используем свойство $casts вместо метода
     */
    protected $casts = [
        'options' => 'array', // Laravel будет работать с этим полем как с массивом
        'is_required' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
