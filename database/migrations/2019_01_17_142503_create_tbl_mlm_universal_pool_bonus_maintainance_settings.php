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
        Schema::create('tbl_mlm_universal_pool_bonus_maintain_settings', function (Blueprint $table) {
            $table->id('universal_pool_settings_id');
            $table->double('required_direct')->default(0);
            $table->string('binary_maintenace')->default('0');
            $table->string('maintain_date')->default('disable');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_mlm_universal_pool_bonus_maintain_settings');
    }
};
