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
        Schema::create('tbl_currency', function (Blueprint $table) {
            $table->id('currency_id');
            $table->string('currency_name');
            $table->string('currency_abbreviation');
            $table->tinyInteger('currency_default')->default(0);
            $table->tinyInteger('archive')->default(0);
            $table->tinyInteger('currency_buying')->default(0);
            $table->tinyInteger('currency_enable')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_currency');
    }
};
