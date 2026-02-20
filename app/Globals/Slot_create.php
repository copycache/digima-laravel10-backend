<?php
namespace App\Globals;

use DB;
use Hash;
use Crypt;
use Validator;
use Carbon\Carbon;

use App\Models\Tbl_audit_trail;
use App\Models\Tbl_slot_limit;
use App\Models\Tbl_slot;
use App\Models\Tbl_mlm_universal_pool_bonus_points;
use App\Models\Tbl_mlm_plan;
use App\Models\Tbl_mlm_incentive_bonus;
use App\Models\Tbl_item;
use App\Models\Tbl_currency;
use App\Models\Tbl_top_recruiter;
use App\Models\Tbl_stairstep_settings;
use App\Models\Users;

use App\Globals\Log;
use App\Globals\Stairstep;

class Slot_create
{
    public static function validate_slot_if_retailer($return,$owner_id)
    {
    	$user = Users::where("id",$owner_id)->first();
    	if($user)
    	{
    		if($user->registered_as_retailer == 1)
    		{
				$return["status_message"][$return["i"]] = "You cannot create/activate a slot on a retailer account.";
				$return["i"]++;
    		}
    	}
    	return $return;
    }

    public static function validate_membership_code($return,$code,$pin, $not_import = 1)
    {

    	if($not_import == 1)
    	{
			$check_code 	  = Code::check_membership_code_unused($code,$pin);
			if($check_code == "used")
			{
				$return["status_message"][$return["i"]] = "Code already used.";
				$return["i"]++;
			}
			else if($check_code == "not_exist")
			{
				$return["status_message"][$return["i"]] = "Code does not exists.";
				$return["i"]++;
			}
    	}

    	return $return;
    }

    public static function validate_slot_limit($return,$owner_id,$code = null,$pin = null)
    {
    	$check_bundle     = Code::get_membership_code_details($code,$pin);
        if($check_bundle)
        {
            if($check_bundle->slot_qty == 1)
            {
				$check_slot_limit = Tbl_slot_limit::where("user_id",$owner_id)->first();
				if($check_slot_limit)
				{
					if($check_slot_limit->slot_limit != 0)
					{
						if($check_slot_limit->active_slots >= $check_slot_limit->slot_limit)
						{
							$return["status_message"][$return["i"]] = "Slot limit reached";
							$return["i"]++;
						}
					}
				}
				else
				{
					$insert_limit["user_id"] 		  = $owner_id;
					$insert_limit["active_slots"]	  = 0;
					$insert_limit["slot_limit"]       = 0;
					Tbl_slot_limit::insert($insert_limit);
					$check_slot_limit =Tbl_slot_limit::where("user_id",$owner_id)->first();
					if($check_slot_limit->slot_limit != 0)
					{
						if($check_slot_limit->active_slots >= $check_slot_limit->slot_limit)
						{
							$return["status_message"][$return["i"]] = "Slot limit reached";
							$return["i"]++;
						}
					}
				}
			}
		}


		return $return;
    }

    public static function validate_required($return,$slot_owner,$slot_sponsor,$not_import = 1)
    {
		$data["slot_owner"]	    = $slot_owner;
		$data["slot_sponsor"]   = $slot_sponsor;


		if($not_import == 1)
		{
			$rules["slot_owner"]    = "required|exists:users,id";
			$rules["slot_sponsor"]  = "required|exists:tbl_slot,slot_no";

			$validator = Validator::make($data, $rules);
			if ($validator->fails())
			{
				$len = count($validator->errors()->getMessages());
				foreach ($validator->errors()->getMessages() as $key => $value)
				{
					foreach($value as $val)
					{
						$return["status_message"][$return["i"]] = $val;
						$return["i"]++;
					}
				}
			}
		}
		else if($not_import == 2)
		{
			$rules["slot_sponsor"]  = "required|exists:tbl_slot,slot_no";
			$validator = Validator::make($data, $rules);
			if ($validator->fails())
			{
				$len = count($validator->errors()->getMessages());
				foreach ($validator->errors()->getMessages() as $key => $value)
				{
					foreach($value as $val)
					{
						$return["status_message"][$return["i"]] = $val;
						$return["i"]++;
					}
				}
			}
		}
		else
		{
			$check_slot_owner = Users::where('id', $data['slot_owner'])->first();
			if(!$check_slot_owner)
			{
				$return['status_message'][$return["i"]] = 'No slot owner';
				$return["i"]++;
			}
			$check_slot_sponsor = Tbl_slot::where('slot_no', $data['slot_sponsor'])->first();
			if(!$check_slot_sponsor)
			{
				$return['status_message'][$return["i"]] = 'No slot sponsor';
				$return["i"]++;
			}
		}


		return $return;
    }

