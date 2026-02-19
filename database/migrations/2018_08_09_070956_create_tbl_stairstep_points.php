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
        Schema::create('tbl_stairstep_points', function (Blueprint $table) {
            $table->id('stairstep_points_id');
            $table->unsignedInteger('stairstep_points_slot_id');
            $table->double('stairstep_points_amount');
            $table->string('stairstep_points_type');
            $table->unsignedInteger('stairstep_points_cause_id')->nullable();
            $table->unsignedInteger('stairstep_points_cause_membership_id')->nullable();
            $table->integer('stairstep_points_cause_level')->default(0);
            $table->string('stairstep_points_date_created');
            $table->double('stairstep_override_points');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_stairstep_points');
    }
};
