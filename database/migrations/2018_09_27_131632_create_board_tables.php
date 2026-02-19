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
        if (!Schema::hasTable('tbl_mlm_board_placement')) {
            Schema::create('tbl_mlm_board_placement', function (Blueprint $table) {
                $table->id('mlm_board_placement_id');
                $table->unsignedInteger('placement_parent_id')->nullable();
                $table->unsignedInteger('placement_child_id')->nullable();
                $table->integer('placement_level');
                $table->integer('board_level');
                $table->string('placement_position');
                $table->tinyInteger('to_grad')->default(0);
            });
        }

        if (!Schema::hasTable('tbl_mlm_board_settings')) {
            Schema::create('tbl_mlm_board_settings', function (Blueprint $table) {
                $table->id('mlm_board_settings_id');
                $table->integer('board_depth');
                $table->integer('graduation_bonus')->default(0);
                $table->string('board_logic')->default('First In, First Out');
                $table->unsignedInteger('board_level');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_mlm_board_settings');
        Schema::dropIfExists('tbl_mlm_board_placement');
    }
};
