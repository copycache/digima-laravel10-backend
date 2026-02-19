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
        Schema::create('tbl_cart', function (Blueprint $table) {
            $table->id('cart_id');
            $table->string('cart_key');
            $table->integer('cart_item_id');
            $table->integer('cart_item_quantity');
            $table->string('cart_created')->nullable();
            $table->string('cart_updated')->nullable();
            $table->tinyInteger('cart_status')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_cart');
    }
};
