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
        Schema::create('tbl_incentive_items', function (Blueprint $table) {
            $table->id('item_id');
            $table->string('item_name')->nullable();
            $table->integer('item_type')->default(0);
            $table->double('price')->default(0);
            $table->double('commission')->default(0);
            $table->string('thumbnail')->nullable();
            $table->integer('archive')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_incentive_items');
    }
};
