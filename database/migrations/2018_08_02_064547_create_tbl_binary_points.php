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
        Schema::create('tbl_binary_points', function (Blueprint $table) {
            $table->id('binary_points_id');
            $table->unsignedInteger('binary_points_slot_id');
            $table->double('binary_receive_left')->default(0);
            $table->double('binary_receive_right')->default(0);
            $table->double('binary_old_left')->default(0);
            $table->double('binary_old_right')->default(0);
            $table->double('binary_new_left')->default(0);
            $table->double('binary_new_right')->default(0);
            $table->double('binary_points_income')->default(0);
            $table->double('binary_points_flushout')->default(0);
            $table->string('binary_points_trigger')->default('');
            $table->unsignedInteger('binary_cause_slot_id')->nullable();
            $table->unsignedInteger('binary_cause_membership_id')->nullable();
            $table->integer('binary_cause_level')->default(0);
            $table->dateTime('binary_points_date_received');
            $table->double('gc_gained')->default(0);
            $table->double('flushout_points_left')->default(0);
            $table->double('flushout_points_right')->default(0);
            $table->double('binary_points_projected_income')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_binary_points');
    }
};
