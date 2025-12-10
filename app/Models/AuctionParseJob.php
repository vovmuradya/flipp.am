<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuctionParseJob extends Model
{
    protected $fillable = [
        'job_id',
        'url',
        'status',
        'result',
        'error_message',
    ];

    protected $casts = [
        'result' => 'array',
    ];
}
