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
        Schema::create('tbl_survey_question', function (Blueprint $table) {
            $table->id();
            $table->string('survey_question')->nullable();
            $table->smallInteger('survey_archived')->default(0);
            $table->dateTime('survey_created_date')->nullable();
        });

        Schema::create('tbl_survey_choices', function (Blueprint $table) {
            $table->id();
            $table->integer('survey_question_id');
            $table->string('survey_choices_details');
            $table->smallInteger('survey_choices_status')->default(0);
            $table->dateTime('survey_created_date')->nullable();
        });

        Schema::create('tbl_survey_answer', function (Blueprint $table) {
            $table->id();
            $table->integer('survey_question_id');
            $table->integer('survey_choices_id');
            $table->integer('slot_id');
            $table->dateTime('survey_created_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_survey_answer');
        Schema::dropIfExists('tbl_survey_choices');
        Schema::dropIfExists('tbl_survey_question');
    }
};
