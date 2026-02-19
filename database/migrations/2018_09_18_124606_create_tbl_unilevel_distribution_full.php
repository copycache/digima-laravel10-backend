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
        if (!Schema::hasColumn('tbl_unilevel_distribute', 'distribute_full_id')) {
            Schema::table('tbl_unilevel_distribute', function (Blueprint $table) {
                $table->integer('slot_id')->nullable();
                $table->integer('distribute_full_id')->nullable();
            });
        }

        if (!Schema::hasTable('tbl_unilevel_distribute_full')) {
            Schema::create('tbl_unilevel_distribute_full', function (Blueprint $table) {
                $table->id('distribute_full_id');
                $table->dateTime('start_date');
                $table->dateTime('end_date');
                $table->dateTime('distribution_date');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_unilevel_distribute_full');
        // Note: We don't drop columns added to tbl_unilevel_distribute by default 
        // to avoid potential data loss if this migration is rolled back alone.
    }
};
