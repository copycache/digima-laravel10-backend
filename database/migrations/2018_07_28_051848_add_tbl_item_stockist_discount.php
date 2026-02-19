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
        Schema::create('tbl_item_stockist_discount', function (Blueprint $table) {
            $table->id('item_stockist_discount_id');
            $table->unsignedInteger('stockist_level_id')->nullable();
            $table->unsignedInteger('item_id')->nullable();
            $table->double('discount')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_item_stockist_discount');
    }
};
