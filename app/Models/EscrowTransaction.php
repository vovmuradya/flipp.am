<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EscrowTransaction extends Model
{
    protected $fillable = [
        'hold_id',
        'listing_id',
        'buyer_id',
        'seller_id',
        'amount',
        'currency',
        'status',
        'provider',
        'meta',
        'captured_at',
        'released_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'float',
            'meta' => 'array',
            'captured_at' => 'datetime',
            'released_at' => 'datetime',
        ];
    }

    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }
}
