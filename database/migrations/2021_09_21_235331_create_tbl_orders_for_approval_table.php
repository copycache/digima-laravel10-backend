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
        Schema::create('tbl_orders_for_approval', function (Blueprint $table) {
            $table->id();
            $table->string('slot_id')->nullable();
            $table->string('user_id')->nullable();
            $table->string('address')->nullable();
            $table->string('branch_id')->nullable();
            $table->string('courier')->nullable();
            $table->string('default_min_spend')->nullable();
            $table->string('default_voucher_deduct')->nullable();
            $table->string('default_voucher_status')->nullable();
            $table->string('dragonpay_charged')->nullable();
            $table->string('email_address')->nullable();
            $table->string('grandtotal')->nullable();
            $table->string('item_charged')->nullable();
            $table->text('items')->nullable();
            $table->string('method_charge')->nullable();
            $table->string('min_spend')->nullable();
            $table->string('overall_cashback')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('shipping_fee')->nullable();
            $table->string('subtotal')->nullable();
            $table->string('sum')->nullable();
            $table->string('total_item_price')->nullable();
            $table->string('voucher_deduct')->nullable();
            $table->string('item_fee')->nullable();
            $table->string('date_created')->nullable();
            $table->string('admin_status')->nullable();
            $table->string('date_approved')->nullable();
            $table->string('user_status')->nullable();
            $table->string('date_purchased')->nullable();
            $table->string('other_discount')->default(0);
            $table->string('transaction_number')->nullable();
            $table->text('remarks')->nullable();
            $table->integer('shop_status')->default(0);
            $table->string('delivery_method')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_orders_for_approval');
    }
};
