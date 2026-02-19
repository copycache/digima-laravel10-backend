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
        if (!Schema::hasTable('tbl_share_link_settings')) {
            Schema::create('tbl_share_link_settings', function (Blueprint $table) {
                $table->id('share_link_settings_id');
                $table->double('share_link_maximum_income');
                $table->integer('share_link_maximum_register_per_day');
                $table->double('share_link_income_per_registration');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_share_link_settings');
    }
};
