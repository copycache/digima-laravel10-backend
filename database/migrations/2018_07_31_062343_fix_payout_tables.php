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
        Schema::dropIfExists('tbl_payout_method');
        Schema::dropIfExists('tbl_payout_type');

        Schema::create('tbl_payout_type', function (Blueprint $table) {
            $table->id('payout_type_id');
            $table->string('payout_type_name');
            $table->string('payout_type_code');
            $table->tinyInteger('archived')->default(0);
        });

        Schema::create('tbl_payout_method', function (Blueprint $table) {
            $table->id('payout_method_id');
            $table->string('payout_method_name');
            $table->string('payout_method_type');
            $table->double('payout_method_fee')->default(0);
            $table->string('payout_method_fee_type')->nullable();
            $table->text('payout_method_image')->nullable();
            $table->tinyInteger('archived')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_payout_method');
        Schema::dropIfExists('tbl_payout_type');
    }
};
