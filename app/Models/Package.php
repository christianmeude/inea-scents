<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use OpenApi\Attributes as OAT;

#[OAT\Schema(
    schema: 'Package',
    title: 'Package',
    description: 'Package model',
    properties: [
        new OAT\Property(property: 'id', type: 'integer', example: 1),
        new OAT\Property(property: 'name', type: 'string', example: 'Romantic Getaway'),
        new OAT\Property(property: 'description', type: 'string', example: 'A nice package for couples.', nullable: true),
        new OAT\Property(property: 'inclusions', type: 'array', items: new OAT\Items(type: 'string')),
        new OAT\Property(property: 'pax_options', type: 'array', items: new OAT\Items(type: 'integer')),
        new OAT\Property(property: 'freebies', type: 'array', items: new OAT\Items(type: 'string')),
        new OAT\Property(property: 'price', type: 'number', format: 'float', example: 199.99),
        new OAT\Property(property: 'rating', type: 'number', format: 'float', example: 4.5),
        new OAT\Property(property: 'reviews_count', type: 'integer', example: 120),
        new OAT\Property(property: 'images', type: 'array', items: new OAT\Items(type: 'string')),
        new OAT\Property(property: 'gallery_images', type: 'array', items: new OAT\Items(type: 'string')),
        new OAT\Property(property: 'scents', type: 'array', items: new OAT\Items(ref: '#/components/schemas/Scent')),
        new OAT\Property(property: 'created_at', type: 'string', format: 'date-time', nullable: true),
        new OAT\Property(property: 'updated_at', type: 'string', format: 'date-time', nullable: true),
    ]
)]
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
        'rating',
        'reviews_count',
        'gallery_images',
    ];

    protected $casts = [
        'inclusions' => 'array',
        'pax_options' => 'array',
        'freebies' => 'array',
        'images' => 'array',
        'price' => 'decimal:2',
        'rating' => 'decimal:2',
        'reviews_count' => 'integer',
        'gallery_images' => 'array',
    ];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function scents(): BelongsToMany
    {
        return $this->belongsToMany(Scent::class);
    }
}
