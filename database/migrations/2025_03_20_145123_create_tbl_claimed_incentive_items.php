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
        Schema::create('tbl_claimed_incentive_items', function (Blueprint $table) {
            $table->id();
            $table->integer('slot_id')->nullable();
            $table->unsignedInteger('reward_item')->nullable();
            $table->unsignedInteger('incentive_setup_id')->nullable();
            $table->double('purchase_count')->default(0);
            $table->double('commission')->default(0);
            $table->string('status')->nullable();
            $table->timestamp('claimed_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_claimed_incentive_items');
    }
};
