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
        Schema::create('tbl_signup_bonus_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('slot_id');
            $table->unsignedInteger('sponsor_id');
            $table->unsignedInteger('membership_id');
            $table->string('date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_signup_bonus_logs');
    }
};
