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
        Schema::create('tbl_item_rating', function (Blueprint $table) {
            $table->id('item_rate_id');
            $table->double('item_rate')->default(0);
            $table->unsignedInteger('item_id')->default(0);
            $table->unsignedInteger('user_id')->default(0);
            $table->text('item_review')->nullable();
            $table->integer('item_is_disabled')->default(0);
            $table->string('item_rate_created');
            $table->string('item_rate_order_number')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_item_rating');
    }
};
