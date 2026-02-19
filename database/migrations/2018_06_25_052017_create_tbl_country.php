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
        Schema::create('tbl_country', function (Blueprint $table) {
            $table->id('country_id');
            $table->string('country_name');
            $table->string('currency_code');
            $table->double('currency_conversion')->default(0);
            $table->tinyInteger('archive')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_country');
    }
};
