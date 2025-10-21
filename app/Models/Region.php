<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships; // Импортируем

class Region extends Model
{
    use HasFactory, HasRecursiveRelationships; // Используем

    protected $fillable = ['parent_id', 'name', 'slug', 'type', 'latitude', 'longitude'];

    public function listings()
    {
        return $this->hasMany(Listing::class);
    }
}
