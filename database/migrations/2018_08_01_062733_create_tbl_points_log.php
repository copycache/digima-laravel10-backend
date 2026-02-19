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
        Schema::create('tbl_points_log', function (Blueprint $table) {
            $table->id('points_log_id');
            $table->unsignedInteger('points_log_slot_id');
            $table->double('points_log_amount');
            $table->string('points_log_type');
            $table->unsignedInteger('points_log_cause_id')->nullable();
            $table->unsignedInteger('points_log_cause_membership_id')->nullable();
            $table->integer('points_log_cause_level')->default(0);
            $table->string('points_log_date_created');
            $table->double('running_balance')->default(0);
            $table->string('balance_type')->default('');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_points_log');
    }
};
