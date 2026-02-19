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
        Schema::create('tbl_mlm_universal_pool_bonus_settings', function (Blueprint $table) {
            $table->id('universal_pool_bonus_id');
            $table->integer('membership_id');
            $table->double('max_price');
            $table->double('percent');
            $table->integer('required_direct')->default(0);
        });

        Schema::create('tbl_mlm_universal_pool_bonus_points', function (Blueprint $table) {
            $table->id('universal_pool_bonus_points_id');
            $table->integer('universal_pool_bonus_grad_stat')->default(0);
            $table->double('universal_pool_bonus_points');
            $table->double('excess_universal_pool_bonus_points');
            $table->unsignedInteger('slot_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_mlm_universal_pool_bonus_points');
        Schema::dropIfExists('tbl_mlm_universal_pool_bonus_settings');
    }
};
