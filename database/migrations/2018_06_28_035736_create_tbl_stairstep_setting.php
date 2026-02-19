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
        Schema::create('tbl_stairstep_settings', function (Blueprint $table) {
            $table->id('stairstep_settings_id');
            $table->tinyInteger('personal_as_group')->default(0);
            $table->tinyInteger('live_update')->default(0);
            $table->tinyInteger('allow_downgrade')->default(0);
            $table->tinyInteger('rank_first')->default(0);
            $table->string('personal_stairstep_pv_label')->default('Accumulated Personal PV');
            $table->string('group_stairstep_pv_label')->default('Accumulated Group PV');
            $table->string('earning_label_points')->default('Override Points');
            $table->double('sgpv_to_wallet_conversion')->default(0);
            $table->double('override_multiplier')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_stairstep_settings');
    }
};
