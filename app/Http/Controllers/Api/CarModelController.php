<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CarModel;
use App\Models\CarBrand;
use Illuminate\Http\Request;

class CarModelController extends Controller
{
    public function getByBrand(CarBrand $brand)
    {
        return $brand->models()
            ->orderBy('name_' . app()->getLocale())
            ->get();
    }
}
