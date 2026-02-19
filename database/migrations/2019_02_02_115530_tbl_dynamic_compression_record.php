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
        Schema::create('tbl_dynamic_compression_record', function (Blueprint $table) {
            $table->unsignedInteger('slot_id');
            $table->double('earned_points');
            $table->unsignedInteger('cause_slot_id');
            $table->integer('dynamic_level');
            $table->integer('cause_slot_level');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->dateTime('date_created');
            $table->double('cause_slot_ppv');
            $table->double('cause_slot_percentage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_dynamic_compression_record');
    }
};
