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
        Schema::create('tbl_cash_in_proofs', function (Blueprint $table) {
            $table->id('cash_in_proof_id');
            $table->string('cash_in_slot_code');
            $table->string('cash_in_member_name');
            $table->unsignedInteger('cash_in_method_id');
            $table->string('cash_in_currency');
            $table->double('cash_in_charge')->default(0);
            $table->double('cash_in_receivable')->default(0);
            $table->double('cash_in_payable')->default(0);
            $table->longText('cash_in_proof');
            $table->string('cash_in_status')->default('pending');
            $table->dateTime('cash_in_date');
            $table->text('cash_in_message')->nullable();
            $table->string('cash_in_wallet')->default('MLM WALLET');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_cash_in_proofs');
    }
};
