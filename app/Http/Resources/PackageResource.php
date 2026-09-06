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
            'inclusions' => self::cleanStrings($this->inclusions),
            'pax_options' => self::cleanPaxOptions($this->pax_options),
            'freebies' => self::cleanStrings($this->freebies),
            'price' => (float) $this->price,
            'rating' => (float) $this->rating,
            'reviews_count' => (int) $this->reviews_count,
            'images' => self::cleanStrings($this->images),
            'gallery_images' => self::cleanStrings($this->gallery_images),
            'scents' => ScentResource::collection($this->whenLoaded('scents')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    /**
     * Read-boundary guard: legacy rows may hold [null]/[""] shapes.
     * Never emit null/empty elements — the mobile client's generated
     * deserializer requires List<String>.
     */
    private static function cleanStrings(mixed $value): array
    {
        $list = is_string($value) ? json_decode($value, true) : $value;
        if (! is_array($list)) {
            return [];
        }

        $cleaned = [];
        foreach ($list as $item) {
            if (is_string($item) && trim($item) !== '') {
                $cleaned[] = trim($item);
            }
        }

        return array_values($cleaned);
    }

    /**
     * Read-boundary guard: coerce numeric strings ("12") to ints so a
     * single legacy row can never poison the whole packages list.
     */
    private static function cleanPaxOptions(mixed $value): array
    {
        $list = is_string($value) ? json_decode($value, true) : $value;
        if (! is_array($list)) {
            return [];
        }

        $cleaned = [];
        foreach ($list as $item) {
            if (is_int($item) && $item >= 1) {
                $cleaned[] = $item;
            } elseif (is_float($item) && (int) $item === $item && $item >= 1) {
                $cleaned[] = (int) $item;
            } elseif (is_string($item) && trim($item) !== '' && filter_var(trim($item), FILTER_VALIDATE_INT) !== false) {
                $int = (int) trim($item);
                if ($int >= 1) {
                    $cleaned[] = $int;
                }
            }
        }

        return array_values($cleaned);
    }
}
