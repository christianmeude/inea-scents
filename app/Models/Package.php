<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @OA\Schema(
 *     schema="Package",
 *     title="Package",
 *     description="Package model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Romantic Getaway"),
 *     @OA\Property(property="description", type="string", example="A nice package for couples."),
 *     @OA\Property(property="inclusions", type="array", @OA\Items(type="string")),
 *     @OA\Property(property="pax_options", type="array", @OA\Items(type="integer")),
 *     @OA\Property(property="freebies", type="array", @OA\Items(type="string")),
 *     @OA\Property(property="price", type="number", format="float", example=199.99),
 *     @OA\Property(property="rating", type="number", format="float", example=4.5),
 *     @OA\Property(property="reviews_count", type="integer", example=120),
 *     @OA\Property(property="images", type="array", @OA\Items(type="string")),
 *     @OA\Property(property="gallery_images", type="array", @OA\Items(type="string")),
 *     @OA\Property(property="scents", type="array", @OA\Items(ref="#/components/schemas/Scent")),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
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
