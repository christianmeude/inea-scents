<?php

use App\Services\DuplicateCatalogMerger;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Heal duplicate catalog rows left by non-idempotent seeding.
     *
     * Generic (no hardcoded ids): every name that appears more than once
     * keeps a single survivor — packages: most scent links, tie-break
     * highest id; scents: most package links, tie-break lowest id.
     * Bookings and pivot links repoint BEFORE deletes because
     * bookings.package_id is cascadeOnDelete. Safe no-op when clean.
     */
    public function up(): void
    {
        (new DuplicateCatalogMerger)();
    }

    public function down(): void
    {
        // Data merge is irreversible by design; nothing to undo.
    }
};
