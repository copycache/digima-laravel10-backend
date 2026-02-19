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
        Schema::create('tbl_milestone_points_log', function (Blueprint $table) {
            $table->id();
            $table->integer('points_slot_id')->index();
            $table->decimal('points_receive_left', 10, 2)->default(0);
            $table->decimal('points_receive_right', 10, 2)->default(0);
            $table->integer('group_id')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_milestone_points_log');
    }
};
