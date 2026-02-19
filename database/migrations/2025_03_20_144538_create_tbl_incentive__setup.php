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
        Schema::create('tbl_incentive_setup', function (Blueprint $table) {
            $table->id('setup_id');
            $table->unsignedInteger('item_id')->nullable();
            $table->double("number_of_purchase")->default(0);
            $table->unsignedInteger("reward_item_id")->nullable();
            $table->boolean('archive')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_incentive_setup');
    }
};
