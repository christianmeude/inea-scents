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
            'inclusions' => \App\Support\PackageSanitizer::strings($this->inclusions),
            'pax_options' => \App\Support\PackageSanitizer::paxOptions($this->pax_options),
            'freebies' => \App\Support\PackageSanitizer::strings($this->freebies),
            'price' => (float) $this->price,
            'rating' => (float) $this->rating,
            'reviews_count' => (int) $this->reviews_count,
            'images' => \App\Support\PackageSanitizer::strings($this->images),
            'gallery_images' => \App\Support\PackageSanitizer::strings($this->gallery_images),
            'scents' => ScentResource::collection($this->whenLoaded('scents')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

}
