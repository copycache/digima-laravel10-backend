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
        Schema::create('tbl_unilevel_or_points_logs', function (Blueprint $table) {
            $table->id('unilevel_or_points_id');
            $table->unsignedInteger('unilevel_or_points_slot_id');
            $table->double('unilevel_or_points_amount');
            $table->string('unilevel_or_points_type');
            $table->unsignedInteger('unilevel_or_points_cause_id')->nullable();
            $table->unsignedInteger('unilevel_or_points_cause_membership_id')->nullable();
            $table->integer('unilevel_or_points_cause_level')->default(0);
            $table->string('unilevel_or_points_date_created');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_unilevel_or_points_logs');
    }
};
