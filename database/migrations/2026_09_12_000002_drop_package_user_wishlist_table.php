<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Wishlist removed from the client (2026-09-12): the
     * `/api/wishlist*` endpoints, controller, and User relation are gone,
     * so the pivot table goes too.
     */
    public function up(): void
    {
        Schema::dropIfExists('package_user_wishlist');
    }

    public function down(): void
    {
        Schema::create('package_user_wishlist', function ($table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('package_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'package_id']);
        });
    }
};