    public static function check_inactive($return,$owner_id)
    {
	    $proceed_to_inactive = 0;
		$check_inactive                    = Tbl_slot::where("slot_owner",$owner_id)->where("membership_inactive",1)->where("slot_status","active")->first();
		if($check_inactive)
		{
			if($check_inactive->membership_inactive == 1)
			{
				$proceed_to_inactive = 1;
			}
		}

		$data["check_inactive"]      = $check_inactive;
		$data["proceed_to_inactive"] = $proceed_to_inactive;

		return $data;
    }

    public static function validate_inactive($return,$slot_sponsor,$check_inactive,$proceed_to_inactive)
    {
		$slot_sponsor 				       = Tbl_slot::where("slot_no",$slot_sponsor)->first();
		if($slot_sponsor)
		{
			if($slot_sponsor->membership_inactive == 1)
			{
				$return["status_message"][$return["i"]] = "Sponsor is inactive...";
				$return["i"]++;
			}
			
			if($proceed_to_inactive == 1)
			{
				if($slot_sponsor->slot_id == $check_inactive->slot_id)
				{
					$return["status_message"][$return["i"]] = "Cannot sponsor yourself...";
					$return["i"]++;
				}
			}
		}

		return $return;
    }

    public static function validate_slot_no($return,$custom_slot_no)
    {
		if($custom_slot_no != null)
		{
			$messages['slot_no.unique'] = 'This Slot Code already exists.';
			$check['slot_no']			= $custom_slot_no;
			$checker["slot_no"]  		= "required|unique:tbl_slot";

			$validator = Validator::make($check, $checker, $messages);
			if ($validator->fails())
			{
				$len = count($validator->errors()->getMessages());
				foreach ($validator->errors()->getMessages() as $key => $value)
				{
					foreach($value as $val)
					{
						$return["status_message"][$return["i"]] = $val;
						$return["i"]++;
					}
				}
			}
		}


		return $return;
    }

