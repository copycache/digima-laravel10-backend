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
        Schema::create('tbl_mlm_unilevel_settings', function (Blueprint $table) {
            $table->id('mlm_unilevel_settings_id');
            $table->tinyInteger('personal_as_group');
            $table->double('gpv_to_wallet_conversion');
            $table->string('personal_pv_label')->default('Personal PV');
            $table->string('group_pv_label')->default('Group PV');
            $table->string('is_dynamic')->default('normal');
            $table->integer('unilevel_complan_show_to')->default(0);
            $table->string('unilevel_level_based_by')->default('membership');
            $table->string('unilevel_maintenance_based_by')->default('membership');
        });

        Schema::create('tbl_membership_unilevel_level', function (Blueprint $table) {
            $table->integer('membership_level');
            $table->unsignedInteger('membership_id');
            $table->unsignedInteger('membership_entry_id');
            $table->double('membership_percentage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_membership_unilevel_level');
        Schema::dropIfExists('tbl_mlm_unilevel_settings');
    }
};
