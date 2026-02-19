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
        Schema::create('tbl_vortex_token_log', function (Blueprint $table) {
            $table->id('vortex_token_log_id');
            $table->unsignedInteger('slot_id');
            $table->unsignedInteger('slot_cause_id');
            $table->string('plan_type');
            $table->double('vortex_amount')->default(0);
            $table->dateTime('date_created');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_vortex_token_log');
    }
};
