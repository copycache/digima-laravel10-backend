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
        Schema::create('tbl_unilevel_items', function (Blueprint $table) {
            $table->id('tbl_unilevel_items_id');
            $table->unsignedInteger('unilevel_settings_id');
            $table->integer('item_qty')->default(0);
            $table->unsignedInteger('item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_unilevel_items');
    }
};
