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
        Schema::create('tbl_watched_videos', function (Blueprint $table) {
            $table->id('watched_id');
            $table->unsignedInteger('watched_slot_id');
            $table->unsignedInteger('watched_video_id');
            $table->dateTime('watch_video_date_created');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_watched_videos');
    }
};
