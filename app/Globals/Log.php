<?php
namespace App\Globals;

use App\Models\Tbl_points_log;
use App\Models\Tbl_wallet_log;
use App\Models\Tbl_earning_log;
use App\Models\Tbl_currency;
use App\Models\Tbl_slot;
use App\Models\Tbl_binary_points;
use App\Models\Tbl_stairstep_points;
use App\Models\Tbl_unilevel_points;
use App\Models\Tbl_override_points;
use App\Models\Tbl_cashback_points;
use App\Models\Tbl_personal_cashback_points;
use App\Models\Tbl_unilevel_or_points_logs;
use App\Models\Tbl_lockdown_logs;
use App\Models\Tbl_mlm_lockdown_plan;
use App\Models\Tbl_mlm_plan;
use App\Models\Tbl_other_settings;
use App\Models\Tbl_flushout_log;
use App\Models\Tbl_income_limit_settings;
use App\Models\Tbl_income_limit_flushout_logs;
use App\Models\Tbl_milestone_points_log;

use Carbon\Carbon;

use App\Globals\Wallet;

class Log
{
	public static function insert_wallet($slot_id,$amount,$plan,$currency_id = 0, $transaction_id = null, $date = null)
	{
		$lock["slot_id"] 		= $slot_id;
		$lock["amount"] 		= $amount;
		$lock["plan"] 			= $plan;
		$lock["currency_id"] 	= $currency_id;
		$lock["transaction_id"] = $transaction_id;
		$amount                 = Self::income_limit($slot_id,$amount);
		$is_lockdown = Self::lockdown_logs($lock);
		if($is_lockdown == false)
		{
			$wallet_log_id = 0;
			if($currency_id == 0)
			{
				$currency_default = Tbl_currency::where("currency_default",1)->first();
				if($currency_default)
				{
					$currency_id = $currency_default->currency_id;
				}
				else
				{
					$currency_id = null;
				}
			}
			
			if($amount >= 0)
			{
				$entry = "DEBIT";
			}
			else
			{
				$entry = "CREDIT";
			}
	
			// Created By: Centy - 10-27-2023
			if($plan == 'ACHIEVERS RANK')
			{
				$running_balance = Tbl_wallet_log::where("wallet_log_slot_id",$slot_id)->where("wallet_log_details","ACHIEVERS RANK")->sum("wallet_log_amount");

			} else {

				$running_balance = Tbl_wallet_log::where("wallet_log_slot_id",$slot_id)->where("wallet_log_details",'!=',"ACHIEVERS RANK")->where("currency_id",$currency_id)->sum("wallet_log_amount");
			}

			$insert["wallet_log_slot_id"]             = $slot_id;
			$insert["wallet_log_amount"]              = $amount;
			$insert["wallet_log_details"]             = $new_plan = trim(preg_replace('/_/', ' ', $plan));
			$insert["wallet_log_type"]                = $entry;
			$insert["wallet_log_running_balance"]     = $running_balance + $amount;
			$insert["wallet_log_date_created"]        = $date ? $date : Carbon::now();
			$insert["transaction_id"]        		  = $transaction_id;
			$insert["currency_id"]                    = $currency_id;
	
			if($amount == 0 && $plan == "BINARY")
			{
	
			}
			else
			{
				if($amount != 0 )
				{
					$wallet_log_id = Tbl_wallet_log::insertGetId($insert);
			// Created By: Centy - 10-27-2023		
					if($plan != 'ACHIEVERS RANK')
					{
						Wallet::update_wallet($slot_id,$amount,$currency_id);
					}
					return $wallet_log_id;
				}
			}
		}
	}

