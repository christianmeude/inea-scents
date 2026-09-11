<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->json('pax_prices')->nullable(false)->default('{}');
        });

        // Backfill legacy rows so every package has a usable tier map:
        // single tier at the scalar price for its first pax option.
        foreach (DB::table('packages')->select('id', 'price', 'pax_options')->get() as $row) {
            $options = is_string($row->pax_options) ? json_decode($row->pax_options, true) : $row->pax_options;
            $first = is_array($options) && count($options) > 0 ? (int) $options[0] : 50;
            DB::table('packages')->where('id', $row->id)->update([
                'pax_prices' => json_encode([$first => (float) $row->price]),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn('pax_prices');
        });
    }
};
