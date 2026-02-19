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
        Schema::create('tbl_unilevel_or_distribute_full', function (Blueprint $table) {
            $table->id('distribute_full_id');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->dateTime('distribution_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_unilevel_or_distribute_full');
    }
};
