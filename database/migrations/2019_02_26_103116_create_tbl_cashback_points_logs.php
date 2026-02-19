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
        if (!Schema::hasTable('tbl_personal_cashback_points')) {
            Schema::create('tbl_personal_cashback_points', function (Blueprint $table) {
                $table->id('personal_cashback_points_id');
                $table->unsignedInteger('slot_id');
                $table->double('personal_cashback_points')->default(0);
                $table->tinyInteger('distributed')->default(0);
                $table->dateTime('personal_cashback_points_date_created');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_personal_cashback_points');
    }
};
