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
        Schema::table('tbl_membership', function (Blueprint $table) {
            $table->integer('global_pool_enabled')->default(0);
        });

        Schema::create('tbl_global_pool_bonus_settings', function (Blueprint $table) {
            $table->id('global_pool_bonus_id');
            $table->double('global_pool_amount')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_global_pool_bonus_settings');
        Schema::table('tbl_membership', function (Blueprint $table) {
            $table->dropColumn('global_pool_enabled');
        });
    }
};
