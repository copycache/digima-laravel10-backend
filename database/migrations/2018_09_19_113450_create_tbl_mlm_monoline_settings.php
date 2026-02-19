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
        Schema::create('tbl_mlm_monoline_settings', function (Blueprint $table) {
            $table->id('monoline_settings_id');
            $table->integer('membership_id');
            $table->double('max_price');
            $table->double('monoline_percent');
            $table->double('monoline_points');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_mlm_monoline_settings');
    }
};
