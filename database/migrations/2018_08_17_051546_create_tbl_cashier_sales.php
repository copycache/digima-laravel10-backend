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
        Schema::create('tbl_cashier_sales', function (Blueprint $table) {
            $table->id('cashier_sales_id');
            $table->text('items');
            $table->double('subtotal');
            $table->string('discount_type');
            $table->double('change')->default(0);
            $table->integer('cashier_id');
            $table->text('payment_issued');
            $table->dateTime('sales_date_transacted');
            $table->integer('transaction_currency');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_cashier_sales');
    }
};
