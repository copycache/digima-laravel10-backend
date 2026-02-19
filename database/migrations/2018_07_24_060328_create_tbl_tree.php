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
        Schema::create('tbl_tree_sponsor', function (Blueprint $table) {
            $table->id('tree_sponsor_id');
            $table->unsignedInteger('sponsor_parent_id');
            $table->unsignedInteger('sponsor_child_id');
            $table->unsignedInteger('sponsor_level');
        });

        Schema::create('tbl_tree_placement', function (Blueprint $table) {
            $table->id('tree_placement_id');
            $table->unsignedInteger('placement_parent_id');
            $table->unsignedInteger('placement_child_id');
            $table->unsignedInteger('placement_level');
            $table->string('placement_position');
            $table->string('position_type')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_tree_placement');
        Schema::dropIfExists('tbl_tree_sponsor');
    }
};
