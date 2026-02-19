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
        Schema::create('tbl_vortex_settings', function (Blueprint $table) {
            $table->integer('vortex_slot_required');
            $table->double('vortex_token_required');
            $table->double('vortex_token_reward');
        });

        Schema::create('tbl_membership_vortex', function (Blueprint $table) {
            $table->integer('membership_id');
            $table->integer('membership_entry_id');
            $table->double('membership_vortex_token');
        });

        Schema::table('tbl_membership', function (Blueprint $table) {
            $table->double('vortex_registered_token')->default(0);
            $table->double('vortex_gc_income')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_membership', function (Blueprint $table) {
            $table->dropColumn(['vortex_registered_token', 'vortex_gc_income']);
        });
        Schema::dropIfExists('tbl_membership_vortex');
        Schema::dropIfExists('tbl_vortex_settings');
    }
};
