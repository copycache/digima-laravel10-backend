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
        Schema::create('tbl_watch_earn_settings', function (Blueprint $table) {
            $table->id('watch_earn_settings_id');
            $table->double('watch_earn_maximum_amount')->default(0);
            $table->double('watch_earn_video_amount')->default(0);
            $table->double('watch_earn_video_max')->default(0);
        });

        Schema::create('tbl_video', function (Blueprint $table) {
            $table->id('video_id');
            $table->string('video_title');
            $table->text('video_desc')->nullable();
            $table->string('video_url');
            $table->integer('video_sequence');
            $table->double('video_is_archived')->default(0);
            $table->string('type')->default('upload');
            $table->string('video_url_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_video');
        Schema::dropIfExists('tbl_watch_earn_settings');
    }
};
