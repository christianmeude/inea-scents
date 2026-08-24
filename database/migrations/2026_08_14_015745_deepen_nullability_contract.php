<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('packages')->whereNull('inclusions')->update(['inclusions' => '[]']);
        DB::table('packages')->whereNull('pax_options')->update(['pax_options' => '[]']);
        DB::table('packages')->whereNull('freebies')->update(['freebies' => '[]']);
        DB::table('packages')->whereNull('images')->update(['images' => '[]']);
        DB::table('packages')->whereNull('gallery_images')->update(['gallery_images' => '[]']);
        DB::table('packages')->whereNull('rating')->update(['rating' => 0.00]);

        DB::table('bookings')->whereNull('customer_email')->update(['customer_email' => 'no-email@example.com']);
        DB::table('bookings')->whereNull('pax')->update(['pax' => 1]);
        DB::table('bookings')->whereNull('payment_method')->update(['payment_method' => 'unknown']);

        Schema::table('packages', function (Blueprint $table) {
            $table->json('inclusions')->nullable(false)->default('[]')->change();
            $table->json('pax_options')->nullable(false)->default('[]')->change();
            $table->json('images')->nullable(false)->default('[]')->change();
            $table->json('gallery_images')->nullable(false)->default('[]')->change();
            $table->decimal('rating', 3, 2)->nullable(false)->default(0.00)->change();
        });

        DB::statement('ALTER TABLE packages ALTER COLUMN freebies TYPE json USING freebies::json');
        DB::statement('ALTER TABLE packages ALTER COLUMN freebies SET NOT NULL');
        DB::statement("ALTER TABLE packages ALTER COLUMN freebies SET DEFAULT '[]'");

        Schema::table('bookings', function (Blueprint $table) {
            $table->string('customer_email')->nullable(false)->change();
            $table->integer('pax')->nullable(false)->default(1)->change();
            $table->string('payment_method')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->json('inclusions')->nullable()->change();
            $table->json('pax_options')->nullable()->change();
            $table->json('images')->nullable()->change();
            $table->json('gallery_images')->nullable()->change();
            $table->decimal('rating', 3, 2)->nullable()->change();
        });

        DB::statement('ALTER TABLE packages ALTER COLUMN freebies DROP DEFAULT');
        DB::statement('ALTER TABLE packages ALTER COLUMN freebies DROP NOT NULL');
        DB::statement('ALTER TABLE packages ALTER COLUMN freebies TYPE text USING freebies::text');

        Schema::table('bookings', function (Blueprint $table) {
            $table->string('customer_email')->nullable()->change();
            $table->integer('pax')->nullable()->change();
            $table->string('payment_method')->nullable()->change();
        });
    }
};
