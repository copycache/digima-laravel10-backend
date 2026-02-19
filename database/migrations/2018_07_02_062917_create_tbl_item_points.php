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
        Schema::create('tbl_item_points', function (Blueprint $table) {
            $table->id('item_points_id');
            $table->string('item_points_key');
            $table->double('item_points_personal_pv')->default(0);
            $table->double('item_points_group_pv')->default(0);
            $table->integer('item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_item_points');
    }
};
