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
        Schema::create('tbl_cashier_payment_method', function (Blueprint $table) {
            $table->id('cashier_payment_method_id');
            $table->string('cashier_payment_method_name');
            $table->tinyInteger('cashier_payment_method_status')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_cashier_payment_method');
    }
};
