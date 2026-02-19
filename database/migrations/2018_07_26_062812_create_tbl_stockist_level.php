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
        Schema::create('tbl_stockist_level', function (Blueprint $table) {
            $table->id('stockist_level_id');
            $table->string('stockist_level_discount')->default(0);
            $table->string('stockist_level_name');
            $table->datetime('stockist_level_date_created');
            $table->tinyInteger('archive')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_stockist_level');
    }
};
