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
        if (!Schema::hasColumn('tbl_item', 'item_binary_pts')) {
            Schema::table('tbl_item', function (Blueprint $table) {
                $table->double('item_binary_pts')->default(0);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_item', function (Blueprint $table) {
            $table->dropColumn('item_binary_pts');
        });
    }
};
