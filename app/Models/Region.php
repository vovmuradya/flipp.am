<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;

class Region extends Model
{
    use HasFactory, HasRecursiveRelationships;

    protected $fillable = ['parent_id', 'name', 'slug', 'type', 'latitude', 'longitude'];

    protected $casts = [
        'name' => 'array',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    protected $appends = ['localized_name'];

    public function getLocalizedNameAttribute()
    {
        $nameData = $this->name;
        if (is_array($nameData)) {
            $locale = app()->getLocale();
            return $nameData[$locale]
                ?? $nameData['ru']
                ?? $nameData['en']
                ?? (reset($nameData) ?: '');
        }

        return (string) $nameData;
    }

    public function listings()
    {
        return $this->hasMany(Listing::class);
    }
}
