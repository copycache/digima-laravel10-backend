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
        Schema::create('tbl_orders', function (Blueprint $table) {
            $table->id('order_id');
            $table->text('items');
            $table->string('delivery_method');
            $table->integer('delivery_charge')->default(0);
            $table->double('subtotal');
            $table->text('buyer_name');
            $table->text('buyer_slot_code');
            $table->unsignedInteger('buyer_slot_id')->nullable();
            $table->dateTime('order_date_created');
            $table->double('grand_total');
            $table->unsignedInteger('retailer')->nullable();
            $table->string('order_from');
            $table->unsignedInteger('cashier_id')->nullable();
            $table->double('change')->default(0);
            $table->text('discount');
            $table->string('order_status')->nullable();
            $table->datetime('order_date_delivered')->nullable();
            $table->double('manager_discount')->default(0);
            $table->double('tax_amount')->default(0);
            $table->dateTime('date_status_changed')->nullable();
            $table->longText('buyer_address')->nullable();
            $table->dateTime('order_date_completed')->nullable();
            $table->unsignedInteger('payment_method')->nullable();
            $table->double('payment_tendered')->default(0);
            $table->string('dragonpay_charged')->nullable()->default(0);
            $table->string('voucher')->default(0);
            $table->string('courier')->nullable();
            $table->string('shipping_fee')->default(0);
            $table->string('other_discount')->default(0);
            $table->string('for_approval_trans_no')->nullable();
            $table->integer('shipping_fee_v2')->default(0);
            $table->integer('handling_fee')->default(0);
            $table->string('buyer_contact_number')->nullable();
            $table->string('buyer_email')->nullable();
            $table->integer('buyer_sponsor_id')->nullable();
            $table->string('receiver_name')->nullable();
            $table->string('receiver_contact')->nullable();
            $table->string('receiver_email')->nullable();
        });

        Schema::create('tbl_receipt', function (Blueprint $table) {
            $table->id('receipt_id');
            $table->text('items');
            $table->string('delivery_method');
            $table->integer('delivery_charge')->default(0);
            $table->double('subtotal');
            $table->text('buyer_name');
            $table->text('buyer_slot_code');
            $table->unsignedInteger('buyer_slot_id')->nullable();
            $table->dateTime('receipt_date_created');
            $table->double('grand_total');
            $table->string('claim_code');
            $table->tinyInteger('claimed')->default(0);
            $table->unsignedInteger('retailer')->nullable();
            $table->unsignedInteger('receipt_order_id')->nullable();
            $table->double('change')->default(0);
            $table->text('discount');
            $table->double('manager_discount')->default(0);
            $table->double('tax_amount')->default(0);
            $table->longText('buyer_address')->nullable();
            $table->unsignedInteger('payment_method')->nullable();
            $table->string('courier')->nullable();
            $table->string('transaction_number')->nullable();
            $table->string('voucher')->default(0);
            $table->string('processor_name')->nullable();
            $table->string('unclaimed_status')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_receipt');
        Schema::dropIfExists('tbl_orders');
    }
};
