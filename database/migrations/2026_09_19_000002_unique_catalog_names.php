<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Block recurrence of duplicate catalog rows at the database level.
     * Runs after the merge migration so existing dupes never violate it.
     */
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->unique('name');
        });

        Schema::table('scents', function (Blueprint $table) {
            $table->unique('name');
        });
    }

    public function down(): void
    {
        Schema::table('scents', function (Blueprint $table) {
            $table->dropUnique(['name']);
        });

        Schema::table('packages', function (Blueprint $table) {
            $table->dropUnique(['name']);
        });
    }
};