	public static function insert_earnings($slot_id,$amount,$plan,$entry,$cause_id,$details, $level = 0,$currency_id = 0, $date = null)
	{
		$lock["slot_id"] 		= $slot_id;
		$lock["amount"] 		= $amount;
		$lock["plan"] 			= $plan;
		$lock["currency_id"] 	= $currency_id;
		$lock["entry"]		 	= $entry;
		$lock["cause_id"]		= $cause_id;
		$lock["details"]		= $details;
		$lock["level"]		 	= $level;
		$is_lockdown = Self::lockdown_logs($lock);
		if($is_lockdown == false)
		{
			if($currency_id == 0)
			{
				$currency_default = Tbl_currency::where("currency_default",1)->first();
				if($currency_default)
				{
					$currency_id = $currency_default->currency_id;
				}
				else
				{
					$currency_id = 0;
				}
			}
		
			if($cause_id) {
				$cause_info									       = Tbl_slot::where("slot_id",$cause_id)->first();
			} 
			
			$insert_earning["earning_log_slot_id"]             = $slot_id;
			$insert_earning["earning_log_amount"]              = $amount;
			$insert_earning["earning_log_plan_type"]           = $new_plan = trim(preg_replace('/_/', ' ', $plan));
			$insert_earning["earning_log_entry_type"]          = $entry;
			$insert_earning["earning_log_cause_id"]            = $cause_id;
			$insert_earning["earning_log_cause_membership_id"] = $cause_id ? ($cause_info->slot_membership == 0 ? null : $cause_info->slot_membership) : null;
			$insert_earning["earning_log_cause_level"] 		   = $level;
			$insert_earning["earning_log_date_created"]        = $date ? $date : Carbon::now();
			$insert_earning["earning_log_currency_id"]         = $currency_id;
	
			if($amount == 0 && $plan == "BINARY")
			{
	
			}
			else
			{
				if($amount != 0)
				{
					return Tbl_earning_log::insertGetId($insert_earning);
				} else {
					return 0;
				}
			}
		}
	}

	public static function insert_points($slot_id,$amount,$type,$cause_id, $level = 0)
	{
		$lock["slot_id"] 		= $slot_id;
		$lock["amount"] 		= $amount;
		$lock["type"] 			= $type;
		$lock["cause_id"]		= $cause_id;
		$lock["level"]		 	= $level;
		$is_lockdown = Self::lockdown_logs($lock);
		if($is_lockdown == false)
		{
			$cause_info = Tbl_slot::where("slot_id",$cause_id)->first();
	
			if(Tbl_points_log::where("points_log_type",$type)->first())
			{
				$running_balance = Tbl_points_log::where("points_log_type",$type)->where("points_log_slot_id",$slot_id)->sum("points_log_amount") + $amount;
			}
			else
			{
				$running_balance = 0;
			}
	
			if($amount >= 0)
			{
				$balance_type = "Debit";
			}
			else
			{
				$balance_type = "Credit";
			}
	
			$insert["points_log_slot_id"]				= $slot_id;				
			$insert["points_log_amount"]				= $amount;				
			$insert["points_log_type"]					= $type;
			$insert["points_log_cause_id"]				= $cause_id;
			$insert["points_log_cause_membership_id"]	= $cause_info->slot_membership;
			$insert["points_log_cause_level"]			= $level;
			$insert["points_log_date_created"]			= Carbon::now();
			$insert["running_balance"]			        = $running_balance;
			$insert["balance_type"]			            = $balance_type;
	
			Tbl_points_log::insert($insert);
		}

	}

	public static function insert_stairstep_points($slot_id,$amount,$type,$cause_id, $level = 0,$override = 0)
	{
		$lock["slot_id"] 		= $slot_id;
		$lock["amount"] 		= $amount;
		$lock["type"] 			= $type;
		$lock["cause_id"]		= $cause_id;
		$lock["level"]		 	= $level;
		$lock["override"]		= $override;
		$is_lockdown = Self::lockdown_logs($lock);
		if($is_lockdown == false)
		{
			$cause_info = Tbl_slot::where("slot_id",$cause_id)->first();
	
			$insert["stairstep_points_slot_id"]				= $slot_id;				
			$insert["stairstep_points_amount"]				= $amount;				
			$insert["stairstep_points_type"]				= $type;
			$insert["stairstep_points_cause_id"]			= $cause_id;
			$insert["stairstep_points_cause_membership_id"]	= $cause_info->slot_membership;
			$insert["stairstep_points_cause_level"]			= $level;
			$insert["stairstep_points_date_created"]		= Carbon::now();
			$insert["stairstep_override_points"]		    = $override;
	
			Tbl_stairstep_points::insert($insert);
		}
	}

