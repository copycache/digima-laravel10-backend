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
        Schema::create('tbl_unilevel_matrix_bonus_settings', function (Blueprint $table) {
            $table->id();
            $table->integer('matrix_level')->default(0);
            $table->integer('matrix_placement_start_at')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_unilevel_matrix_bonus_settings');
    }
};
