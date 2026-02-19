<?php
namespace App\Globals;

use Illuminate\Support\Facades\DB;
use App\Models\Tbl_wallet;
use App\Models\Tbl_currency;
use App\Models\Tbl_slot;
use App\Globals\Log;

use Carbon\Carbon;
class Wallet
{
	public static function update_wallet($slot_id,$amount,$currency_id = 0)
	{
		$currency = Tbl_currency::where("currency_id",$currency_id)->first();
		if(!$currency)
		{
			$currency = Tbl_currency::where("currency_default",1)->first();
		}

		if($currency)
		{
			$wallet = Tbl_wallet::where("slot_id",$slot_id)->where("currency_id",$currency->currency_id)->first();
			if($wallet)
			{
				$update["wallet_amount"] = Tbl_wallet::where("slot_id",$slot_id)->where("currency_id",$currency->currency_id)->first()->wallet_amount + $amount;
				Tbl_wallet::where("slot_id",$slot_id)->where("currency_id",$currency->currency_id)->update($update);
			}
			else
			{
				$insert["wallet_amount"]	= $amount;
				$insert["slot_id"]			= $slot_id;
				$insert["currency_id"]		= $currency->currency_id;
				$insert["wallet_address"]	= Self::generateWalletAddress($currency->currency_abbreviation . Carbon::now(), 33);
				Tbl_wallet::insert($insert);
			}
		}

	}

	public static function generateSlotWalletAddress($slot_id)
	{
		$currency = Tbl_currency::where("archive", 0)->get();
		foreach ($currency as $key => $value) 
		{
			$wallet = Tbl_wallet::where("slot_id", $slot_id)->where("currency_id", $value->currency_id)->first();
			if(!$wallet)
			{
				$insert_wallet["wallet_amount"] = 0;
				$insert_wallet["slot_id"] =  $slot_id;
				$insert_wallet["currency_id"] = $value->currency_id;
				$insert_wallet["wallet_address"] = Self::generateWalletAddress($value->currency_abbreviation . Carbon::now(), 33);
				Tbl_wallet::insert($insert_wallet);
			}
			else
			{
				if(!$wallet->wallet_address)
				{
					Tbl_wallet::where("wallet_id", $wallet->wallet_id)->update(["wallet_address" => Self::generateWalletAddress($value->currency_abbreviation . Carbon::now(), 33)]);
				}
			}
		}
	}

	public static function generateWalletAddress($passphrase, $length)
	{
		return substr(hash('sha256', $passphrase), $length * -1);
	}

	public static function get_wallet_default($slot_id)
	{
		$wallet_info = null;
		$currency    = Tbl_currency::where("currency_default",1)->first();

		if($currency)
		{
			$wallet = Tbl_wallet::where("slot_id",$slot_id)->where("currency_id",$currency->currency_id)->first();
			if(!$wallet)
			{
				$insert["wallet_amount"]	= 0;
				$insert["slot_id"]			= $slot_id;
				$insert["currency_id"]		= $currency->currency_id;
				$insert["wallet_address"]	= Self::generateWalletAddress($currency->currency_abbreviation . Carbon::now(), 33);
				Tbl_wallet::insert($insert);
			}


			$wallet_info = Tbl_wallet::where("slot_id",$slot_id)->where("currency_id",$currency->currency_id)->first();
		}

		return $wallet_info;
	}
}
