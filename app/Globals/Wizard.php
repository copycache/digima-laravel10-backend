<?php
namespace App\Globals;

use App\Models\Tbl_admin;
use App\Models\Tbl_country;
use App\Models\Tbl_company;
use App\Models\Tbl_company_settings;
use App\Models\Tbl_customer;
use App\Models\Tbl_mlm_settings;
use App\Models\Tbl_binary_settings;
use App\Models\Tbl_mlm_plan;
use App\Models\Tbl_membership_income;
use App\Models\Tbl_membership_indirect_level;
use App\Models\Tbl_binary_points_settings;
use App\Models\Tbl_binary_pairing;
use App\Models\Tbl_mlm_unilevel_settings;
use App\Models\Tbl_membership;
use App\Models\Tbl_stairstep_settings;
use App\Models\Tbl_stairstep_rank;
use App\Models\Tbl_payout_settings;
use App\Models\Tbl_payout_method;
use App\Models\Tbl_bank;
use App\Models\Tbl_remittance;
use DB;
use Carbon\Carbon;
use Validator;
class Wizard
{
	public static function step_one($data,$data_company)
	{
		$insert["admin_username"]		                = $data["admin_username"];
		$insert["admin_password"]		                = $data["admin_password"];
		$insert["admin_first_name"]		                = $data["admin_first_name"];
		$insert["admin_last_name"]		                = $data["admin_last_name"];
		$insert["admin_email"]			                = $data["admin_email"];
		$insert["admin_contact"]		                = $data["admin_contact"];
		$insert["admin_date_created"]	                = Carbon::now();

		$insert_company["company_name"]					= $data_company["company_name"];
		$insert_company["company_contact"]				= $data_company["company_contact"];
		$insert_company["company_address"]				= $data_company["company_address"];
		$insert_company["company_office_hours"]			= $data_company["company_office_hours"];


		$validation["admin_username"]	                = $insert["admin_username"];
		$validation["admin_password"]	                = $insert["admin_password"];
		$validation["admin_first_name"]	                = $insert["admin_first_name"];
		$validation["admin_last_name"]	                = $insert["admin_last_name"];
		$validation["admin_email"]		                = $insert["admin_email"];
		$validation["admin_contact"]	                = $insert["admin_contact"];

		$validation["company_name"]	                	= $insert_company["company_name"];
		$validation["company_contact"]	                = $insert_company["company_contact"];
		$validation["company_address"]	                = $insert_company["company_address"];
		$validation["company_office_hours"]	            = $insert_company["company_office_hours"];

        $rules['admin_username']                        = 'required|unique:tbl_admin,admin_username';
    	$rules['admin_password']                        = 'required';
    	$rules['admin_first_name']                      = 'required';
    	$rules['admin_last_name']                       = 'required';
    	$rules['admin_email']                           = 'required';
    	$rules['admin_contact']                         = 'required';

    	$rules['company_name']                          = 'required';
    	$rules['company_contact']                       = 'required';
    	$rules['company_address']                       = 'required';
    	$rules['company_office_hours']                  = 'required';

    	$validator = Validator::make($validation,$rules);
    	if ($validator->passes())
    	{
			if($insert["admin_password"] != $data["admin_rpassword"])
			{
				$return["status"]         = "failed"; 
				$return["status_code"]    = 101; 
				$return["status_message"] = "Password mismatch";

				return $return;
			}
			else
			{
				/* INSERT TABLE */
			    Tbl_company::insert($insert_company);
				Tbl_admin::insert($insert);

				$return["status"]         = "success"; 
				$return["status_code"]    = 1; 
				$return["status_message"] = "Successfully created";

				return $return;
			}
    	}
    	else
    	{
			$return["status"]         = "failed"; 
			$return["status_code"]    = 101; 
			$return["status_message"] = $validator->errors()->first();

			return $return;
    	}
	}

	public static function step_two($data,$data_country)
	{
		
		$settings["country_id"]				 = $data["country_id"];
		$settings["base_currency"] 			 = $data["base_currency"];
		$settings["allow_multiple_currency"] = $data["allow_multiple_currency"];


		$has = Tbl_company_settings::first();
		if($has)
		{
			Tbl_company_settings::where("company_settings_id",$has->company_settings_id)->update($settings);
		}
		else
		{
			Tbl_company_settings::insert($settings);
		}
		

		if($settings["allow_multiple_currency"] == 1)
		{
			foreach($data_country as $key => $set)
			{
				$update["currency_conversion"] = $set;
				Tbl_country::where("country_id",$key)->update($update); 
			}
		}

		$return["status"]         = "success"; 
		$return["status_code"]    = 1; 
		$return["status_message"] = "Currency saved";

		return $return;
	}

