<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarGeneration extends Model
{
    public function model()
    {
        return $this->belongsTo(CarModel::class, 'car_model_id');
    }
}