	public static function insert_unilevel_points($slot_id,$amount,$type,$cause_id, $level = 0, $item_id = null)
	{
		$lock["slot_id"] 		= $slot_id;
		$lock["amount"] 		= $amount;
		$lock["type"] 			= $type;
		$lock["cause_id"]		= $cause_id;
		$lock["level"]		 	= $level;
		$lock["item_id"]		= $item_id;
		$is_lockdown = Self::lockdown_logs($lock);
		if($is_lockdown == false)
		{
			$cause_info = Tbl_slot::where("slot_id",$cause_id)->first();
	
			$insert["unilevel_points_slot_id"]				= $slot_id;				
			$insert["unilevel_points_amount"]				= $amount;				
			$insert["unilevel_points_type"]				    = $type;
			$insert["unilevel_points_cause_id"]			    = $cause_id;
			$insert["unilevel_points_cause_membership_id"]	= $cause_info->slot_membership;
			$insert["unilevel_points_cause_level"]			= $level;
			$insert["unilevel_item_id"]					    = $item_id;
			$insert["unilevel_points_date_created"]		    = Carbon::now();
	
			Tbl_unilevel_points::insert($insert);
		}
	}


	public static function insert_override_points($slot_id,$amount)
	{
		$lock["slot_id"] 		= $slot_id;
		$lock["amount"] 		= $amount;
		$lock["type"] 			= "OVERRIDE_POINTS";
		$is_lockdown = Self::lockdown_logs($lock);
		if($is_lockdown == false)
		{
			$insert["slot_id"]							= $slot_id;				
			$insert["override_amount"]					= $amount;				
			$insert["distributed"]						= 0;
			$insert["override_points_date_created"]		= Carbon::now();
	
			Tbl_override_points::insert($insert);
		}		
	}
	
	public static function insert_cashback_points($slot_id,$amount,$type,$cause_id, $level = 0)
	{
		$lock["slot_id"] 		= $slot_id;
		$lock["amount"] 		= $amount;
		$lock["type"] 			= $type;
		$lock["cause_id"]		= $cause_id;
		$lock["level"]		 	= $level;
		$is_lockdown = Self::lockdown_logs($lock);
		if($is_lockdown == false)
		{
			$cause_info = Tbl_slot::where("slot_id",$cause_id)->first();
	
			$insert["cashback_points_slot_id"]				= $slot_id;				
			$insert["cashback_points_amount"]				= $amount;				
			$insert["cashback_points_type"]				    = $type;
			$insert["cashback_points_cause_id"]			    = $cause_id;
			$insert["cashback_points_cause_membership_id"]	= $cause_info->slot_membership;
			$insert["cashback_points_cause_level"]			= $level;
			$insert["cashback_points_date_created"]		    = Carbon::now();
	
			Tbl_cashback_points::insert($insert);
		}

	}

	public static function insert_unilevel_or_points_logs($slot_id,$amount,$type,$cause_id, $level = 0)
	{
		$lock["slot_id"] 		= $slot_id;
		$lock["amount"] 		= $amount;
		$lock["type"] 			= $type;
		$lock["cause_id"]		= $cause_id;
		$lock["level"]		 	= $level;
		$is_lockdown = Self::lockdown_logs($lock);
		if($is_lockdown == false)
		{
			$cause_info = Tbl_slot::where("slot_id",$cause_id)->first();
	
			$insert["unilevel_or_points_slot_id"]				= $slot_id;				
			$insert["unilevel_or_points_amount"]				= $amount;				
			$insert["unilevel_or_points_type"]				    = $type;
			$insert["unilevel_or_points_cause_id"]			    = $cause_id;
			$insert["unilevel_or_points_cause_membership_id"]	= $cause_info->slot_membership;
			$insert["unilevel_or_points_cause_level"]			= $level;
			$insert["unilevel_or_points_date_created"]		    = Carbon::now();
	
			Tbl_unilevel_or_points_logs::insert($insert);
		}
	}

