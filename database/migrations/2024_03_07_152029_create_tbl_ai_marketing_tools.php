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
        Schema::create('tbl_ai_marketing_tools', function (Blueprint $table) {
            $table->id();
            $table->string('category')->nullable();
            $table->string('sub_category')->nullable();
            $table->string('title')->nullable();
            $table->text('details')->nullable();
            $table->string('thumbnail')->nullable();
            $table->json('image_link')->nullable();
            $table->string('video_link')->nullable();
            $table->integer('membership_id')->nullable();
            $table->integer('archived')->default(0);
            $table->timestamps();
            $table->json('file_link')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_ai_marketing_tools');
    }
};
