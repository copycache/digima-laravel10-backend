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
        Schema::create('tbl_binary_settings', function (Blueprint $table) {
            $table->id('binary_settings_id');
            $table->tinyInteger('auto_placement');
            $table->string('auto_placement_type');
            $table->tinyInteger('member_disable_auto_position');
            $table->string('member_default_position');
            $table->tinyInteger('strong_leg_retention');
            $table->integer('gc_pairing_count');
            $table->integer('cycle_per_day');
            $table->integer('crossline')->default(0);
            $table->integer('included_binary_repurchase')->default(0);
            $table->integer('gc_paring_amount')->default(0);
            $table->integer('amount_binary_limit')->default(0);
            $table->integer('strong_leg_limit_points')->default(0);
            $table->integer('sponsor_matching_cycle')->default(1);
            $table->double('sponsor_matching_limit')->default(0);
            $table->integer('mentors_matching_cycle')->default(1);
            $table->double('mentors_matching_limit')->default(0);
            $table->smallInteger('binary_points_enable')->default(0);
            $table->double('binary_points_minimum_conversion')->default(0);
            $table->smallInteger('mentors_points_enable')->default(0);
            $table->double('mentors_points_minimum_conversion')->default(0);
            $table->integer('binary_extreme_position')->default(0);
            $table->integer('sponsor_selection')->default(1);
            $table->integer('show_slot_tracker')->default(1);
            $table->integer('show_earnings_tracker')->default(1);
            $table->integer('show_earnings_tracker_per_cycle')->default(1);
            $table->integer('binary_limit_type')->default(1);
            $table->integer('binary_maximum_points_per_level_enable')->default(0);
            $table->integer('binary_maximum_slot_per_level_enable')->default(0);
            $table->integer('binary_required_direct_enable')->default(0);
            $table->string('binary_max_limit_based_by')->default('membership');
            $table->string('mentors_level_based_by')->default('membership');
            $table->boolean('binary_auto_placement_enable')->default(false);
            $table->integer('binary_auto_placement_time')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_binary_settings');
    }
};