	public static function step_three($data)
	{
		/*SLOT CREATION FORMAT*/
		// 1 = Name Abbreviation + Auto
		// 2 = Number
		// 3 = Auto Number
		// 4 = Random Letters
		// 5 = Random Numbers

		if($data["binary_enabled"] == 1)
		{
			$setting["auto_placement"] = $data["auto_placement"];
			if($setting["auto_placement"] == 1)
			{
				$setting["auto_placement_type"]		     = $data["auto_placement_type"];
				$setting["member_disable_auto_position"] = $data["member_disable_auto_position"];
				if($setting["member_disable_auto_position"] == 1)
				{
					$setting["member_default_position"]	    = $data["member_default_position"];			
				}
			}

			$has = Tbl_binary_settings::first();
			if($has)
			{
				Tbl_binary_settings::where("binary_settings_id",$has->binary_settings_id)->update($setting);
			}
			else
			{
				$setting["auto_placement"]               = isset($setting["auto_placement"]) ? $setting["auto_placement"] : 0;
				$setting["auto_placement_type"]          = isset($setting["auto_placement_type"]) ? $setting["auto_placement_type"] : 0;
				$setting["member_disable_auto_position"] = isset($setting["member_disable_auto_position"]) ? $setting["member_disable_auto_position"] : 0;
				$setting["member_default_position"]      = isset($setting["member_default_position"]) ? $setting["member_default_position"] : 0;
				$setting["strong_leg_retention"]         = 0;
				$setting["gc_pairing_count"]             = 0;
				$setting["cycle_per_day"]                = 1;
				Tbl_binary_settings::insert($setting);
			}

		}		


		$mlm_setting["mlm_slot_no_format_type"] = $data["mlm_slot_no_format_type"];
		if($mlm_setting["mlm_slot_no_format_type"] < 1 && $mlm_setting["mlm_slot_no_format_type"] > 5)
		{
			$mlm_setting["mlm_slot_no_format_type"] = 1;
		}

		$has = Tbl_mlm_settings::first();
		if($has)
		{
			Tbl_mlm_settings::where("mlm_settings_id",$has->mlm_settings_id)->update($mlm_setting);
		}
		else
		{
			$mlm_setting["mlm_slot_no_format"]			 = "";
			$mlm_setting["free_registration"]			 = 0;
			$mlm_setting["multiple_type_membership"]	 = 0;
			$mlm_setting["gc_inclusive_membership"]		 = 0;
			$mlm_setting["product_inclusive_membership"] = 0;
			Tbl_mlm_settings::insert($mlm_setting);
		}	


		$return["status"]         = "success"; 
		$return["status_code"]    = 1; 
		$return["status_message"] = "Binary Settings Saved";

		return $return;
	}

	public static function step_four($data)
	{
		$mlm_setting["free_registration"]		     = $data["free_registration"];
		$mlm_setting["multiple_type_membership"]     = $data["multiple_type_membership"];
		$mlm_setting["gc_inclusive_membership"]      = $data["gc_inclusive_membership"];
		$mlm_setting["product_inclusive_membership"] = $data["product_inclusive_membership"];

		$has = Tbl_mlm_settings::first();
		if($has)
		{
			Tbl_mlm_settings::where("mlm_settings_id",$has->mlm_settings_id)->update($mlm_setting);
		}
		else
		{
			$mlm_setting["mlm_slot_no_format"]			 = "";
			$mlm_setting["mlm_slot_no_format_type"]      = 1;
			Tbl_mlm_settings::insert($mlm_setting);
		}	

		$return["status"]         = "success"; 
		$return["status_code"]    = 1; 
		$return["status_message"] = "Settings Saved";

		return $return;
	}

	public static function step_five($data)
	{
		foreach($data as $key => $value)
		{
			$update["mlm_plan_label"] = $value;
			Tbl_mlm_plan::where("mlm_plan_id",$key)->update($update);
		}
		
		$return["status"]         = "success"; 
		$return["status_code"]    = 1; 
		$return["status_message"] = "Settings Saved";
		return $return;
	}

