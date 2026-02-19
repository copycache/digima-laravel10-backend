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
        Schema::create('tbl_remittance', function (Blueprint $table) {
            $table->id('remittance_id');
            $table->string('remittance_name');
            $table->tinyInteger('remittance_payout_enable')->default(0);
            $table->dateTime('remittance_date_created');
            $table->tinyInteger('archive')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_remittance');
    }
};
