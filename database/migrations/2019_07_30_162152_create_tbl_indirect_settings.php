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
        Schema::table('tbl_slot', function (Blueprint $table) {
            $table->double('slot_indirect_wallet_points')->default(0);
        });

        Schema::create('tbl_indirect_settings', function (Blueprint $table) {
            $table->id('indirect_settings_id');
            $table->smallInteger('indirect_points_enable')->default(0);
            $table->double('indirect_points_minimum_conversion')->default(0);
            $table->string('indirect_level_based_by')->default('membership');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_indirect_settings');
        Schema::table('tbl_slot', function (Blueprint $table) {
            $table->dropColumn('slot_indirect_wallet_points');
        });
    }
};
