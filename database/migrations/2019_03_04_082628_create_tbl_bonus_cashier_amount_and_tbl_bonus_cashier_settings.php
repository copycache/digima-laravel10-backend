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
        Schema::create('tbl_cashier_bonus', function (Blueprint $table) {
            $table->id('cashier_bonus_id');
            $table->integer('cashier_bonus_buy_amount');
            $table->integer('cashier_bonus_given_amount');
            $table->smallInteger('archive')->default(0);
        });

        Schema::create('tbl_cashier_bonus_settings', function (Blueprint $table) {
            $table->id('cashier_bonus_settings_id');
            $table->smallInteger('cashier_bonus_enable')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_cashier_bonus_settings');
        Schema::dropIfExists('tbl_cashier_bonus');
    }
};
