<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tbl_dragonpay_transaction', function (Blueprint $table) {
            $table->id();
            $table->text('ordered_item')->nullable();
            $table->string('vat')->nullable();
            $table->unsignedInteger('buyer_slot_id')->nullable();
            $table->integer('cashier_user_id')->nullable();
            $table->string('from')->nullable();
            $table->string('delivery_method')->nullable();
            $table->string('picked_up')->nullable();
            $table->string('change')->nullable();
            $table->string('manager_discount')->nullable();
            $table->string('remarks')->nullable();
            $table->string('address')->nullable();
            $table->unsignedInteger('cashier_method')->nullable();
            $table->string('payment_given')->nullable();
            $table->string('status')->nullable();
            $table->string('dragonpay_txnid')->nullable();
            $table->string('dragonpay_refno')->nullable();
            $table->string('dragonpay_status')->nullable();
            $table->text('dragonpay_message')->nullable();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->string('dragonpay_charged')->nullable()->default(0);
            $table->string('date_accomplished')->nullable();
            $table->string('subtotal')->default(0)->nullable();
            $table->string('grandtotal')->default(0)->nullable();
            $table->string('voucher')->default(0);
            $table->string('courier')->nullable();
            $table->string('shipping_fee')->default(0);
            $table->string('other_discount')->default(0);
            $table->string('for_approval_trans_no')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_dragonpay_transaction');
    }
};
