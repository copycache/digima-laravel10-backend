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
        Schema::create('tbl_orders_rel_item', function (Blueprint $table) {
            $table->id('orders_rel_item_id');
            $table->unsignedInteger('rel_order_id');
            $table->unsignedInteger('item_id');
            $table->integer('quantity');
        });

        Schema::create('tbl_receipt_rel_item', function (Blueprint $table) {
            $table->id('receipt_rel_item_id');
            $table->unsignedInteger('rel_receipt_id');
            $table->unsignedInteger('item_id');
            $table->integer('quantity');
            $table->double('price')->default(0);
            $table->double('subtotal')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_receipt_rel_item');
        Schema::dropIfExists('tbl_orders_rel_item');
    }
};
