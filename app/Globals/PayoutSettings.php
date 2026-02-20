<?php
namespace App\Globals;

use DB;
use App\Models\Tbl_membership;
use App\Models\Tbl_payout_method;
use App\Models\Tbl_payout_charge;
use App\Models\Tbl_bank;
use App\Models\Tbl_remittance;

class PayoutSettings
{
	public static function add_payout_charges($data)
	{
        $insert["payout_charge_name"]	= $data["payout_charge_name"];
        $insert["payout_charge_status"]	= $data["payout_charge_status"];
        $insert["payout_charge_type"]	= $data["payout_charge_type"];
        $insert["payout_charge_value"]	= $data["payout_charge_value"];

		Tbl_payout_charge::insert($insert);

		$return["status"]         = "success";
		$return["status_code"]    = 1;
		$return["status_message"] = "Settings Saved";
		return $return;
	}

	public static function edit_payout_charges($data,$payout_charge_id)
	{
        $update["payout_charge_name"]	= $data["payout_charge_name"];
        $update["payout_charge_status"]	= $data["payout_charge_status"];
        $update["payout_charge_type"]	= $data["payout_charge_type"];
        $update["payout_charge_value"]	= $data["payout_charge_value"];

		Tbl_payout_charge::where("payout_charge_id",$payout_charge_id)->update($update);

		$return["status"]         = "success";
		$return["status_code"]    = 1;
		$return["status_message"] = "Settings Saved";
		return $return;
	}

	public static function edit_payout_method($data,$payout_method_id)
	{
        $update["payout_method_status"]	= $data["payout_method_status"];
        $update["payout_method_charge"]	= $data["payout_method_charge"];

		Tbl_payout_method::where("payout_method_id",$payout_method_id)->update($update);

		$return["status"]         = "success";
		$return["status_code"]    = 1;
		$return["status_message"] = "Settings Saved";
		return $return;
	}

	public static function add_bank($data)
	{
        $insert["bank_name"]			= $data["bank_name"];
        $insert["bank_payout_enable"]	= $data["bank_payout_enable"];
        $insert["bank_date_created"]	= Carbon::now();
        $insert["archive"]				= 0;

		Tbl_bank::insert($insert);

		$return["status"]         = "success";
		$return["status_code"]    = 1;
		$return["status_message"] = "Settings Saved";
		return $return;
	}

	public static function add_remittance($data)
	{
        $insert["remittance_name"]			= $data["remittance_name"];
        $insert["remittance_payout_enable"]	= $data["remittance_payout_enable"];
        $insert["remittance_date_created"]	= Carbon::now();
        $insert["archive"]				    = 0;

		Tbl_remittance::insert($insert);

		$return["status"]         = "success";
		$return["status_code"]    = 1;
		$return["status_message"] = "Settings Saved";
		return $return;
	}

}
