<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarBrand extends Model
{
    use HasFactory;

    protected $fillable = [
        'nhtsa_id',
        'name_en',
        'name_ru',
    ];

    public function models()
    {
        return $this->hasMany(CarModel::class);
    }
}
