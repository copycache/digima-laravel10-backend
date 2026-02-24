<?php
namespace App\Http\Controllers\Admin;

use App\Models\Tbl_slot;
use App\Models\User;
use App\Models\Tbl_wallet_log;
use App\Globals\Audit_trail;
use App\Globals\Tbl_vortex_slot;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\DB;

class AdminResetController extends AdminController
{
	public function reset_data()
	{
		$member_list		= Request::input("member_list");
		$slot_list			= Request::input("slot_list");
		$plan_settings		= Request::input("plan_settings");
		$generated_codes	= Request::input("generated_codes");
		$product_list		= Request::input("product_list");

		$password			= Request::input("security_key");

		$data['member_list']		= Request::input("member_list");
		$data['slot_list']			= Request::input("slot_list");
		$data['plan_settings']		= Request::input("plan_settings");
		$data['generated_codes']	= Request::input("generated_codes");
		$data['product_list']		= Request::input("product_list");
		$user						= Request::input("user");
		$data['security_key']		= Request::input("security_key");

		if($password == "waterqwerty")
		{
			$action = 'Reset Data';
			Audit_trail::audit(null,serialize($data),$user['id'],$action);
			if($slot_list == true)
			{
				DB::table("tbl_wallet_log")->delete();
				DB::table("tbl_wallet")->update(['wallet_amount' => 0]);
				DB::table("tbl_earning_log")->delete();
				DB::table("tbl_points_log")->delete();
				DB::table("tbl_unilevel_points")->delete();
				DB::table("tbl_stairstep_points")->delete();
				DB::table("tbl_binary_points")->delete();
				DB::table("tbl_monoline_points")->delete();
				DB::table("tbl_leveling_bonus_points")->delete();
				DB::table("tbl_slot_limit")->delete();
				DB::table("tbl_mlm_universal_pool_bonus_points")->delete();
				DB::table("tbl_top_recruiter")->delete();
				DB::table("tbl_vortex_slot")->delete();
				DB::table("tbl_vortex_token_log")->delete();
				Tbl_slot::where("slot_id","!=",1)->delete();

				DB::table("tbl_slot")->update(["slot_left_points"=>0]);
				DB::table("tbl_slot")->update(["slot_right_points"=>0]);
				DB::table("tbl_slot")->update(["slot_wallet"=>0]);
				DB::table("tbl_slot")->update(["slot_total_earnings"=>0]);
				DB::table("tbl_slot")->update(["slot_total_payout"=>0]);
				DB::table("tbl_slot")->update(["slot_stairstep_rank"=>0]);
				DB::table("tbl_slot")->update(["slot_pairs_per_day_date"=>""]);
				DB::table("tbl_slot")->update(["slot_pairs_per_day"=>0]);
				DB::table("tbl_slot")->update(["slot_personal_spv"=>0]);
				DB::table("tbl_slot")->update(["slot_group_spv"=>0]);
				DB::table("tbl_slot")->update(["meridiem" => ""]);

									
				DB::statement("ALTER TABLE tbl_unilevel_points AUTO_INCREMENT =  1");
				DB::statement("ALTER TABLE tbl_slot AUTO_INCREMENT =  1");
				DB::statement("ALTER TABLE tbl_wallet_log AUTO_INCREMENT =  1");
				DB::statement("ALTER TABLE tbl_points_log AUTO_INCREMENT =  1");
				DB::statement("ALTER TABLE tbl_stairstep_points AUTO_INCREMENT =  1");
				DB::statement("ALTER TABLE tbl_binary_points AUTO_INCREMENT =  1");
				DB::statement("ALTER TABLE tbl_tree_placement AUTO_INCREMENT =  1");
				DB::statement("ALTER TABLE tbl_tree_sponsor AUTO_INCREMENT =  1");
				DB::statement("ALTER TABLE tbl_vortex_slot AUTO_INCREMENT =  1");
				DB::statement("ALTER TABLE tbl_vortex_token_log AUTO_INCREMENT =  1");
			}

			if($member_list == true)
			{
				if($slot_list == true)
				{
					User::where([
						['type', '=', 'member'],
						['type', '=', 'cashier'],
					])->delete();
					DB::statement("ALTER TABLE users AUTO_INCREMENT =  1");
				}
			}

			if($plan_settings == true)
			{
				/* DIRECT*/
				DB::table("tbl_membership_income")->update(["membership_direct_income"=>0]);
				/* INDIRECT */
				DB::table("tbl_membership")->update(["membership_indirect_level"=>0]);
				DB::table("tbl_membership_indirect_level")->delete();

				/* UNILEVEL */
				DB::table("tbl_membership")->update(["membership_unilevel_level"=>0]);
				DB::table("tbl_membership_unilevel_level")->update(["membership_percentage"=>0]);
				DB::table("tbl_mlm_unilevel_settings")->update(["gpv_to_wallet_conversion"=>1]);
				DB::table("tbl_mlm_unilevel_settings")->update(["personal_as_group"=>0]);
				DB::table("tbl_mlm_unilevel_settings")->update(["personal_pv_label"=>"Personal PV"]);
				DB::table("tbl_mlm_unilevel_settings")->update(["group_pv_label"=>"Group PV"]);

				/*STAIRSTEP*/
				DB::table("tbl_stairstep_settings")->update(["personal_stairstep_pv_label"=>"Accumulated Personal PV"]);
				DB::table("tbl_stairstep_settings")->update(["group_stairstep_pv_label"=>"Accumulated Group PV"]);
				DB::table("tbl_stairstep_settings")->update(["earning_label_points"=>"Override Points"]);
				DB::table("tbl_stairstep_settings")->update(["sgpv_to_wallet_conversion"=>0]);
				DB::table("tbl_stairstep_settings")->update(["personal_as_group"=>0]);
				DB::table("tbl_stairstep_settings")->update(["live_update"=>0]);
				DB::table("tbl_stairstep_rank")->delete();
				DB::statement("ALTER TABLE tbl_stairstep_rank AUTO_INCREMENT =  1");

				/*BINARY*/
				DB::table("tbl_binary_settings")->update(["gc_pairing_count"=>0]);
				DB::table("tbl_binary_settings")->update(["cycle_per_day"=>1]);
				DB::table("tbl_binary_settings")->update(["strong_leg_retention"=>0]);
				DB::table("tbl_binary_pairing")->delete();
				DB::table("tbl_binary_points_settings")->delete();
				DB::statement("ALTER TABLE tbl_binary_pairing AUTO_INCREMENT =  1");


			}

			if($generated_codes == true)
			{
				if($slot_list == true)
				{
					DB::table("tbl_codes")->delete();
					DB::table("tbl_inventory")->update(["inventory_quantity"=>0]);
					DB::statement("ALTER TABLE tbl_stairstep_rank AUTO_INCREMENT =  1");
				}
				else
				{
					DB::table("tbl_codes")->where("code_used",0)->where("code_sold",0)->delete();
				}
			}

			if($product_list == true)
			{
				DB::table("tbl_item")->delete();
				DB::statement("ALTER TABLE tbl_stairstep_rank AUTO_INCREMENT =  1");
			}

			$return["status"]             = "success"; 
			$return["status_code"]        = 201; 
			$return["status_message"]     = "Data reset...";
		}
		else
		{
			$return["status"]             = "error"; 
			$return["status_code"]        = 400; 
			$return["status_message"][0]  = "Wrong Password";
		}

		return $return;
	}
}
