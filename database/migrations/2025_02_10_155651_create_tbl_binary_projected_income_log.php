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
        Schema::create('tbl_binary_projected_income_log', function (Blueprint $table) {
            $table->id('log_id');
            $table->unsignedInteger('slot_id');
            $table->unsignedInteger('membership_id')->nullable();
            $table->unsignedInteger('cause_slot_id');
            $table->unsignedInteger('cause_membership_id')->nullable();
            $table->integer('cause_level')->default(0);
            $table->double("wallet_amount")->default(0);
            $table->integer("status")->default(0);
            $table->string('date_status_change')->nullable();
            $table->string('date_created')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_binary_projected_income_log');
    }
};
