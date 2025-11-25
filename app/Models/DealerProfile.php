<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DealerProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'logo',
        'company_name',
        'description',
        'phone',
        'city',
        'slug',
    ];

    protected static function booted(): void
    {
        static::creating(function (DealerProfile $profile) {
            if (empty($profile->slug)) {
                $profile->slug = Str::slug($profile->company_name . '-' . Str::random(6));
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function listings()
    {
        return $this->hasMany(Listing::class, 'user_id', 'user_id');
    }
}
