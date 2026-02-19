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
        Schema::create('tbl_claimed_reward_items', function (Blueprint $table) {
            $table->id();
            $table->integer('slot_id');
            $table->integer('reward_item');
            $table->string('status');
            $table->string('claimed_at')->nullable();
            $table->string('approved_at')->nullable();
            $table->string('cancelled_at')->nullable();
            $table->double('reward_price')->default(0);
            $table->unsignedInteger('currency_id')->nullable();
            $table->unsignedInteger('membership_id')->nullable();
            $table->string('account_number')->nullable();
            $table->string('account_name')->nullable();
            $table->string('proof_of_payment')->nullable();
            $table->boolean("upgrade_prime_reward")->default(false);
            $table->double("reward_commission")->default(0);
            $table->integer("membership_transfer")->default(0);
            $table->integer("product_transfer")->default(0);
            $table->integer("auto_activate_product_code")->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_claimed_reward_items');
    }
};
