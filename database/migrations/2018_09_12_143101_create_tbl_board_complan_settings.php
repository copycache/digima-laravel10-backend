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
        if (!Schema::hasTable('tbl_mlm_board_settings')) {
            Schema::create('tbl_mlm_board_settings', function (Blueprint $table) {
                $table->id('mlm_board_settings_id');
                $table->integer('board_depth');
                $table->double('graduation_bonus')->default(0);
                $table->string('board_logic')->default('First In, First Out');
                $table->integer('max_board_count');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_mlm_board_settings');
    }
};
