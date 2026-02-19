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
        Schema::create('tbl_cashback_points', function (Blueprint $table) {
            $table->id('cashback_points_id');
            $table->unsignedInteger('cashback_points_slot_id');
            $table->double('cashback_points_amount');
            $table->string('cashback_points_type');
            $table->unsignedInteger('cashback_points_cause_id')->nullable();
            $table->unsignedInteger('cashback_points_cause_membership_id')->nullable();
            $table->integer('cashback_points_cause_level')->default(0);
            $table->string('cashback_points_date_created');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_cashback_points');
    }
};
