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
        Schema::create('tbl_fivestar_rank', function (Blueprint $table) {
            $table->id('fivestar_rank_id');
            $table->integer('fivestar_rank_level')->nullable();
            $table->string('fivestar_rank_name')->nullable();
            $table->string('fivestar_rank_thumbnail')->nullable();
            $table->unsignedInteger('fivestar_rank_bind_membership')->nullable();
            $table->double('fivestar_rank_number_of_pairs')->default(0);
            $table->double('fivestar_rank_personal_pv')->default(0);
            $table->double('fivestar_rank_group_pv')->default(0);
            $table->double('fivestar_rank_indirect_level')->default(0);
            $table->double('fivestar_rank_unilevel_level')->default(0);
            $table->double('fivestar_rank_unilevel_maintenance')->default(0);
            $table->double('fivestar_rank_binary_limit')->default(0);
            $table->double('fivestar_rank_mentors_level')->default(0);
            $table->double('archive')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_fivestar_rank');
    }
};
