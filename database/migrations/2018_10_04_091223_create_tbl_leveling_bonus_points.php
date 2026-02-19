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
        Schema::create('tbl_leveling_bonus_points', function (Blueprint $table) {
            $table->integer('slot_id');
            $table->integer('membership_id');
            $table->integer('membership_level');
            $table->integer('left_point');
            $table->integer('right_point');
            $table->integer('claim');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_leveling_bonus_points');
    }
};
