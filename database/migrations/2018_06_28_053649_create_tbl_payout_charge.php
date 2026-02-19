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
        Schema::create('tbl_payout_charge', function (Blueprint $table) {
            $table->id('payout_charge_id');
            $table->string('payout_charge_name');
            $table->string('payout_charge_status');
            $table->string('payout_charge_type');
            $table->double('payout_charge_value');
            $table->tinyInteger('archive')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_payout_charge');
    }
};
