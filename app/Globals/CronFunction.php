<?php
namespace App\Globals;

use Illuminate\Support\Facades\DB;
use App\Models\Tbl_wallet;

use App\Globals\Log;

class CronFunction
{
	public static function orabella_convert()
	{
		$get = Tbl_wallet::where("currency_id",3)->where("wallet_amount","!=",0)->get();

		foreach($get as $g)
		{
			$slot_id		= $g->slot_id;
			$amount			= $g->wallet_amount * -1;
			$plan			= "ORABELLA_CONVERT";
			$currency_id 	= $g->currency_id;
			Log::insert_wallet($slot_id,$amount,$plan,$currency_id);

			$slot_id		= $g->slot_id;
			$amount			= $g->wallet_amount;
			$plan			= "ORABELLA_CONVERT";
			$currency_id 	= 1;
			Log::insert_wallet($slot_id,$amount,$plan,$currency_id);
		}
	}
}
