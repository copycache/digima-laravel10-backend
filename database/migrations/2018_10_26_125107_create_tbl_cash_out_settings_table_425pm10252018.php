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
        Schema::create('tbl_cash_out_settings', function (Blueprint $table) {
            $table->id('cash_out_settings_id');
            $table->integer('cash_out_settings_per_day');
            $table->integer('cash_out_settings_per_date');
            $table->integer('kyc_required')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_cash_out_settings');
    }
};