    public static function new_slot_initial_data($new_id,$slot_sponsor,$membership_id,$slot_owner)
    {
    	/* UNIVERSAL POOL */
    	$new_slot  									     = Tbl_slot::where("slot_id",$new_id)->first();
    	$check_slot_limit                                = Tbl_slot_limit::where("user_id",$slot_owner)->first();
		$settings["slot_id"]                             = $new_id;
		$settings["universal_pool_bonus_points"]         = 0;
		$settings["universal_pool_bonus_grad_stat"]      = 0;
		$settings["excess_universal_pool_bonus_points"]  = 0;
		Tbl_mlm_universal_pool_bonus_points::insert($settings);

		/* ULTRAPRO INCENTIVES */
		$check_plan_incentive     = Tbl_mlm_plan::where('mlm_plan_code','=','INCENTIVE_BONUS')->first()->mlm_plan_enable;
		$check_incentive_settings = Tbl_mlm_incentive_bonus::first() ? Tbl_mlm_incentive_bonus::first()->incentives_status : 0 ;
		$code           	 	  = Tbl_item::where('item_type','=','membership_kit')->where('membership_id',$membership_id)->first(); 
		if($code->item_points_incetives != 0 && $check_incentive_settings == 1 && $check_plan_incentive == 1)
		{	
			$currency_id	= Tbl_currency::where('currency_abbreviation','=',$code->item_points_currency)->first()->currency_id;
			Log::insert_wallet($slot_sponsor->slot_id,$code->item_points_incetives,"UPCOIN",$currency_id);
			$details = "";
			Log::insert_earnings($slot_sponsor->slot_id,$code->item_points_incetives,"UPCOIN","PRODUCT REPURCHASE",$new_id,$details,0,$currency_id);
		}
		
		/* SLOT LIMIT */
		if(!$check_slot_limit)
		{
			$insert_limit["user_id"] 		  = $slot_owner;
			$insert_limit["active_slots"]	  = 0;
			$insert_limit["slot_limit"]       = 0;
			Tbl_slot_limit::insert($insert_limit);
			$check_slot_limit =Tbl_slot_limit::where("user_id",$slot_owner)->first();
		}	
		
		$update_limit["active_slots"] = $check_slot_limit->active_slots + 1;
		Tbl_slot_limit::where("user_id",$slot_owner)->update($update_limit);

		/* OTHER INITIAL DATA*/
		Slot_create::insert_top_recruiter($new_slot);

		/* STAIRSTEP UPDATE */
		/* PROCEED TO HERE IF LIVE UPDATE IS ON*/
		$check_plan_stairstep     = Tbl_mlm_plan::where('mlm_plan_code','=','STAIRSTEP')->first() ? Tbl_mlm_plan::where('mlm_plan_code','=','STAIRSTEP')->first()->mlm_plan_enable : 0;
		if($check_plan_stairstep == 1)
		{
			$_is_live  = Tbl_stairstep_settings::first();
			if($_is_live)
			{
				if($_is_live->live_update == 0)
				{
					$stairstep_direct = Tbl_slot::where("slot_id",$new_slot->slot_sponsor)->first();
					if($stairstep_direct)
					{
						$is_slot_creation = true;
						Stairstep::update_rank($new_slot->slot_sponsor,null,null,$is_slot_creation);
					}
				}
			}
		}
    }

    public static function insert_top_recruiter($new_slot)
    {
		$date_from = Carbon::now()->startofMonth()->format('Y-m-d');
		$date_to  = Carbon::now()->endofMonth()->format('Y-m-d');
		if($new_slot->slot_sponsor != 0)
		{
			$check = Tbl_top_recruiter::where('slot_id',$new_slot->slot_sponsor)->whereDate('date_from','=',$date_from)->whereDate('date_to','=',$date_to)->first();
			if(!$check)
			{
				$insertrecruiter['slot_id']		   = $new_slot->slot_sponsor;
				$insertrecruiter['date_from']      = $date_from;
				$insertrecruiter['date_to']        = $date_to;
				Tbl_top_recruiter::insert($insertrecruiter);
			}
			$recruit = Tbl_top_recruiter::where('slot_id',$new_slot->slot_sponsor)->whereDate('date_from','=',$date_from)->whereDate('date_to','=',$date_to)->first();
			$updaterecruiter1['total_recruits'] = $recruit->total_recruits + 1;
			Tbl_top_recruiter::where('slot_id',$recruit->slot_id)->whereDate('date_from','=',$date_from)->whereDate('date_to','=',$date_to)->update($updaterecruiter1);
			if($new_slot->slot_sponsor_member != 0)
			{	
				$recruit2 = Tbl_top_recruiter::where('slot_id',$new_slot->slot_sponsor_member)->whereDate('date_from','=',$date_from)->whereDate('date_to','=',$date_to)->first();
				$updaterecruiter2['total_leads'] = $recruit2->total_leads + 1;
				Tbl_top_recruiter::where('slot_id',$new_slot->slot_sponsor_member)->whereDate('date_from','=',$date_from)->whereDate('date_to','=',$date_to)->update($updaterecruiter2);
			}
		}
    }


}