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
        Schema::create('tbl_marketing_tools_subcategory', function (Blueprint $table) {
            $table->id();
            $table->integer('category_id');
            $table->string('sub_category_name')->nullable();
            $table->integer('archived')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_marketing_tools_subcategory');
    }
};
