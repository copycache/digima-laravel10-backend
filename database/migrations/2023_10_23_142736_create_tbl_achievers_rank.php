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
        Schema::create('tbl_achievers_rank', function (Blueprint $table) {
            $table->id('achievers_rank_id');
            $table->integer('achievers_rank_level')->default(0);
            $table->string('achievers_rank_name');
            $table->double('achievers_rank_binary_points_left')->default(0);
            $table->double('achievers_rank_binary_points_right')->default(0);
            $table->double('achievers_rank_reward')->default(0);
            $table->tinyInteger('archive')->default(0);
            $table->dateTime('achievers_rank_date_created');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_achievers_rank');
    }
};
