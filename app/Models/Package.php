<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    protected $fillable = [
        'name',
        'description',
        'inclusions',
        'pax_options',
        'freebies',
        'price',
        'images',
    ];

    protected $casts = [
        'inclusions' => 'array',
        'pax_options' => 'array',
        'freebies' => 'array',
        'images' => 'array',
        'price' => 'decimal:2',
    ];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
