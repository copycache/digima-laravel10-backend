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
        Schema::create('tbl_shipping_fee_matrix', function (Blueprint $table) {
            $table->id('shipping_fee_matrix_id');
            $table->integer('shipping_fee_increment')->default(1);
            $table->double('shipping_fee_increment_amount')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_shipping_fee_matrix');
    }
};
