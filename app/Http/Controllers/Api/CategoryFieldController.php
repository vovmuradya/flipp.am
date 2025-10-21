<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryFieldController extends Controller
{
    public function index(Category $category)
    {
        return response()->json($category->customFields);
    }
}
