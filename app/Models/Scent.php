<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @OA\Schema(
 *     schema="Scent",
 *     title="Scent",
 *     description="Scent model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Lavender"),
 *     @OA\Property(property="description", type="string", example="A calming floral scent."),
 *     @OA\Property(property="image_url", type="string", example="https://example.com/lavender.jpg"),
 *     @OA\Property(property="is_available", type="boolean", example=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class Scent extends Model
{
    protected $fillable = [
        'name',
        'description',
        'image_url',
        'is_available',
    ];

    protected $casts = [
        'is_available' => 'boolean',
    ];

    public function packages(): BelongsToMany
    {
        return $this->belongsToMany(Package::class);
    }
}
