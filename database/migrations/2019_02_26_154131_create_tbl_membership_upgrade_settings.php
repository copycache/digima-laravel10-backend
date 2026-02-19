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
        Schema::create('tbl_membership_upgrade_settings', function (Blueprint $table) {
            $table->id('membership_upgrade_settings_id');
            $table->string('membership_upgrade_settings_method')->default('direct_downlines');
            $table->smallInteger('membership_upgrade_settings_flushout')->default(0);
        });

        Schema::table('tbl_membership', function (Blueprint $table) {
            $table->integer('required_upgrade_points')->default(0);
            $table->integer('given_upgrade_points')->default(0);
        });

        Schema::table('tbl_slot', function (Blueprint $table) {
            $table->integer('slot_upgrade_points')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_slot', function (Blueprint $table) {
            $table->dropColumn('slot_upgrade_points');
        });

        Schema::table('tbl_membership', function (Blueprint $table) {
            $table->dropColumn(['required_upgrade_points', 'given_upgrade_points']);
        });

        Schema::dropIfExists('tbl_membership_upgrade_settings');
    }
};
