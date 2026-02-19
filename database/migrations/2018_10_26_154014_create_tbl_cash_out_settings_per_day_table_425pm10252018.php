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
        Schema::create('tbl_cash_out_settings_per_day', function (Blueprint $table) {
            $table->id('cash_out_settings_per_day_id');
            $table->string('cash_out_settings_day');
            $table->tinyInteger('day_archived')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_cash_out_settings_per_day');
    }
};