	public static function insert_binary_points($slot_id,$receive,$old,$new,$cause_id,$log_earnings,$log_flushout,$level,$trigger,$gc_gained = 0,$flushout_points,$binary_repurchase_pts,$date = null, $projected_income = 0)
	{
		$lock["slot_id"] 		= $slot_id;
		$lock["type"] 			= "BINARY_POINTS";
		$is_lockdown = Self::lockdown_logs($lock);
		if($is_lockdown == false)
		{
			$cause_info = Tbl_slot::where("slot_id",$cause_id)->first();
	
			$insert["binary_points_slot_id"]		= $slot_id;					
			$insert["binary_receive_left"]			= $receive["left"];
			$insert["binary_receive_right"]			= $receive["right"];
			$insert["binary_old_left"]				= $old["left"];
			$insert["binary_old_right"]				= $old["right"];
			$insert["binary_new_left"]				= $new["left"];
			$insert["binary_new_right"]				= $new["right"];
			$insert["binary_points_income"]			= $log_earnings;
			$insert["binary_points_projected_income"] = $projected_income;
			$insert["binary_points_flushout"]		= $log_flushout;
			$insert["binary_points_trigger"]		= $trigger;
			$insert["binary_cause_slot_id"]			= $cause_info->slot_id;
			$insert["binary_cause_membership_id"]	= $cause_info->slot_membership;
			$insert["binary_cause_level"]			= $level;
			$insert["binary_points_date_received"]	= $date ? $date : Carbon::now();
			$insert["gc_gained"]				    = $gc_gained;
			$insert["flushout_points_left"]			= $flushout_points["left"];
			$insert["flushout_points_right"]		= $flushout_points["right"];
	
			Tbl_binary_points::insert($insert);
		}
	}

