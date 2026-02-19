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
        if (!Schema::hasTable('tbl_stairstep_distribute')) {
            Schema::create('tbl_stairstep_distribute', function (Blueprint $table) {
                $table->id('stairstep_distribute_id');
                $table->dateTime('stairstep_distribute_date_start');
                $table->dateTime('stairstep_distribute_end_start');
                $table->double('stairstep_personal_pv')->default(0);
                $table->double('stairstep_required_personal_pv')->default(0);
                $table->double('stairstep_group_pv')->default(0);
                $table->string('status')->default('');
                $table->double('stairstep_override_amount')->default(0);
                $table->double('stairstep_override_points')->default(0);
                $table->double('stairstep_multiplier')->default(1);
                $table->dateTime('stairstep_date_distributed');
                $table->unsignedInteger('slot_id')->nullable();
                $table->unsignedInteger('distribute_full_id')->nullable();
                $table->integer('current_rank_id')->default(0);
            });
        }

        if (!Schema::hasTable('tbl_stairstep_distribute_full')) {
            Schema::create('tbl_stairstep_distribute_full', function (Blueprint $table) {
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
        Schema::dropIfExists('tbl_stairstep_distribute_full');
        Schema::dropIfExists('tbl_stairstep_distribute');
    }
};
