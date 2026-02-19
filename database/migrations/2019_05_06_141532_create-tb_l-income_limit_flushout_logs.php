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
        Schema::create('tbl_income_limit_flushout_logs', function (Blueprint $table) {
            $table->id('income_limit_flushout_logs_id');
            $table->double('flushout_income_amount')->default(0);
            $table->unsignedInteger('flushout_income_slot_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_income_limit_flushout_logs');
    }
};
