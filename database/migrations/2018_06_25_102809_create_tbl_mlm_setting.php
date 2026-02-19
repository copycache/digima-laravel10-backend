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
        Schema::create('tbl_mlm_settings', function (Blueprint $table) {
            $table->id('mlm_settings_id');
            $table->string('mlm_slot_no_format');
            $table->integer('mlm_slot_no_format_type');
            $table->tinyInteger('free_registration');
            $table->tinyInteger('multiple_type_membership');
            $table->tinyInteger('gc_inclusive_membership');
            $table->tinyInteger('product_inclusive_membership');
            $table->integer('add_slot_sponsor_selection')->default(0);
            $table->integer('add_slot_automatic_sponsor')->default(0);
            $table->integer('company_account')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_mlm_settings');
    }
};
