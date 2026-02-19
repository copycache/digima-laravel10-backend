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
        Schema::create('tbl_milestone_pairing_points_setup', function (Blueprint $table) {
            $table->id('points_setup_id');
            $table->integer('membership_id')->nullable();
            $table->integer('milestone_pairing_left')->default(0);
            $table->integer('milestone_pairing_right')->default(0);
            $table->decimal('milestone_pairing_bonus', 10, 2)->default(0);
            $table->tinyInteger('archive')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_milestone_pairing_points_setup');
    }
};
