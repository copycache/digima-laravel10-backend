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
        Schema::create('tbl_lockdown_autoship_items', function (Blueprint $table) {
            $table->id('lockdown_autoship_items_id');
            $table->integer('item_qty')->default(0);
            $table->unsignedInteger('item_id');
            $table->integer('included')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_lockdown_autoship_items');
    }
};
