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
        Schema::create('tbl_mlm_board_slot', function (Blueprint $table) {
            $table->id('board_slot_id');
            $table->unsignedInteger('slot_id');
            $table->unsignedInteger('placement');
            $table->string('placement_position');
            $table->unsignedInteger('board_level')->default(1);
            $table->integer('graduated')->default(0);
        });

        Schema::table('tbl_slot', function (Blueprint $table) {
            $table->integer('current_board_level')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_slot', function (Blueprint $table) {
            $table->dropColumn('current_board_level');
        });
        Schema::dropIfExists('tbl_mlm_board_slot');
    }
};
