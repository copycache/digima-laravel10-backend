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
        Schema::create('tbl_achievers_rank_claimed_history', function (Blueprint $table) {
            $table->id();
            $table->integer('slot_id');
            $table->integer('rank_id');
            $table->dateTime('claimed_date');
            $table->dateTime('approved_date')->nullable();
            $table->dateTime('rejected_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_achievers_rank_claimed_history');
    }
};
