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
        Schema::create('tbl_stairstep_items', function (Blueprint $table) {
            $table->id('tbl_stairstep_items_id');
            $table->unsignedInteger('stairstep_settings_id');
            $table->integer('item_qty')->default(0);
            $table->unsignedInteger('item_id');
            $table->integer('included')->default(0);
        });

        Schema::table('tbl_stairstep_settings', function (Blueprint $table) {
            $table->integer('auto_ship')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_stairstep_settings', function (Blueprint $table) {
            $table->dropColumn('auto_ship');
        });
        Schema::dropIfExists('tbl_stairstep_items');
    }
};
