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
        Schema::create('tbl_fivestar_rank_mentors_level', function (Blueprint $table) {
            $table->integer('level')->default(0);
            $table->unsignedInteger('fivestar_rank_id')->nullable();
            $table->double('mentors_percentage')->default(0);
            $table->double('mentors_direct_required')->default(0);
            $table->unsignedInteger('membership_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_fivestar_rank_mentors_level');
    }
};
