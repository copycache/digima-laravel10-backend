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
        Schema::create('tbl_eloading_tab_settings', function (Blueprint $table) {
            $table->id('eloading_tab_id');
            $table->string('eloading_tab_name');
            $table->integer('eloading_tab_active')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_eloading_tab_settings');
    }
};
