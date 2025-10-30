<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarGeneration extends Model
{
    // Разрешаем массовое присвоение для полей, используемых в сидере
    protected $fillable = [
        'car_model_id',
        'name',
        'body_code',
        'year_start',
        'year_end',
    ];

    // Приводим годы к целочисленному типу для удобства работы
    protected $casts = [
        'year_start' => 'integer',
        'year_end' => 'integer',
    ];

    public function model()
    {
        return $this->belongsTo(CarModel::class, 'car_model_id');
    }
}