	public static function step_five_one($data)
	{
		foreach($data as $value)
		{
			$setting["membership_id"]			  = $value["membership_id"];
			$setting["membership_entry_id"]		  = $value["membership_entry_id"];
			$setting["membership_direct_income"]  = $value["membership_direct_income"];

			$check = Tbl_membership_income::where("membership_id",$setting["membership_id"])->where("membership_entry_id",$setting["membership_entry_id"])->first();
			if(!$check)
			{
				Tbl_membership_income::insert($setting);
			}
			else
			{				
				Tbl_membership_income::where("membership_id",$setting["membership_id"])->where("membership_entry_id",$setting["membership_entry_id"])->update($setting);
			}
		}

		$return["status"]         = "success"; 
		$return["status_code"]    = 1; 
		$return["status_message"] = "Settings Saved";
		return $return;
	}

	public static function step_five_two($data)
	{
		$level = 0;

		foreach($data as $value)
		{
			$level = $level + 1;
			$setting["membership_level"]	       = $value["membership_level"];
			$setting["membership_id"]			   = $value["membership_id"];
			$setting["membership_entry_id"]		   = $value["membership_entry_id"];
			$setting["membership_indirect_income"] = $value["membership_indirect_income"];

			Tbl_membership_indirect_level::truncate();
			Tbl_membership_indirect_level::insert($setting);
		}

		$return["status"]         = "success"; 
		$return["status_code"]    = 1; 
		$return["status_message"] = "Settings Saved";
		return $return;
	}

	public static function step_five_three($data_plan,$data,$data_combination)
	{

		$plan_setting["strong_leg_retention"] = $data_plan["strong_leg_retention"];
		$plan_setting["gc_pairing_count"] 	  = $data_plan["gc_pairing_count"];
		$plan_setting["cycle_per_day"] 		  = $data_plan["cycle_per_day"];

		$has = Tbl_binary_settings::first();
		Tbl_binary_settings::where("binary_settings_id",$has->binary_settings_id)->update($plan_setting);


		foreach($data as $value)
		{
			$setting["membership_id"]			  = $value["membership_id"];
			$setting["membership_entry_id"]		  = $value["membership_entry_id"];
			$setting["membership_binary_points"]  = $value["membership_binary_points"];

			$check = Tbl_binary_points_settings::where("membership_id",$setting["membership_id"])->where("membership_entry_id",$setting["membership_entry_id"])->first();
			if(!$check)
			{
				Tbl_binary_points_settings::insert($setting);
			}
			else
			{				
				Tbl_binary_points_settings::where("membership_id",$setting["membership_id"])->where("membership_entry_id",$setting["membership_entry_id"])->update($setting);
			}
		}

		if($data_combination)
		{
			Tbl_binary_pairing::truncate();
			foreach($data_combination as $value)
			{
				$settings_combination["binary_pairing_left"]  = $value["binary_pairing_left"];
				$settings_combination["binary_pairing_right"] = $value["binary_pairing_right"];
				$settings_combination["binary_pairing_bonus"] = $value["binary_pairing_bonus"];
				Tbl_binary_pairing::insert($settings_combination);
			}	
		}



		$return["status"]         = "success"; 
		$return["status_code"]    = 1; 
		$return["status_message"] = "Settings Saved";
		return $return;
	}

	public static function step_five_four($unilevel_setting,$membership)
	{
		$settings["personal_as_group"]		  = $unilevel_setting["personal_as_group"];
		$settings["gpv_to_wallet_conversion"] = $unilevel_setting["gpv_to_wallet_conversion"];
		$check = Tbl_mlm_unilevel_settings::first();
		if(!$check)
		{
			Tbl_mlm_unilevel_settings::insert($settings);
		}
		else
		{	
			Tbl_mlm_unilevel_settings::where("mlm_unilevel_settings_id",$check->mlm_unilevel_settings_id)->update($settings);
		}
		foreach($membership as $value)
		{
			$settings_combination["membership_required_pv"] = $value["membership_required_pv"];
			Tbl_membership::where("membership_id",$value["membership_id"])->update($settings_combination);
		}

		$return["status"]         = "success"; 
		$return["status_code"]    = 1; 
		$return["status_message"] = "Settings Saved";
		return $return;
	}

