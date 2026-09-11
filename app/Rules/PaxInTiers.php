<?php

namespace App\Rules;

use App\Models\Package;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Rejects headcounts the package is not sold at. Blank or off-tier pax
 * used to reach the insert and 500 on the NOT NULL column — this turns
 * them into a 422 with errors at the boundary instead.
 */
class PaxInTiers implements ValidationRule
{
    public function __construct(private Package $package) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || (is_string($value) && trim($value) === '')) {
            $fail('Select PAX from the available options.');
            return;
        }

        if (! is_numeric($value) || (int) $value < 1) {
            $fail('PAX must be at least 1.');
            return;
        }

        // Tier-less packages (no map, no legacy options) keep the legacy
        // min:1 contract — e.g. ad-hoc test rows. Tiered packages enforce
        // exact steps so price is always defined.
        $tiers = $this->package->tierPax();
        if (count($tiers) > 0 && ! in_array((int) $value, $tiers, true)) {
            $fail('Select PAX from the available options: '.implode(', ', $tiers).'.');
        }
    }
}
