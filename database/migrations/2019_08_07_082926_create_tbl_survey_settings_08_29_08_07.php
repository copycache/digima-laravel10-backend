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
        Schema::create('tbl_survey_settings', function (Blueprint $table) {
            $table->id();
            $table->integer('survey_limit')->default(0);
            $table->integer('survey_limit_per_day')->default(0);
            $table->double('survey_amount')->default(0);
            $table->double('survey_limit_convertion')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_survey_settings');
    }
};
