<?php
namespace App\Globals;

use DB;
use Carbon\Carbon;

use App\Globals\MLM;
use App\Globals\Log;

use App\Models\Tbl_codes;
use App\Models\Tbl_membership;
use App\Models\Tbl_slot;
use App\Models\Tbl_currency;
use App\Models\Tbl_mlm_plan;
use App\Models\Tbl_mlm_incentive_bonus;


class Product
{
	public static function activate_code($data)
	{
		$slot_info	= Tbl_slot::where('slot_id', $data['slot_id'])->first();

		$check_restricted = Tbl_membership::where('membership_id', $slot_info->slot_membership)->first();
		if($check_restricted)
		{
			if($check_restricted->restriction == 'product' || $check_restricted->restriction == 'all')
			{
				$return['status']	= 'error';
				$return["status_code"]    = 400;
				$return['message'] = "you are restricted from using Product codes.";
			}
			else
			{
				$check = Self::check_product_code_unused($data["code"],$data["pin"],$data["slot_id"],$data["slot_owner"]);
				if($check == "used")
				{
				
					$return["message"]        = "Code already used.";
					$return["status"]         = "error";
					$return["status_code"]    = 400;
				}
				else if($check == "not_exist")
				{
				
					$return["message"]        = "Code does not exists.";
					$return["status"]         = "error";
					$return["status_code"]    = 400;
				}
				else if($check=="success")
				{
					$return["message"]        = "Product Code Activated";
					$return["status"]         = "success";
					$return["status_code"]    = 200;
				}
				else
				{
					$return["message"]        = "Failed to Activate";
					$return["status"]         = "error";
					$return["status_code"]    = 400;
				}
			}
		}
		else
		{
			$return["message"]        = "Please activate your account first to use product code";
			$return["status"]         = "error";
			$return["status_code"]    = 400;
		}

		return $return;
	}

	

	public static function check_product_code_unused($code,$pin,$slot_id,$slot_owner)
	{
		$code = Tbl_codes::where("code_activation",$code)->where("code_pin",$pin)->inventory()->inventoryitem()->where('tbl_item.archived', 0)->where("item_type","product")->first();
		if($code)
		{
			if($code->code_used == 0)
			{
	            $return["code_id"]   		= $code->code_id;
				$update["code_used"] 		= 1;
				$update["code_sold"] 		= 1;
				$update["org_code_sold_to"] = $slot_owner;
				$update["code_sold_to"] 	= $slot_owner;
				$update["code_used_by"] 	= $slot_owner;
				$update['code_slot_used'] 	= $slot_id;
				$update["code_date_used"] 	= Carbon::now();
				// $update["code_date_sold"] 	= Carbon::now();
				Tbl_codes::where("code_id",$code->code_id)->update($update);
				/*INSERT CASHBACK*/
				MLM::purchase($slot_id,$code->item_id);
				if($code->bind_membership_id != 0)
				{
					// dd(123124);
					MLM::create_entry($slot_id,$code->bind_membership_id);
					MLM::placement_entry($slot_id,null,$code->bind_membership_id);
				}
				$return  = "success"; 
			}
			else
			{
	            $return  = "used"; 
			}
		}
		else
		{
            $return  = "not_exist"; 
		}
		return $return;
	}
}
