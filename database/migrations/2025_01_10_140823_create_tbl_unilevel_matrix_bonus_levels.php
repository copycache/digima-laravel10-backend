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
        Schema::create('tbl_unilevel_matrix_bonus_levels', function (Blueprint $table) {
            $table->integer('level');
            $table->integer('membership_id');
            $table->double('matrix_commission')->default(0);
            $table->dateTime('date_created');
            $table->integer('membership_entry_id');
            $table->integer("minimum_membership_for_realtime_commission")->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_unilevel_matrix_bonus_levels');
    }
};
