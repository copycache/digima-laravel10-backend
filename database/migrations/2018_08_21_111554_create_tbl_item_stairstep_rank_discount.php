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
        Schema::create('tbl_item_stairstep_rank_discount', function (Blueprint $table) {
            $table->id('item_stairstep_rank_discount_id');
            $table->integer('stairstep_rank_id');
            $table->integer('item_id');
            $table->double('discount')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_item_stairstep_rank_discount');
    }
};
