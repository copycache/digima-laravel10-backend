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
        Schema::create('tbl_livewell_rank', function (Blueprint $table) {
            $table->id('livewell_rank_id');
            $table->integer('livewell_rank_level')->default(0);
            $table->string('livewell_rank_name');
            $table->unsignedInteger('livewell_bind_membership');
            $table->tinyInteger('archive')->default(0);
            $table->dateTime('livewell_rank_date_created');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_livewell_rank');
    }
};
