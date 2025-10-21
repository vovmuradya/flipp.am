<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ListingFieldValue extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $fillable = ['listing_id', 'field_id', 'value'];
}
