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
        'image_url',
    ];

    protected $casts = [
        'inclusions' => 'array',
        'pax_options' => 'array',
        'price' => 'decimal:2',
    ];
}
