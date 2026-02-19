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
        Schema::create('tbl_unilevel_points', function (Blueprint $table) {
            $table->id('unilevel_points_id');
            $table->unsignedInteger('unilevel_points_slot_id');
            $table->double('unilevel_points_amount');
            $table->string('unilevel_points_type');
            $table->unsignedInteger('unilevel_points_cause_id')->nullable();
            $table->unsignedInteger('unilevel_points_cause_membership_id')->nullable();
            $table->integer('unilevel_points_cause_level')->default(0);
            $table->string('unilevel_points_date_created');
            $table->tinyInteger('unilevel_points_distribute')->default(0);
            $table->unsignedInteger('unilevel_item_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_unilevel_points');
    }
};
