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
        Schema::create('tbl_prime_refund_points_log', function (Blueprint $table) {
            $table->id('log_id');
            $table->unsignedInteger('slot_id')->nullable();
            $table->unsignedInteger('membership_id')->nullable();
            $table->unsignedInteger('cause_slot_id')->nullable();
            $table->unsignedInteger('cause_membership_id')->nullable();
            $table->double('points')->default(0);
            $table->double('flushout_points')->default(0);
            $table->double('commission')->default(0);
            $table->boolean('status')->default(false);
            $table->string('date_created')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_prime_refund_points_log');
    }
};