	public static function get_earning_amount($slot_id)
	{
		$amount = Tbl_earning_log::where("earning_log_slot_id",$slot_id)->sum("earning_log_amount");

		return $amount;
	}
	public static function insert_personal_cashback_points($slot_id,$amount)
	{
		$lock["slot_id"] 		= $slot_id;
		$lock["amount"] 		= $amount;
		$lock["type"] 			= "PERSONAL_CASHBACK_POINTS";
		$is_lockdown = Self::lockdown_logs($lock);
		if($is_lockdown == false)
		{
			$insert["slot_id"]									= $slot_id;				
			$insert["personal_cashback_points"]					= $amount;				
			$insert["distributed"]								= 0;
			$insert["personal_cashback_points_date_created"]	= Carbon::now();
	
			Tbl_personal_cashback_points::insert($insert);
			$_CP = Tbl_slot::where("slot_id",$slot_id)->select("slot_cashback_points")->first()->slot_cashback_points;
			$CP = $_CP + $amount;
			Tbl_slot::where("slot_id",$slot_id)->update(["slot_cashback_points"=>$CP]);
		}		
	}
	public static function lockdown_logs($data)
	{
		$is_lockdown = false;
		$enabled 	 = Tbl_other_settings::where("key","lockdown_enable")->first() ? Tbl_other_settings::where("key","lockdown_enable")->first()->value : 0; 

		if($enabled == 1)
		{	
			$slot_id 					    = Tbl_slot::where("slot_id",$data["slot_id"])->first();
			$slot_id->maintained_until_date = $slot_id->maintained_until_date == null ? Carbon::now() : $slot_id->maintained_until_date;
			$date_today                     = Carbon::now();
			$plan 		                    = isset($data["plan"]) ? $data["plan"] : null;

			if($plan == null )
			{
				if(isset($data["type"]))
				{
					if($data["type"] == "UNILEVEL_PPV" || $data["type"] == "UNILEVEL_GPV")
					{
						$plan = "UNILEVEL";
					}
					else if($data["type"] == "STAIRSTEP_PPV" || $data["type"] == "STAIRSTEP_GPV" || $data["type"] == "OVERRIDE_POINTS") 
					{
						$plan = "STAIRSTEP";
					}
					else if($data["type"] == "UNILEVEL_ORABELLA_PV") 
					{
						$plan = "UNILEVEL_OR";
					}
					else if($data["type"] == "MONOLINE_POINTS") 
					{
						$plan = "MONOLINE";
					}
					else if($data["type"] == "PERSONAL_CASHBACK_POINTS") 
					{
						$plan = "PERSONAL_CASHBACK";
					}
					else if($data["type"] == "BINARY_POINTS") 
					{
						$plan = "BINARY";
					}
					else if($data["type"] == "UNIVERSAL_POINTS") 
					{
						$plan = "UNIVERSAL_POOL_BONUS";
					}
					else 
					{
						$plan = "Where_are_you_going";
					}
				}
			}
			if($plan == "DIRECT BONUS")
			{
				$plan = "DIRECT";
			}
			else if($plan == "MENTORS_BONUS")
			{
				$plan = "BINARY";
			}
			else if($plan == "GLOBAL_POOL_BONUS")
			{
				$plan = "GLOBAL_POOL_BONUS";
			}

			$legit_plan = Tbl_mlm_plan::where("mlm_plan_code",$plan)->first();
			if ($legit_plan != null) 
			{
				$lockdown_plan = Tbl_mlm_lockdown_plan::plan()->where("mlm_plan_code",$plan)->first();
				if($slot_id->maintained_until_date && $lockdown_plan)
				{
					if ($slot_id->maintained_until_date >= $date_today) 
					{
						if($lockdown_plan->is_lockdown_enabled == 1)
						{
							$is_lockdown = false; 
						}
					}
					else 
					{
						if($lockdown_plan->is_lockdown_enabled == 1)
						{
							$is_lockdown = true;
						}
					}
				}

				if($is_lockdown == true)
				{
					$currency_id = isset($data["currency_id"]) ? $data["currency_id"] : null;
					if($currency_id == 0 || $currency_id == null)
					{
						$currency_default = Tbl_currency::where("currency_default",1)->first();
						if($currency_default)
						{
							$currency_id = $currency_default->currency_id;
						}
						else
						{
							$currency_id = null;
						}
					}

					$logs["slot_id"] 		= $data["slot_id"];
					$logs["plan"] 			= isset($plan) ? $plan : null;
					$logs["entry"] 			= isset($data["entry"]) ? $data["entry"] : null;
					$logs["type"] 			= isset($data["type"]) ? $data["type"] : null;
					$logs["details"] 		= isset($data["details"]) ? $data["details"] : null;
					$logs["currency_id"] 	= $currency_id;
					$logs["level"] 			= isset($data["level"]) ? $data["level"] : 0;
					$logs["item_id"] 		= isset($data["item_id"]) ? $data["item_id"] : null;
					$logs["transaction_id"] = isset($data["transaction_id"]) ? $data["transaction_id"] : null;
					$logs["cause_id"] 		= isset($data["cause_id"]) ? $data["cause_id"] : null;
					$logs["amount"] 		= isset($data["amount"]) ? $data["amount"] : null;
					$logs["override"] 		= isset($data["override"]) ? $data["override"] : null;
					$logs["created_at"] 	= Carbon::now();
		
					Tbl_lockdown_logs::insert($logs);
				}
			}
		}


		return $is_lockdown;
	}

	public static function flushout_logs($amount,$log_id)
	{
		$insert["flushout_amount"]    = $amount;
		$insert["from_wallet_log_id"] = $log_id; 
		Tbl_flushout_log::insert($insert);
	}

