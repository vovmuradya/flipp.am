<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarModel extends Model
{
    use HasFactory;

    protected $fillable = [
        'car_brand_id',
        'nhtsa_id',
        'name_en',
        'name_ru',
    ];

    public function brand()
    {
        return $this->belongsTo(CarBrand::class);
    }
    public function generations()
    {
        return $this->hasMany(CarGeneration::class, 'car_model_id');
    }
}
