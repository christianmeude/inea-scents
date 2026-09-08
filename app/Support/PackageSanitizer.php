<?php

namespace App\Support;

/**
 * Single backend-owned rule for Package list-field cleaning, serving both
 * the admin write path and the API read path.
 *
 * - Accepts arrays or JSON-encoded strings (legacy rows).
 * - strings(): trims, drops null/empty/whitespace/non-string elements.
 * - paxOptions(): coerces numeric strings and whole floats to ints,
 *   drops null/invalid/fractional/sub-1 entries.
 * Clean data passes through identically.
 */
class PackageSanitizer
{
    public static function strings(mixed $value): array
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

    public static function paxOptions(mixed $value): array
    {
        $list = is_string($value) ? json_decode($value, true) : $value;
        if (! is_array($list)) {
            return [];
        }

        $cleaned = [];
        foreach ($list as $item) {
            if (is_int($item) && $item >= 1) {
                $cleaned[] = $item;
            } elseif (is_float($item) && $item >= 1 && floor($item) == $item) {
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
