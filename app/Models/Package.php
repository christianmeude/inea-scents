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
        new OAT\Property(property: 'pax_prices', type: 'object', example: ['50' => 4499.00]),
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
        'pax_prices',
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
        'pax_prices' => 'array',
        'freebies' => 'array',
        'images' => 'array',
        'price' => 'decimal:2',
        'rating' => 'decimal:2',
        'reviews_count' => 'integer',
        'gallery_images' => 'array',
    ];

    /**
     * Headcounts this package is sold at, from the tier map keys.
     * Falls back to the legacy pax_options list for rows predating tiers.
     *
     * @return int[]
     */
    public function tierPax(): array
    {
        $map = $this->pax_prices ?? [];
        if (is_array($map) && count($map) > 0) {
            return array_map('intval', array_keys($map));
        }

        return array_map('intval', (array) ($this->pax_options ?? []));
    }

    /**
     * Server-side price for a headcount. Null when the pax has no tier —
     * callers treat null as "reject", never as free.
     */
    public function priceForPax(int $pax): ?float
    {
        $map = $this->pax_prices ?? [];
        if (! is_array($map) || count($map) === 0) {
            return null;
        }
        foreach ($map as $key => $price) {
            if ((int) $key === $pax) {
                return (float) $price;
            }
        }

        return null;
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function scents(): BelongsToMany
    {
        return $this->belongsToMany(Scent::class);
    }
}
