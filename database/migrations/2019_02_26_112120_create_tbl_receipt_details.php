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
        Schema::create('tbl_receipt_details', function (Blueprint $table) {
            $table->id('receipt_details_id');
            $table->text('title')->nullable();
            $table->text('tin')->nullable();
            $table->text('details')->nullable();
            $table->text('disclaimer')->nullable();
            $table->tinyInteger('claim_code')->nullable();
            $table->tinyInteger('payment_type')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_receipt_details');
    }
};
