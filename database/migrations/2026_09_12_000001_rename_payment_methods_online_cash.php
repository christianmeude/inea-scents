<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Wire contract REQ-2: payment terms are exactly `online` + `cash`.
     * `bank_transfer` rows are test rows (owner: delete, not migrate);
     * historical `credit_card` rows are remapped so they keep matching
     * the enum after the rename.
     */
    public function up(): void
    {
        DB::table('bookings')->where('payment_method', 'bank_transfer')->delete();
        DB::table('bookings')->where('payment_method', 'credit_card')->update(['payment_method' => 'online']);
    }

    public function down(): void
    {
        DB::table('bookings')->where('payment_method', 'online')->update(['payment_method' => 'credit_card']);
    }
};
