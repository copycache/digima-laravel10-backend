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
        Schema::create('tbl_membership', function (Blueprint $table) {
            $table->id('membership_id');
            $table->string('membership_name');
            $table->double('membership_price');
            $table->double('membership_gc')->default(0);
            $table->integer('membership_indirect_level')->default(0);
            $table->integer('membership_binary_level')->default(0);
            $table->integer('membership_unilevel_level')->default(0);
            $table->integer('membership_cashback_level')->default(0);
            $table->integer('membership_leveling_bonus_level')->default(0);
            $table->integer('membership_unilevel_or_level')->default(0);
            $table->integer('membership_product_share_link_level')->default(0);
            $table->integer('membership_overriding_commission_level')->default(0);
            $table->string('team_sales_bonus_level')->default(0);
            $table->dateTime('membership_date_created');
            $table->integer('membership_required_pv')->default(0);
            $table->integer('membership_required_pv_or')->default(0);
            $table->tinyInteger('archive')->default(0);
            $table->tinyInteger('enable_commission')->default(0);
            $table->integer('hierarchy')->default(0);
            $table->integer('required_directs')->default(0);
            $table->integer('membership_pairings_per_day')->default(0);
            $table->string('color')->default('#ffffff');
            $table->string('restriction')->default('none');
            $table->integer('mentors_level')->default(0);
            $table->double('cashback_percent')->default(0);
            $table->tinyInteger('enable_sponsor_matching')->default(0);
            $table->smallInteger('can_receive_points')->default(1);
            $table->smallInteger('flushout_enable')->default(0);
            $table->integer('global_pool_pv')->default(0);
            $table->string('retailer_commission')->default(0);
            $table->double('share_link_maximum_income')->default(0);
            $table->integer('share_link_maximum_register_per_day')->default(0);
            $table->double('share_link_income_per_registration')->default(0);
            $table->double('direct_cashback')->default(0);
            $table->string('sign_up_bonus')->default(0);
            $table->string('sign_up_minimum')->default(0);
            $table->string('sign_up_voucher_use')->default(0);
            $table->double('minimum_move_wallet')->default(0);
            $table->double('move_wallet_fee')->default(0);
            $table->integer('binary_placement_enable')->default(0);
            $table->double('max_points_per_level')->default(0);
            $table->double('max_earnings_per_level')->default(0);
            $table->integer('max_earnings_per_cycle')->default(0);
            $table->integer('binary_required_direct')->default(0);
            $table->integer('matrix_placement')->default(0);
            $table->integer('unilevel_matrix_level')->default(0);
            $table->integer('binary_realtime_commission')->default(1);
            $table->integer('binary_waiting_commission_reset_days')->default(0);
            $table->boolean('prime_refund_enable')->default(0);
            $table->double('prime_refund_accumulated_points')->default(0);
            $table->double('prime_refund_commission')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_membership');
    }
};
