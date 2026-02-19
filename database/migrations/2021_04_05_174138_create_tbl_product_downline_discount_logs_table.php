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
        Schema::create('tbl_product_downline_discount_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('slot_id');
            $table->unsignedInteger('membership_id');
            $table->unsignedInteger('item_id');
            $table->double('discount');
            $table->string('type');
            $table->string('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_product_downline_discount_logs');
    }
};
