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
                $table->unsignedInteger('placement_parent_id');
                $table->unsignedInteger('placement_child_id');
                $table->integer('placement_level');
                $table->integer('board_level');
                $table->string('placement_position');
                $table->tinyInteger('to_grad')->default(0);
                $table->tinyInteger('graduated')->default(0);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_mlm_board_placement');
    }
};
