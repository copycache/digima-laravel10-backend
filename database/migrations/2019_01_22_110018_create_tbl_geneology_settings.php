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
        Schema::create('tbl_genealogy_settings', function (Blueprint $table) {
            $table->id('genealogy_settings_id');
            $table->smallInteger('show_full_name')->default(1);
            $table->smallInteger('show_slot_no')->default(1);
            $table->smallInteger('show_date_joined')->default(1);
            $table->smallInteger('show_directs_no')->default(1);
            $table->smallInteger('show_binary_points')->default(1);
            $table->smallInteger('show_maintenance_pv')->default(1);
            $table->smallInteger('show_membership')->default(1);
            $table->smallInteger('show_sponsor_username')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_genealogy_settings');
    }
};
