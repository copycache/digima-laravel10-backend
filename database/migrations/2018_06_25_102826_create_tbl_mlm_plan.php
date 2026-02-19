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
        Schema::create('tbl_mlm_plan', function (Blueprint $table) {
            $table->id('mlm_plan_id');
            $table->string('mlm_plan_code');
            $table->string('mlm_plan_label')->default('');
            $table->string('mlm_plan_type')->default('');
            $table->string('mlm_plan_trigger');
            $table->tinyInteger('mlm_plan_enable');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_mlm_plan');
    }
};
