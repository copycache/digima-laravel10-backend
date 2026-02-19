<?php
namespace App\Globals;

use App\Globals\Audit_trail;
use App\Models\Tbl_admin;
use App\Models\Tbl_country;
use App\Models\Tbl_company;
use App\Models\Tbl_company_settings;
use App\Models\Tbl_customer;
use App\Models\Tbl_mlm_settings;
use App\Models\Tbl_binary_settings;
use App\Models\Tbl_mlm_plan;
use App\Models\Tbl_membership;
use App\Models\Tbl_membership_indirect_level;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
class Membership
{
	public static function add($data)
	{
		$insert["membership_name"]			 = $data["membership_name"];
		$insert["membership_price"]			 = $data["membership_price"];
		$insert["membership_gc"]			 = $data["membership_gc"];
		$insert["membership_indirect_level"] = 0;
		$insert["membership_unilevel_level"] = 0;
		$insert["membership_date_created"]	 = Carbon::now();
		$insert["membership_required_pv"]	 = 0;

		Tbl_membership::insert($insert);


		$return["status"]         = "success"; 
		$return["status_code"]    = 1; 
		$return["status_message"] = "Membership Created";

		return $return;
	}

	public static function get()
	{
		$data = Tbl_membership::where("archive",0)->get();

		foreach($data as $key => $d)
		{
			$data[$key]->membership_indirect_level = count(Tbl_membership_indirect_level::select("membership_level")->where("membership_id",$d->membership_id)->groupBy("membership_level")->get());
		}

		return $data;
	}

	public static function get_manage_settings()
	{
		$data = Tbl_membership::where("archive",0)->get();
		return $data;
	}

	public static function submit($data)
	{

		foreach ($data as $key => $value) 
		{
			$rules["hierarchy"] = "required|numeric|min:1|max:100";
			$rules["membership_name"] = "required|unique:tbl_membership,membership_name,".$value["membership_id"].",membership_id";

			$validator = Validator::make($value, $rules);

	        if ($validator->fails()) 
	        {
	            $return["status"]         = "error"; 
				$return["status_code"]    = 400; 
				$return["status_message"] = $validator->messages()->all();

				return $return;
	        }
	        else
	        {
				$old_value  = Tbl_membership::where("membership_id", $value["membership_id"])->first();
	        	$param["membership_name"] 	= $value["membership_name"];
				$param["hierarchy"] 		= $value["hierarchy"];
				$param["minimum_move_wallet"] 	= $value["minimum_move_wallet"];
				$param["move_wallet_fee"] 	= $value["move_wallet_fee"];
				$param["enable_commission"] = $value["enable_commission"];
				$param["free_slot_membership"] = $value["free_slot_membership"];
				$param["color"]             = $value["color"];
				$param["restriction"]       = $value["restriction"];
				// $param["flushout_enable"]   = $value["flushout_enable"];
				$param["membership_transfer"]   = $value["membership_transfer"];
				$param["product_transfer"]   = $value["product_transfer"];
				$param["auto_activate_product_code"]   = $value["auto_activate_product_code"];
				if ($value["membership_id"]) 
				{
					$param["archive"] = $value["archive"];


					Tbl_membership::where("membership_id", $value["membership_id"])->update($param);
				}
				else
				{
					$param["membership_date_created"] = Carbon::now();
					$param["membership_price"] = 0;

					Tbl_membership::insert($param);
				}
				$new_value  = Tbl_membership::where("membership_id", $value["membership_id"])->first();
				$action     = "Update Membership";
				$user       = Request::user()->id;
				Audit_trail::audit(serialize($old_value),serialize($new_value),$user,$action);
			}
		}
		// $new_value  = Tbl_membership::get();
		// $action     = "Update Membership";
		// $user       = Request::user()->id;
		// Audit_trail::audit(serialize($old_value),serialize($new_value),$user,$action);
		$return["status"]         = "success"; 
		$return["status_code"]    = 200; 
		$return["status_message"] = "Membership Updated";

		return $return;
	}
}
