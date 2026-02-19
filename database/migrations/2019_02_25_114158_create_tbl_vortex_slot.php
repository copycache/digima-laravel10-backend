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
        Schema::create('tbl_vortex_slot', function (Blueprint $table) {
            $table->id('vortex_slot_id');
            $table->integer('owner_id');
            $table->integer('cause_slot_id');
            $table->dateTime('date_created')->nullable();
            $table->dateTime('date_graduated')->nullable();
            $table->tinyInteger('graduated')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_vortex_slot');
    }
};
