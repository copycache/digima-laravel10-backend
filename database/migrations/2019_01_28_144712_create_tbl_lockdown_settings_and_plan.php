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
        if (!Schema::hasTable('tbl_mlm_lockdown_plan')) {
            Schema::create('tbl_mlm_lockdown_plan', function (Blueprint $table) {
                $table->id('mlm_lockdown_plan_id');
                $table->unsignedInteger('mlm_plan_code_id')->nullable();
                $table->smallInteger('is_lockdown_enabled')->default(0);
            });
        }

        if (!Schema::hasColumn('tbl_item', 'added_days')) {
            Schema::table('tbl_item', function (Blueprint $table) {
                $table->integer('added_days')->default(0);
            });
        }

        if (!Schema::hasColumn('tbl_slot', 'maintained_until_date')) {
            Schema::table('tbl_slot', function (Blueprint $table) {
                $table->dateTime('maintained_until_date')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_slot', function (Blueprint $table) {
            $table->dropColumn('maintained_until_date');
        });

        Schema::table('tbl_item', function (Blueprint $table) {
            $table->dropColumn('added_days');
        });

        Schema::dropIfExists('tbl_mlm_lockdown_plan');
    }
};