	public static function step_five_five($data)
	{
		$settings["personal_as_group"]	= $data["personal_as_group"];
		$settings["live_update"]		= $data["live_update"];

		$check = Tbl_stairstep_settings::first();
		if(!$check)
		{
			if($settings["live_update"] == 1)
			{
				$settings["allow_downgrade"]	= $data["allow_downgrade"];
				$settings["rank_first"]			= $data["rank_first"];		
			}
			Tbl_stairstep_settings::insert($settings);
		}
		else
		{	
			if($settings["live_update"] == 1)
			{
				$settings["allow_downgrade"]	= 0;
				$settings["rank_first"]			= 0;
			}
			
			Tbl_stairstep_settings::where("stairstep_settings_id",$check->stairstep_settings_id)->update($settings);
		}

		$return["status"]         = "success"; 
		$return["status_code"]    = 1; 
		$return["status_message"] = "Settings Saved";
		return $return;
	}

	public static function step_six($data)
	{
		$settings["encash_all_wallet_cutoff"]	= $data["encash_all_wallet_cutoff"];
		$settings["minimum_encashment"]		    = $data["minimum_encashment"];

		$check = Tbl_payout_settings::first();
		if(!$check)
		{
			Tbl_payout_settings::insert($settings);
		}
		else
		{	
			Tbl_payout_settings::where("payout_settings_id",$check->payout_settings_id)->update($settings);
		}

		$return["status"]         = "success"; 
		$return["status_code"]    = 1; 
		$return["status_message"] = "Settings Saved";
		return $return;
	}

	public static function step_six_one($data,$bank)
	{
		$settings["bank_enable_payout"]		= $data["bank_enable_payout"];
		$settings["bank_additional_charge"]	= $data["bank_additional_charge"];

		$check = Tbl_payout_settings::first();
		if(!$check)
		{
			Tbl_payout_settings::insert($settings);
		}
		else
		{	
			Tbl_payout_settings::where("payout_settings_id",$check->payout_settings_id)->update($settings);
		}

		$array = [];
		foreach($bank as $b)
		{
			array_push($array, $b->bank_id);
			$update_bank["bank_payout_enable"] = 1;
			Tbl_bank::where("bank_id",$b->bank_id)->update($update_bank);
		}
		
		$update_not["bank_payout_enable"] = 0;
		Tbl_bank::whereNotIn("bank_id",$array)->update($update_not);

		$return["status"]         = "success"; 
		$return["status_code"]    = 1; 
		$return["status_message"] = "Settings Saved";
		return $return;
	}

	public static function step_six_two($data,$cheque)
	{
		$settings["remittance_enable_payout"]		= $data["remittance_enable_payout"];
		$settings["remittance_additional_charge"]	= $data["remittance_additional_charge"];

		$check = Tbl_payout_settings::first();
		if(!$check)
		{
			Tbl_payout_settings::insert($settings);
		}
		else
		{	
			Tbl_payout_settings::where("payout_settings_id",$check->payout_settings_id)->update($settings);
		}

		$array = [];
		foreach($cheque as $c)
		{
			array_push($array, $c->bank_id);
			$update_remittance["remittance_payout_enable"] = 1;
			Tbl_remittance::where("remittance_id",$c->remittance_id)->update($update_remittance);
		}

		$update_not["remittance_payout_enable"] = 0;
		Tbl_remittance::whereNotIn("bank_id",$array)->update($update_not);

		$return["status"]         = "success"; 
		$return["status_code"]    = 1; 
		$return["status_message"] = "Settings Saved";
		return $return;
	}

	public static function step_six_three($data)
	{
		$settings["cheque_enable_payout"]		= $data["cheque_enable_payout"];
		$settings["cheque_additional_charge"]	= $data["cheque_additional_charge"];

		$check = Tbl_payout_settings::first();
		if(!$check)
		{
			Tbl_payout_settings::insert($settings);
		}
		else
		{	
			Tbl_payout_settings::where("payout_settings_id",$check->payout_settings_id)->update($settings);
		}


		$return["status"]         = "success"; 
		$return["status_code"]    = 1; 
		$return["status_message"] = "Settings Saved";
		return $return;
	}

	public static function step_six_four($data)
	{
		$settings["coinsph_enable_payout"]		= $data["coinsph_enable_payout"];
		$settings["coinsph_additional_charge"]	= $data["coinsph_additional_charge"];

		$check = Tbl_payout_settings::first();
		if(!$check)
		{
			Tbl_payout_settings::insert($settings);
		}
		else
		{	
			Tbl_payout_settings::where("payout_settings_id",$check->payout_settings_id)->update($settings);
		}


		$return["status"]         = "success"; 
		$return["status_code"]    = 1; 
		$return["status_message"] = "Settings Saved";
		return $return;
	}
}