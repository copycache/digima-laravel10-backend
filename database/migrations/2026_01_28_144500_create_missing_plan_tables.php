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
        // tbl_milestone_points_setup
        Schema::create('tbl_milestone_points_setup', function (Blueprint $table) {
            $table->id('milestone_points_setup_id');
            $table->integer('membership_id')->nullable();
            $table->integer('membership_entry_id')->nullable();
            $table->decimal('milestone_points', 10, 2)->default(0);
            $table->timestamps();
        });

        // tbl_marketing_support_settings
        Schema::create('tbl_marketing_support_settings', function (Blueprint $table) {
            $table->id('marketing_support_settings_id');
            $table->integer('number_of_days_to_earn')->default(1);
            $table->integer('number_of_income')->default(1);
            $table->timestamps();
        });

        // tbl_marketing_support_setup (implied by usage in Get_plan.php)
        Schema::create('tbl_marketing_support_setup', function (Blueprint $table) {
            $table->id('marketing_support_setup_id');
            $table->integer('membership_id')->nullable();
            $table->integer('membership_entry_id')->nullable();
            $table->decimal('income', 10, 2)->default(0);
            $table->timestamps();
        });

        // tbl_leaders_support_settings
        Schema::create('tbl_leaders_support_settings', function (Blueprint $table) {
            $table->id('leaders_support_settings_id');
            $table->integer('number_of_days_to_earn')->default(1);
            $table->integer('number_of_income')->default(1);
            $table->timestamps();
        });

        // tbl_leaders_support_setup (implied by usage in Get_plan.php)
        Schema::create('tbl_leaders_support_setup', function (Blueprint $table) {
            $table->id('leaders_support_setup_id');
            $table->integer('membership_id')->nullable();
            $table->integer('membership_entry_id')->nullable();
            $table->decimal('income', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_milestone_points_setup');
        Schema::dropIfExists('tbl_marketing_support_settings');
        Schema::dropIfExists('tbl_marketing_support_setup');
        Schema::dropIfExists('tbl_leaders_support_settings');
        Schema::dropIfExists('tbl_leaders_support_setup');
    }
};
