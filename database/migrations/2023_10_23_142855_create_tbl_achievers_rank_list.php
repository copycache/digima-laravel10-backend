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
        Schema::create('tbl_achievers_rank_list', function (Blueprint $table) {
            $table->id('list_id');
            $table->integer('slot_id');
            $table->integer('rank_id');
            $table->string('left_downline');
            $table->string('right_downline');
            $table->dateTime('qualified_date');
            $table->integer('status')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_achievers_rank_list');
    }
};
