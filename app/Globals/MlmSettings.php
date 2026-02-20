<?php
namespace App\Globals;

use DB;
use App\Models\Tbl_mlm_unilevel_settings;
use App\Models\Tbl_membership_unilevel_level;
use App\Models\Tbl_membership;

class MlmSettings
{
	public static function unilevel_save_settings($data)
	{
		$level = count($data);
		$first = 0;
		$ctr   = 0;
		foreach($data as $value)
		{
			$ctr++;
			if($first == 0)
			{
				Tbl_membership_unilevel_level::where("membership_level",">",$level)->delete();
				$first = 1;
			}

			$setting["membership_level"]		  = $value["membership_level"];
			$setting["membership_id"]			  = $value["membership_id"];
			$setting["membership_entry_id"]		  = $value["membership_entry_id"];
			$setting["membership_percentage"]     = $value["membership_percentage"];
			$has = Tbl_membership_unilevel_level::where("membership_level",$value["membership_level"])->where("membership_id",$value["membership_id"])->where("membership_entry_id",$value["membership_id"])->first();
			if($has)
			{
				Tbl_membership_unilevel_level::where("membership_level",$value["membership_level"])->where("membership_id",$value["membership_id"])->where("membership_entry_id",$value["membership_id"])->update($setting);
			}
			else
			{
				Tbl_membership_unilevel_level::insert($setting);
			}
		}

		if(isset($setting["membership_id"]))
		{
			$update_level["membership_unilevel_level"] = $ctr;
			Tbl_membership::where("membership_id",$setting["membership_id"])->update($update_level);
		}

		$return["status"]         = "success"; 
		$return["status_code"]    = 1; 
		$return["status_message"] = "Settings Saved";
		return $return;
	}
}