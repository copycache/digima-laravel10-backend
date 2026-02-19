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
        Schema::create('tbl_income_limit_settings', function (Blueprint $table) {
            $table->id('income_limit_id');
            $table->string('income_limit_status')->default('enable');
            $table->double('income_limit')->default(0);
            $table->string('income_limit_cycle')->default('daily');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_income_limit_settings');
    }
};