	public static function income_limit($slot_id,$amount)
	{
		$check = Tbl_income_limit_settings::first() ? Tbl_income_limit_settings::first()->income_limit_status : 'disable';

		if($check == 'enable') {
			
			$settings = Tbl_income_limit_settings::first();
			if($settings->income_limit_cycle == 'daily') {

				$date_from = Carbon::now()->startofday();
				$date_to   = Carbon::now()->endofday();
				$total_amount  =  Tbl_earning_log::where('earning_log_slot_id',$slot_id)->whereDate("earning_log_date_created",">=",$date_from)->whereDate("earning_log_date_created","<=",$date_to)->sum('earning_log_amount');

			}
			else if($settings->income_limit_cycle == 'weekly') {

				$date_from = Carbon::now()->startofweek();
				$date_to   = Carbon::now()->endofweek();
				$total_amount  =  Tbl_earning_log::where('earning_log_slot_id',$slot_id)->whereDate("earning_log_date_created",">=",$date_from)->whereDate("earning_log_date_created","<=",$date_to)->sum('earning_log_amount');

			}
			else if($settings->income_limit_cycle == 'monthly') {

				$date_from = Carbon::now()->startofmonth();
				$date_to   = Carbon::now()->endofmonth();
				$total_amount  =  Tbl_earning_log::where('earning_log_slot_id',$slot_id)->whereDate("earning_log_date_created",">=",$date_from)->whereDate("earning_log_date_created","<=",$date_to)->sum('earning_log_amount');

			}
			else {

				$total_amount  =  Tbl_earning_log::where('earning_log_slot_id',$slot_id)->sum('earning_log_amount');

			}

			if($settings->income_limit != 0){

				
				if($total_amount < $settings->income_limit) {

					$new_total_amount = $total_amount + $amount;

					if($new_total_amount > $settings->income_limit) {

						$diff  = $new_total_amount - $settings->income_limit;
						$new_amount = $amount - $diff;
						Self::income_limit_flushout_logs($slot_id,$diff);
					}
					else {

						$new_amount =  $amount;

					}
				}
				else {

					$new_amount =  0;
				}
			}
					
		}
		else {

			$new_amount =  $amount;
		}


		// dd($amount,$new_amount);
		return $new_amount;
	}
	
	public static function income_limit_flushout_logs($slot_id,$amount)
	{
		$insert["flushout_income_amount"]    = $amount;
		$insert["flushout_income_slot_id"]   = $slot_id; 
		Tbl_income_limit_flushout_logs::insert($insert);
	}

	public static function insert_milestone_points($slot_id, $receive, $old, $new,$cause_id,$log_earnings,$log_flushout,$level,$trigger,$flushout_points,$binary_repurchase_pts)
	{
		if (self::lockdown_logs(['slot_id' => $slot_id, 'type' => 'MILESTONE_POINTS'])) {
        	return;
    	}
		$slot_info = Tbl_slot::find($slot_id);
		$cause_info = Tbl_slot::find($cause_id);
		if (!$slot_info || !$cause_info) {
			return; // Optionally handle missing slot data
		}
		// dd($slot_id,$slot_info->slot_membership,$cause_info->slot_id,$cause_info->slot_membership,$level,$trigger,$receive['left'],$receive['right'],$old['left'],$old['right'],$new['left'],$new['right'],$log_earnings,$flushout_points['left'],$flushout_points['right'],$log_flushout);
		Tbl_milestone_points_log::insert([
			'points_slot_id'             => $slot_id,
			'points_membership_id'       => $slot_info->slot_membership,
			'points_cause_slot_id'       => $cause_info->slot_id,
			'points_cause_membership_id' => $cause_info->slot_membership,
			'points_cause_level'         => $level,
			'points_trigger'             => $trigger,
			'points_receive_left'        => $receive['left'],
			'points_receive_right'       => $receive['right'],
			'points_old_left'            => $old['left'],
			'points_old_right'           => $old['right'],
			'points_new_left'            => $new['left'],
			'points_new_right'           => $new['right'],
			'points_income'              => $log_earnings,
			'points_flushout_left' 		 => $flushout_points['left'],
			'points_flushout_right' 	 => $flushout_points['right'],
			'points_flushout_income'	 => $log_flushout,
			'points_date_received'		 => Carbon::now()
		]);
	}
}
