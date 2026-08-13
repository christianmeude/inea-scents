<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PackageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'inclusions' => $this->inclusions ?? [],
            'pax_options' => array_map('intval', $this->pax_options ?? []),
            'freebies' => $this->freebies ?? [],
            'price' => (float) $this->price,
            'rating' => (float) $this->rating,
            'reviews_count' => (int) $this->reviews_count,
            'images' => $this->images ?? [],
            'gallery_images' => $this->gallery_images ?? [],
            'scents' => ScentResource::collection($this->whenLoaded('scents') ?? []),
            'created_at' => (string) ($this->created_at ?? ''),
            'updated_at' => (string) ($this->updated_at ?? ''),
        ];
    }
}
