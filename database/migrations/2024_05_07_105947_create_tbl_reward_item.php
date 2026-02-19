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
        Schema::create('tbl_reward_items', function (Blueprint $table) {
            $table->id();
            $table->string('item_name');
            $table->double('price');
            $table->string('thumbnail')->nullable();
            $table->integer('archive')->default(0);
            $table->timestamps();
            $table->unsignedInteger('currency_id')->nullable();
            $table->unsignedInteger('membership_id')->nullable();
            $table->string("item_name_upgraded")->nullable();
            $table->boolean("is_upgrade_for_prime_refund")->default(false);
            $table->double("commission")->default(0);
            $table->double("commission_upgraded")->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_reward_items');
    }
};
