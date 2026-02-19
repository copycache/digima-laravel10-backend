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
        Schema::create('tbl_matrix_placement', function (Blueprint $table) {
            $table->id('matrix_id');
            $table->unsignedInteger('parent_id');
            $table->unsignedInteger('child_id');
            $table->unsignedInteger('level');
            $table->string('position');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_matrix_placement');
    }
};
