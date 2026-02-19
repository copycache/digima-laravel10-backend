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
        Schema::create('tbl_stairstep_rank', function (Blueprint $table) {
            $table->id('stairstep_rank_id');
            $table->string('stairstep_rank_name');
            $table->double('stairstep_rank_override')->default(0);
            $table->double('stairstep_rank_personal')->default(0);
            $table->double('stairstep_rank_group')->default(0);
            $table->double('stairstep_rank_personal_all')->default(0);
            $table->double('stairstep_rank_group_all')->default(0);
            $table->tinyInteger('archive')->default(0);
            $table->dateTime('stairstep_rank_date_created');
            $table->integer('stairstep_rank_level')->default(0);
            $table->double('stairstep_commission')->default(0);
            $table->double('stairstep_advancement_bonus')->default(0);
            $table->integer('check_match_level')->default(0);
            $table->double('check_match_percentage')->default(0);
            $table->double('breakaway_level')->default(0);
            $table->double('equal_bonus')->default(0);
            $table->integer('stairstep_rank_upgrade')->default(0);
            $table->integer('stairstep_rank_name_id')->default(0);
            $table->integer('stairstep_direct_referral')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_stairstep_rank');
    }
};
