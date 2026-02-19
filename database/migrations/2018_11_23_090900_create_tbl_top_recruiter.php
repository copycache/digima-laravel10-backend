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
        Schema::create('tbl_top_recruiter', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('slot_id');
            $table->integer('total_recruits')->default(0);
            $table->integer('total_leads')->default(0);
            $table->dateTime('date_from')->nullable();
            $table->dateTime('date_to')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_top_recruiter');
    }
};
