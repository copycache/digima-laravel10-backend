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
        Schema::create('tbl_currency_conversion', function (Blueprint $table) {
            $table->id('currency_conversion_id');
            $table->string('currency_conversion_from');
            $table->string('currency_conversion_to');
            $table->string('currency_conversion_rate')->default('0');
            $table->integer('currency_system_conversion')->default(0);
            $table->timestamps();
            $table->tinyInteger('currency_conversion_enable')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_currency_conversion');
    }
};
