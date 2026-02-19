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
        Schema::create('tbl_unilevel_distribute', function (Blueprint $table) {
            $table->id('unilevel_distribute_id');
            $table->dateTime('unilevel_distribute_date_start');
            $table->dateTime('unilevel_distribute_end_start');
            $table->double('unilevel_personal_pv')->default(0);
            $table->double('unilevel_required_personal_pv')->default(0);
            $table->double('unilevel_group_pv')->default(0);
            $table->string('status')->default('');
            $table->double('unilevel_amount')->default(0);
            $table->double('unilevel_multiplier')->default(0);
            $table->dateTime('unilevel_date_distributed');
            $table->unsignedInteger('slot_id')->nullable();
            $table->unsignedInteger('distribute_full_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_unilevel_distribute');
    }
};
