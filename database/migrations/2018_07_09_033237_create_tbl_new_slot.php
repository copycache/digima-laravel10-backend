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
        if (!Schema::hasTable('tbl_slot')) {
            Schema::create('tbl_slot', function (Blueprint $table) {
                $table->id('slot_id');
                $table->string('slot_no')->default('');
                $table->unsignedInteger('slot_owner');
                $table->unsignedInteger('slot_membership');
                $table->unsignedInteger('slot_placement')->default(0);
                $table->string('slot_position')->default('');
                $table->unsignedInteger('slot_sponsor');
                $table->string('slot_type')->default('');
                $table->double('slot_left_points')->default(0);
                $table->double('slot_right_points')->default(0);
                $table->double('slot_wallet')->default(0);
                $table->double('slot_total_earnings')->default(0);
                $table->double('slot_total_payout')->default(0);
                $table->integer('slot_stairstep_rank')->default(0);
                $table->string('slot_pairs_per_day_date')->default('');
                $table->integer('slot_pairs_per_day')->default(0);
                $table->double('slot_personal_pv')->default(0);
                $table->double('slot_group_pv')->default(0);
                $table->unsignedInteger('slot_used_code')->default(0);
                $table->dateTime('slot_date_created');
                $table->tinyInteger('distributed')->default(0);
                $table->tinyInteger('archive')->default(0);
                $table->dateTime('slot_date_placed')->nullable();
                $table->tinyInteger('membership_inactive')->default(0);
                $table->string('slot_status')->default('active');
                $table->double('slot_override_points')->default(0);
                $table->string('meridiem')->default('');
                $table->integer('slot_sponsor_product')->default(0);
                $table->integer('slot_sponsor_member')->default(0);
                $table->tinyInteger('from_bundle')->default(0);
                $table->double('slot_cashback_points')->default(0);
                $table->integer('bonus_no')->default(0);
                $table->integer('initial_payout')->default(1);
                $table->smallInteger('global_pool_entitiled')->default(0);
                $table->integer('top_earner_status')->default(1);
                $table->integer('slot_count_id')->nullable();
                $table->string('store_name')->nullable();
                $table->integer('slot_achievers_rank')->default(0);
                $table->integer('welcome_bonus_notif')->default(0);
                $table->unsignedInteger('matrix_sponsor')->default(0);
                $table->string('matrix_position')->nullable()->default(null);
                $table->integer('slot_livewell_rank')->default(0);
                $table->string('last_binary_projected_income_reset_date')->nullable();
                $table->string('slot_id_number')->nullable();
                $table->timestamp('slot_binary_auto_position_duration')->nullable();
                $table->unsignedInteger('slot_fivestar_rank')->nullable();
            });
        }

        if (!Schema::hasColumn('tbl_slot', 'slot_group_spv')) {
            Schema::table('tbl_slot', function (Blueprint $table) {
                $table->double('slot_group_spv')->default(0);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_slot');
    }
};
