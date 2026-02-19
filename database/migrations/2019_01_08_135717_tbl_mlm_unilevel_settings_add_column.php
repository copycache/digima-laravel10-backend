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
        Schema::table('tbl_mlm_unilevel_settings', function (Blueprint $table) {
            $table->integer('auto_ship')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_mlm_unilevel_settings', function (Blueprint $table) {
            $table->dropColumn('auto_ship');
        });
    }
};
