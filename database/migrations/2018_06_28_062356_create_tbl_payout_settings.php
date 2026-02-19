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
        Schema::create('tbl_payout_settings', function (Blueprint $table) {
            $table->id('payout_settings_id');
            $table->tinyInteger('encash_all_wallet_cutoff')->default(0);
            $table->double('minimum_encashment')->default(0);
            $table->tinyInteger('bank_enable_payout')->default(0);
            $table->double('bank_additional_charge')->default(0);
            $table->tinyInteger('remittance_enable_payout')->default(0);
            $table->double('remittance_additional_charge')->default(0);
            $table->tinyInteger('cheque_enable_payout')->default(0);
            $table->double('cheque_additional_charge')->default(0);
            $table->tinyInteger('cheque_allow_choose_name')->default(0);
            $table->tinyInteger('coinsph_enable_payout')->default(0);
            $table->double('coinsph_additional_charge')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_payout_settings');
    }
};
