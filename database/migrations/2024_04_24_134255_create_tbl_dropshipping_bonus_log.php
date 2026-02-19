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
        Schema::create('tbl_dropshipping_bonus_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('slot_id');
            $table->unsignedInteger('membership_id');
            $table->unsignedInteger('item_id');
            $table->double('commission')->nullable();
            $table->string('type')->nullable();
            $table->string('date')->nullable();
            $table->integer('order_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_dropshipping_bonus_logs');
    }
};
