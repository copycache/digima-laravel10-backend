<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tbl_payout_charges', function (Blueprint $table) {
            $table->id('payout_charges_id');
            $table->double('payout_charges_charge')->default(0);
            $table->double('payout_charges_tax')->default(0);
            $table->double('payout_charges_giftcard')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_payout_charges');
    }
};
