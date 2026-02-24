<?php
namespace App\Globals;

use DB;
use App\Models\Tbl_membership;
use App\Models\Tbl_membership_income;
use App\Models\Tbl_mlm_plan;
use App\Models\Tbl_membership_indirect_level;
use App\Models\Tbl_membership_unilevel_level;
use App\Models\Tbl_mlm_unilevel_settings;
use App\Models\Tbl_stairstep_settings;
use App\Models\Tbl_stairstep_rank;
use App\Models\Tbl_binary_pairing;
use App\Models\Tbl_binary_settings;
use App\Models\Tbl_binary_points_settings;
use App\Models\Tbl_label;

use App\Globals\Audit_trail;
use App\Globals\Get_plan;
use App\Globals\Update_plan;
use Carbon\Carbon;
class Plan
{
	public static function get($plan)
	{
		return Get_plan::$plan();
	}

	public static function update($plan,$label,$data,$trigger = null)
	{	
		return Update_plan::$plan($plan,$label,$data,$trigger);
	}

	public static function update_status($plan,$send)
	{
		if($send == 1)
		{
			$update_plan["mlm_plan_enable"] = 1;
		}
		else if($send == 0)
		{
			$update_plan["mlm_plan_enable"] = 0;
		}

		Tbl_mlm_plan::where("mlm_plan_code",$plan)->update($update_plan);
		$return["update_status"]  = $update_plan["mlm_plan_enable"]; 
		$return["status"]         = "success"; 
		$return["status_code"]    = 201; 

		if($send == 1)
		{
			$return["status_message"] = $plan." enabled";
		}
		else
		{
			$return["status_message"] = $plan." disabled";
		}

		return $return;
	}

	public static function get_label($code)
	{
		$get = Tbl_label::where("plan_code",$code)->first();
		if(!$get)
		{
			$insert["plan_code"] = $code;
			$insert["plan_name"] = str_replace('_', ' ', $code);
			Tbl_label::insert($insert);
		}

		$get = Tbl_label::where("plan_code",$code)->first();

		return $get->plan_name;
	}

	public static function get_trigger($code)
	{
		$get = Tbl_mlm_plan::where("mlm_plan_code",$code)->first();
		if(!$get)
		{
			$insert["mlm_plan_code"] = $code;
			$insert["mlm_plan_name"] = str_replace('_', ' ', $code);
			Tbl_mlm_plan::insert($insert);
		}

		$get = Tbl_mlm_plan::where("mlm_plan_code",$code)->first();

		return $get->mlm_plan_trigger;
	}

	public static function update_label($code,$update)
	{
		$get = Tbl_label::where("plan_code",$code)->first();
		if(!$get)
		{
			$insert["plan_code"] = $code;
			$insert["plan_name"] = $update;
			Tbl_label::insert($insert);
		}
		else
		{
			$update_plan["plan_name"] = $update;
			Tbl_label::where("plan_code",$code)->update($update_plan);			
		}
	}

	public static function update_trigger($code, $update)
	{
		$get = Tbl_mlm_plan::where("mlm_plan_code",$code)->first();
		if(!$get)
		{
			$insert["mlm_plan_code"] = $code;
			$insert["mlm_plan_trigger"] = $update;
			Tbl_mlm_plan::insert($insert);
		}
		else
		{
			$update_plan["mlm_plan_trigger"] = $update;
			Tbl_mlm_plan::where("mlm_plan_code",$code)->update($update_plan);			
		}
	}

	public static function update_board($levels, $depth, $bonus)
	{
		DB::table('tbl_mlm_board_settings')->truncate();
		foreach($bonus as $key => $value)
		{
			$insert['board_depth'] = $depth;
			$insert['board_level'] = $key + 1;
			$insert['graduation_bonus'] = $value;
			DB::table('tbl_mlm_board_settings')->insert($insert);
		}	
		$return["status_message"]  = "Successfully updated"; 
		$return["status"]         = "success"; 
		$return["status_code"]    = 201; 
		return $return;
	}
}
