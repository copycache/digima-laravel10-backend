<?php
namespace App\Globals;

use DB;
use App\Models\Tbl_vortex_settings;
use App\Models\Tbl_vortex_slot;
use App\Models\Tbl_vortex_token_log;
use App\Models\Tbl_slot;
use App\Models\Tbl_currency;
use App\Models\Tbl_membership;
use Carbon\Carbon;

use App\Globals\Log;


class Vortex
{
	public static function insert_token($slot_id,$slot_cause_id,$plan,$amount)
	{
		$insert["slot_id"]			= $slot_id;
		$insert["slot_cause_id"]	= $slot_cause_id;
		$insert["plan_type"]		= $plan;
		$insert["vortex_amount"]	= $amount;
		$insert["date_created"]		= Carbon::now();

		Tbl_vortex_token_log::insert($insert);

		Self::check_token($slot_id);
	}

	public static function check_token($slot_id)
	{
		$settings = Tbl_vortex_settings::first();

		if($settings)
		{
			if($settings->vortex_token_required != 0 && $settings->vortex_slot_required != 0)
			{
				$total = Tbl_vortex_token_log::where("slot_id",$slot_id)->sum("vortex_amount");

				while($total >= $settings->vortex_token_required)
				{
					$insert["slot_id"]			= $slot_id;
					$insert["slot_cause_id"]	= $slot_id;
					$insert["plan_type"]		= "VORTEX_SLOT";
					$insert["vortex_amount"]	= $settings->vortex_token_required * -1;
					$insert["date_created"]		= Carbon::now();

					Tbl_vortex_token_log::insert($insert);

					$slot_owner 					= Tbl_slot::where("slot_id",$slot_id)->first() ? Tbl_slot::where("slot_id",$slot_id)->first()->slot_owner : 0;
					$insert_slot["owner_id"]		= $slot_owner;
					$insert_slot["cause_slot_id"]	= $slot_id;
					$insert_slot["date_created"]	= Carbon::now();
					Tbl_vortex_slot::insert($insert_slot);

					Self::check_slot();

					$total = Tbl_vortex_token_log::where("slot_id",$slot_id)->sum("vortex_amount");
				}
			}
		} 
	}

	public static function check_slot()
	{
		$settings = Tbl_vortex_settings::first();
		if($settings)
		{
			$vortex   = Tbl_vortex_slot::where("graduated",0)->orderBy("vortex_slot_id","ASC")->first();
			if($vortex)
			{
				$required_count = ($vortex->vortex_slot_id * $settings->vortex_slot_required) + 1; 

				$total_slot     = Tbl_vortex_slot::count();

				if($total_slot >= $required_count)
				{
					$update["graduated"]      = 1;
					$update["date_graduated"] = Carbon::now();
					Tbl_vortex_slot::where("vortex_slot_id",$vortex->vortex_slot_id)->update($update);
					$slot_membership = Tbl_slot::where("slot_id",$vortex->cause_slot_id)->first();
					if($slot_membership)
					{
						$slot_membership = Tbl_membership::where("membership_id",$slot_membership->slot_membership)->first();
						if($slot_membership)
						{

							$token_reward    = $slot_membership->vortex_gc_income;
							if($token_reward != 0)
							{
		                        $gc_currency = Tbl_currency::where("currency_abbreviation","GC")->first();
		                        if($gc_currency)
		                        {
		                            $currency_id   = $gc_currency->currency_id;

									Log::insert_wallet($vortex->cause_slot_id,$token_reward,"VORTEX_TOKEN",$currency_id);
		                            Log::insert_earnings($vortex->cause_slot_id,$token_reward,"VORTEX_TOKEN","VORTEX PLACEMENT",$vortex->cause_slot_id,"",0,$currency_id);
		                        }  
							}
						}
					}
				}
			}
		}
	}
}
