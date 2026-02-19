<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblWalletLog extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_wallet_log', function (Blueprint $table) 
        {
            $table->increments('wallet_log_id');
            $table->integer('wallet_log_slot_id')->unsigned();
            $table->double('wallet_log_amount');
            $table->string('wallet_log_details');
            $table->string('wallet_log_type');
            $table->double('wallet_log_running_balance');
            $table->string('wallet_log_date_created');
            $table->integer('currency_id')->unsigned()->nullable();
            
            
            
            $table->integer('transaction_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
