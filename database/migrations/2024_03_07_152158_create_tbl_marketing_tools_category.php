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
        Schema::create('tbl_marketing_tools_category', function (Blueprint $table) {
            $table->id();
            $table->string('category_name')->nullable();
            $table->integer('image_required')->default(0);
            $table->integer('video_required')->default(0);
            $table->integer('archived')->default(0);
            $table->timestamps();
            $table->integer('file_required')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_marketing_tools_category');
    }
};
