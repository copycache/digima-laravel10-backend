<?php
namespace App\Globals;

use App\Models\Tbl_achievers_rank;
use App\Models\Tbl_welcome_bonus_commissions;
use DB;
use Request;
use Carbon\Carbon;
use Validator;
use App\Models\Tbl_membership;
use App\Models\Tbl_membership_income;
use App\Models\Tbl_mlm_plan;
use App\Models\Tbl_membership_indirect_level;
use App\Models\Tbl_membership_unilevel_level;
use App\Models\Tbl_membership_cashback_level;
use App\Models\Tbl_membership_unilevel_or_level;
use App\Models\Tbl_membership_leveling_bonus_level;
use App\Models\Tbl_mlm_unilevel_settings;
use App\Models\Tbl_mlm_monoline_settings;
use App\Models\Tbl_mlm_pass_up_settings;
use App\Models\Tbl_stairstep_settings;
use App\Models\Tbl_stairstep_rank;
use App\Models\Tbl_binary_pairing;
use App\Models\Tbl_binary_settings;
use App\Models\Tbl_binary_points_settings;
use App\Models\Tbl_label;
use App\Models\Tbl_item;
use App\Models\Tbl_mlm_universal_pool_bonus_settings;
use App\Models\Tbl_mlm_universal_pool_bonus_maintain_settings;
use App\Models\Tbl_mlm_incentive_bonus;
use App\Globals\Get_plan;
use App\Globals\Audit_trail;
use App\Models\Tbl_membership_mentors_level;
use App\Models\Tbl_global_pool_bonus_settings;
use App\Models\Tbl_membership_vortex;
use App\Models\Tbl_vortex_settings;
use App\Models\Tbl_membership_gc_income;
use App\Models\Tbl_direct_bonus;
use App\Models\Tbl_incentive_setup;
use App\Models\Tbl_sponsor_matching;
use App\Models\Tbl_share_link_settings;
use App\Models\Tbl_watch_earn_settings;
use App\Models\Tbl_passive_unilevel_premium;
use App\Models\Tbl_indirect_settings;
use App\Models\Tbl_infinity_bonus_setup;
use App\Models\Tbl_leaders_support_settings;
use App\Models\Tbl_membership_product_level;
use App\Models\Tbl_membership_overriding_commission_level;
use App\Models\Tbl_wallet_log;
use App\Models\Tbl_referral_voucher_settings;
use App\Models\Tbl_overriding_commission_v2;
use App\Models\Tbl_reverse_pass_up_settings;
use App\Models\Tbl_pass_up_combination_income;
use App\Models\Tbl_pass_up_direct_combination_income;
use App\Models\Tbl_reverse_pass_up_combination_income;
use App\Models\Tbl_reverse_pass_up_direct_combination_income;
use App\Models\Tbl_unilevel_matrix_bonus_settings;
use App\Models\Tbl_unilevel_matrix_bonus_levels;
use App\Models\Tbl_livewell_rank;
use App\Models\Tbl_marketing_support_settings;
use App\Models\Tbl_marketing_support_setup;
use App\Models\Tbl_prime_refund_setup;
use App\Models\Tbl_milestone_bonus_settings;
use App\Models\Tbl_milestone_pairing_points_setup;
use App\Models\Tbl_milestone_points_setup;
use App\Models\Tbl_leaders_support_setup;
class Update_plan
{
	public static function SPONSOR_MATCHING_BONUS($plan,$label,$data)
	{
		$data      = json_decode($data,true);
		$user      = Request::user()->id;
		$action    = "Update Sponsor Matching Plan";
		$old_value = Get_plan::SPONSOR_MATCHING_BONUS();

		if($data != null)
		{
			if(Tbl_sponsor_matching::first())
			{
				$update["sponsor_matching_percent"]	  = $data["setup"]["sponsor_matching_percent"];
				DB::table("tbl_sponsor_matching")->update($update);
			}
			else
			{
				$insert["sponsor_matching_percent"]	  = $data["setup"]["sponsor_matching_percent"];
				Tbl_sponsor_matching::insert($insert);
			}

			$update = null;
			$insert = null;

			foreach($data["membership_settings"] as $memb)
			{
				$update["enable_sponsor_matching"]  = $memb["enable_sponsor_matching"];
				Tbl_membership::where("membership_id",$memb["membership_id"])->update($update);
			}
		}

		Plan::update_label($plan,$label);
		$update_plan["mlm_plan_enable"] = 1;
		Tbl_mlm_plan::where("mlm_plan_code",$plan)->update($update_plan);

		$new_value = Get_plan::SPONSOR_MATCHING_BONUS();
		Audit_trail::audit(serialize($old_value),serialize($new_value),$user,$action);

		$return["status"]         = "success";
		$return["update_status"]  = $update_plan["mlm_plan_enable"];
		$return["status_code"]    = 201;
		$return["status_message"] = "Settings updated...";

		return $return;
	}

	public static function VORTEX_PLAN($plan,$label,$data)
	{
		$data = json_decode($data,true);
		$user      = Request::user()->id;
		$action    = "Update Vortex Plan";
		$old_value = Get_plan::VORTEX_PLAN();

		if($data != null)
		{
			foreach($data["vortex_settings"] as $key => $value)
			{
				foreach($value as $key2 => $value2)
				{
					$check = $check = Tbl_membership_vortex::where("membership_id",$key)->where("membership_entry_id",$key2)->first();
					if($check)
					{
						$update["membership_vortex_token"] = $value2;
						Tbl_membership_vortex::where("membership_id",$key)->where("membership_entry_id",$key2)->update($update);
					}
					else
					{
						$insert["membership_id"]			= $key;
						$insert["membership_entry_id"]      = $key2;
						$insert["membership_vortex_token"]  = $value2;
						Tbl_membership_vortex::insert($insert);
					}
				}
			}

			$update = null;
			$insert = null;

			foreach($data["membership_settings"] as $memb)
			{
				$update["vortex_registered_token"]	= $memb["vortex_registered_token"];
				$update["vortex_gc_income"]			= $memb["vortex_gc_income"];
				Tbl_membership::where("membership_id",$memb["membership_id"])->update($update);
			}

			$update = null;
			$insert = null;

			if(Tbl_vortex_settings::first())
			{
				$update["vortex_slot_required"]	  = $data["setup"]["vortex_slot_required"];
				$update["vortex_token_required"]  = $data["setup"]["vortex_token_required"];
				$update["vortex_token_reward"]	  = $data["setup"]["vortex_token_reward"];
				DB::table("tbl_vortex_settings")->update($update);
			}
			else
			{
				$insert["vortex_slot_required"]	  = $data["setup"]["vortex_slot_required"];
				$insert["vortex_token_required"]  = $data["setup"]["vortex_token_required"];
				$insert["vortex_token_reward"]	  = $data["setup"]["vortex_token_reward"];
				Tbl_vortex_settings::insert($insert);
			}
		}

		Plan::update_label($plan,$label);
		$update_plan["mlm_plan_enable"] = 1;
		Tbl_mlm_plan::where("mlm_plan_code",$plan)->update($update_plan);

		$new_value = Get_plan::VORTEX_PLAN();
		Audit_trail::audit(serialize($old_value),serialize($new_value),$user,$action);

		$return["status"]         = "success";
		$return["update_status"]  = $update_plan["mlm_plan_enable"];
		$return["status_code"]    = 201;
		$return["status_message"] = "Settings updated...";

		return $return;
	}
	
	public static function DIRECT($plan,$label,$data)
	{
		$data = json_decode($data,true);
		$user      = Request::user()->id;
		$action    = "Update Direct";
		$old_value = Get_plan::DIRECT();
		if($data != null)
		{
			// dd($data["manage_direct_bonus"]);
			foreach ($data["manage_direct_bonus"] as $key => $value) 
			{
				$rules["hierarchy"] = "required|numeric|min:1|max:100";
		
				$validator = Validator::make($value, $rules);
		
				if ($validator->fails()) 
				{
					$return["status"]         = "error"; 
					$return["status_code"]    = 400; 
					$return["status_message"] = "Not Number";
		
					return $return;
				}
				else
				{
					$param["hierarchy"] 		              = $value["hierarchy"];
					$param["direct_bonus_checkpoint"]         = $value["direct_bonus_checkpoint"];
					$param["direct_bonus_amount"]             = $value["direct_bonus_amount"];
					if ($value["direct_bonus_id"]) 
					{
						$param["archive"] = $value["archive"];
						Tbl_direct_bonus::where("direct_bonus_id", $value["direct_bonus_id"])->update($param);
					}
					else
					{
						if($param["direct_bonus_checkpoint"] != 0 && $param["direct_bonus_amount"] !=0)
						{
							Tbl_direct_bonus::insert($param);
						}
					}
				}
			}
			foreach($data["direct_settings"] as $key => $value)
			{
				foreach($value as $key2 => $value2)
				{
					$check = Tbl_membership_income::where("membership_id",$key)->where("membership_entry_id",$key2)->first();
					if($check)
					{
						$update["membership_direct_income"] = $value2;
						Tbl_membership_income::where("membership_id",$key)->where("membership_entry_id",$key2)->update($update);
					}
					else
					{
						$insert["membership_id"]			= $key;
						$insert["membership_entry_id"]      = $key2;
						$insert["membership_direct_income"] = $value2;
						Tbl_membership_income::insert($insert);
					}
				}
			}
			foreach($data["direct_settings2"] as $key => $value)
			{
				foreach($value as $key2 => $value2)
				{
					$check = Tbl_membership_gc_income::where("membership_id",$key)->where("membership_entry_id",$key2)->first();
					if($check)
					{
						$update2["membership_gc_income"] = $value2;
						Tbl_membership_gc_income::where("membership_id",$key)->where("membership_entry_id",$key2)->update($update2);
					}
					else
					{
						$insert2["membership_id"]			= $key;
						$insert2["membership_entry_id"]      = $key2;
						$insert2["membership_gc_income"] = $value2;
						Tbl_membership_gc_income::insert($insert2);
					}
				}
			}
		}

		Plan::update_label($plan,$label);
		$update_plan["mlm_plan_enable"] = 1;
		Tbl_mlm_plan::where("mlm_plan_code",$plan)->update($update_plan);

		$new_value = Get_plan::DIRECT();
		Audit_trail::audit(serialize($old_value),serialize($new_value),$user,$action);

		$return["direct"]         = "ok";
		$return["status"]         = "success";
		$return["update_status"]  = $update_plan["mlm_plan_enable"];
		$return["status_code"]    = 201;
		$return["status_message"] = "Settings updated...";

		return $return;
	}

	public static function INDIRECT($plan,$label,$data)
	{

		$data = json_decode($data,true);
		$user      = Request::user()->id;
		$action    = "Update Indirect";
		$old_value = Get_plan::INDIRECT();
		$times = 0;
		if($data != null)
		{
			Tbl_membership_indirect_level::truncate();
			foreach($data["membership_settings"] as $key => $value)
			{
				$value["membership_indirect_level"] = $value["membership_indirect_level"] + 1;
				if(isset($data["indirect_settings"][$value["membership_id"]]))
				{
					/* GET THE DATA SETTINGS PER MEMBERSHIP */
					foreach($data["indirect_settings"][$value["membership_id"]] as $membership_entry_id => $per_membership)
					{
						$level = 2;
						/* GET THE DATA SETTINGS PER LEVEL OF TARGET MEMBERSHIP */
						foreach($per_membership as $level_target => $membership_indirect_income)
						{
							/* membership_entry_id  = membership_entry_id */
							/* membership_indirect_income = membership_indirect_income*/
							$check = Tbl_membership_indirect_level::where("membership_level",$level)->where("membership_id",$value["membership_id"])->where("membership_entry_id",$membership_entry_id)->first();
							if($check)
							{
								$update_level["membership_level"]		    = $level;
								$update_level["membership_id"]		        = $value["membership_id"];
								$update_level["membership_entry_id"]	    = $membership_entry_id;
								$update_level["membership_indirect_income"] = $membership_indirect_income;
								Tbl_membership_indirect_level::where("membership_level",$level)->where("membership_id",$value["membership_id"])->where("membership_entry_id",$membership_entry_id)->update($update_level);
							}
							else
							{
								$insert["membership_level"]		      = $level;
								$insert["membership_id"]		      = $value["membership_id"];
								$insert["membership_entry_id"]	      = $membership_entry_id;
								$insert["membership_indirect_income"] = $membership_indirect_income;
								Tbl_membership_indirect_level::insert($insert);
							}

							$level++;
							if($level > $value["membership_indirect_level"])
							{
								Tbl_membership_indirect_level::where("membership_level",">=",$level)->where("membership_id",$value["membership_id"])->delete();
								break;
							}
						}

					}
				}

				$update["membership_indirect_level"] = count(Tbl_membership_indirect_level::select("membership_level")->where("membership_id",$value["membership_id"])->groupBy("membership_level")->get());
				Tbl_membership::where("membership_id",$value["membership_id"])->update($update);
			}
			$update_indirect['indirect_points_enable'] = $data['indirect_points_settings']['indirect_points_enable'] != null ? $data['indirect_points_settings']['indirect_points_enable'] : 0;
			$update_indirect['indirect_points_minimum_conversion'] = $data['indirect_points_settings']['indirect_points_minimum_conversion'] != null ? $data['indirect_points_settings']['indirect_points_minimum_conversion'] : 0;
			Tbl_indirect_settings::where('indirect_settings_id',$data['indirect_points_settings']['indirect_settings_id'])->update($update_indirect);
		}

		Plan::update_label($plan,$label);
		$update_plan["mlm_plan_enable"] = 1;
		Tbl_mlm_plan::where("mlm_plan_code",$plan)->update($update_plan);

		$new_value = Get_plan::INDIRECT();
		Audit_trail::audit(serialize($old_value),serialize($new_value),$user,$action);

		$return["status"]         = "success";
		$return["update_status"]  = $update_plan["mlm_plan_enable"];
		$return["status_code"]    = 201;
		$return["status_message"] = "Settings updated...";

		return $return;
	}

	public static function UNILEVEL($plan,$label,$data)
	{
		$data = json_decode($data,true);
		$user      = Request::user()->id;
		$action    = "Update Unilevel";
		$old_value = Get_plan::UNILEVEL();
		if($data != null)
		{

			foreach($data["membership_settings"] as $key => $value)
			{
				if(isset($data["unilevel_settings"][$value["membership_id"]]))
				{
					/* GET THE DATA SETTINGS PER MEMBERSHIP */
					foreach($data["unilevel_settings"][$value["membership_id"]] as $membership_id => $per_membership)
					{
						$level = 1;
						/* GET THE DATA SETTINGS PER LEVEL OF TARGET MEMBERSHIP */
						foreach($per_membership as $membership_percentage)
						{
							/* membership_entry_id  = membership_entry_id */
							/* membership_percentage = membership_percentage*/
							$check = Tbl_membership_unilevel_level::where("membership_level",$level)->where("membership_id",$value["membership_id"])->where("membership_entry_id",$membership_id)->first();
							if($check)
							{
								$update_level["membership_level"]		    = $level;
								$update_level["membership_id"]		        = $value["membership_id"];
								$update_level["membership_entry_id"]	    = $membership_id;
								$update_level["membership_percentage"] 		= $membership_percentage;
								Tbl_membership_unilevel_level::where("membership_level",$level)->where("membership_id",$value["membership_id"])->where("membership_entry_id", $membership_id)->update($update_level);
							}
							else
							{
								$insert["membership_level"]		      = $level;
								$insert["membership_id"]		      = $value["membership_id"];
								$insert["membership_entry_id"]	      = $membership_id;
								$insert["membership_percentage"]      = $membership_percentage;
								Tbl_membership_unilevel_level::insert($insert);
							}

							$level++;
							if($level > $value["membership_unilevel_level"])
							{
								Tbl_membership_unilevel_level::where("membership_level",">=",$level)->where("membership_id",$value["membership_id"])->where("membership_entry_id", $membership_id)->delete();
							}
						}

					}
				}
				$update["membership_unilevel_level"] = $value['membership_unilevel_level'];
				// count(Tbl_membership_unilevel_level::select("membership_level")->where("membership_id",$value["membership_id"])->groupBy("membership_level")->get());

				$update["membership_required_pv"]    = $value["membership_required_pv"];
				Tbl_membership::where("membership_id",$value["membership_id"])->update($update);
			}
		}

		Plan::update_label($plan,$label);
		$update_plan["mlm_plan_enable"] = 1;
		Tbl_mlm_plan::where("mlm_plan_code",$plan)->update($update_plan);
		$update_unilevel_settings["personal_as_group"]  	   = $data["setup"]["personal_as_group"];
		$update_unilevel_settings["gpv_to_wallet_conversion"]  = $data["setup"]["gpv_to_wallet_conversion"];
		$update_unilevel_settings["auto_ship"]  			   = $data["setup"]["auto_ship"];
		$update_unilevel_settings["is_dynamic"]  			   = $data["setup"]["is_dynamic"];
		$update_unilevel_settings["unilevel_complan_show_to"]  			   = $data["setup"]["unilevel_complan_show_to"];
		Plan::update_label("PERSONAL_PV",$data["setup"]["personal_pv"]);
		Plan::update_label("GROUP_PV",$data["setup"]["group_pv"]);

		Tbl_mlm_unilevel_settings::where("mlm_unilevel_settings_id",1)->update($update_unilevel_settings);

		$new_value = Get_plan::UNILEVEL();
		Audit_trail::audit(serialize($old_value),serialize($new_value),$user,$action);

		$return["status"]         = "success";
		$return["update_status"]  = $update_plan["mlm_plan_enable"];
		$return["status_code"]    = 201;
		$return["status_message"] = "Settings updated...";
		return $return;
	}

	public static function STAIRSTEP($plan,$label,$data)
	{
		$data = json_decode($data,true);
		$user      = Request::user()->id;
		$action    = "Update Stairstep";
		$old_value = Get_plan::STAIRSTEP();
		if($data != null)
		{

			$ctr = 1;
			foreach($data["stairstep_settings"] as $index => $value)
			{
				if($value["stairstep_rank_name"] != "" && strlen(trim($value["stairstep_rank_name"])) != 0)
				{

					$check = Tbl_stairstep_rank::where("stairstep_rank_level",$ctr)->count();
					/*JAMES CHANGE THE CHECKING QUERY TO COUNT->THIS IS FIRST BEFORE*/
					if($check == 0)
					{
						$insert["stairstep_rank_name"]			= $value["stairstep_rank_name"];
						$insert["stairstep_rank_override"]		= $value["stairstep_rank_override"] ? $value["stairstep_rank_override"] : 0;
						$insert["equal_bonus"]		            = $value["equal_bonus"] ? $value["equal_bonus"] : 0;
						$insert["breakaway_level"]		        = $value["breakaway_level"] ? $value["breakaway_level"] : 0;
						$insert["stairstep_rank_personal"]		= $value["stairstep_rank_personal"] ? $value["stairstep_rank_personal"] : 0;
						$insert["stairstep_rank_personal_all"]	= $value["stairstep_rank_personal_all"] ? $value["stairstep_rank_personal_all"] : 0;
						$insert["stairstep_rank_group_all"]		= $value["stairstep_rank_group_all"] ? $value["stairstep_rank_group_all"] : 0;

						$insert["stairstep_commission"]			= $value["stairstep_commission"] ? $value["stairstep_commission"] : 0;
						$insert["stairstep_advancement_bonus"]	= $value["stairstep_advancement_bonus"] ? $value["stairstep_advancement_bonus"] : 0;
						$insert["check_match_level"]			= $value["check_match_level"] ? $value["check_match_level"] : 0;
						$insert["check_match_percentage"]		= $value["check_match_percentage"] ? $value["check_match_percentage"] : 0;

						$insert["stairstep_rank_upgrade"]		= $value["stairstep_rank_upgrade"] ? $value["stairstep_rank_upgrade"] : 0;
						$insert["stairstep_rank_name_id"]		= $value["stairstep_rank_name_id"] ? $value["stairstep_rank_name_id"] : 0;

						$insert["stairstep_direct_referral"]    = $value["stairstep_direct_referral"] ? $value["stairstep_direct_referral"] : 0;

						$insert["stairstep_rank_level"]			= $ctr;
						$insert["stairstep_rank_date_created"]	= Carbon::now();

						Tbl_stairstep_rank::insertGetId($insert);

					}
					else
					{
						$update["stairstep_rank_name"]			= $value["stairstep_rank_name"];
						$update["stairstep_rank_override"]		= $value["stairstep_rank_override"] ? $value["stairstep_rank_override"] : 0;
						$update["equal_bonus"]				    = $value["equal_bonus"] ? $value["equal_bonus"] : 0;
						$update["breakaway_level"]				= $value["breakaway_level"] ? $value["breakaway_level"] : 0;
						$update["stairstep_rank_personal"]		= $value["stairstep_rank_personal"] ? $value["stairstep_rank_personal"] : 0;
						$update["stairstep_rank_personal_all"]	= $value["stairstep_rank_personal_all"] ? $value["stairstep_rank_personal_all"] : 0;
						$update["stairstep_rank_group_all"]		= $value["stairstep_rank_group_all"] ? $value["stairstep_rank_group_all"] : 0;

						$update["stairstep_commission"]			= $value["stairstep_commission"] ? $value["stairstep_commission"] : 0;
						$update["stairstep_advancement_bonus"]	= $value["stairstep_advancement_bonus"] ? $value["stairstep_advancement_bonus"] : 0;
						$update["check_match_level"]			= $value["check_match_level"] ? $value["check_match_level"] : 0;
						$update["check_match_percentage"]		= $value["check_match_percentage"] ? $value["check_match_percentage"] : 0;
						$update["archive"]						= 0;

						$update["stairstep_rank_upgrade"]		= $value["stairstep_rank_upgrade"] ? $value["stairstep_rank_upgrade"] : 0;
						$update["stairstep_rank_name_id"]		= $value["stairstep_rank_name_id"] ? $value["stairstep_rank_name_id"] : 0;

						$update["stairstep_direct_referral"]	= $value["stairstep_direct_referral"] ? $value["stairstep_direct_referral"] : 0;

						Tbl_stairstep_rank::where("stairstep_rank_level",$ctr)->update($update);
					}
					$ctr++;
				}
			}

			if($data["stairstep_settings_end"]["stairstep_rank_name"] != "" && strlen(trim($data["stairstep_settings_end"]["stairstep_rank_name"])) != 0 )
			{
				$check = Tbl_stairstep_rank::where("stairstep_rank_level",$ctr)->count();
				/*JAMES CHANGE THE CHECKING QUERY TO COUNT->THIS IS FIRST BEFORE*/
				if($check==0)
				{
					$insert["stairstep_rank_name"]			= $data["stairstep_settings_end"]["stairstep_rank_name"];
					$insert["stairstep_rank_override"]		= $data["stairstep_settings_end"]["stairstep_rank_override"] ? $data["stairstep_settings_end"]["stairstep_rank_override"] : 0;
					$insert["equal_bonus"]					= $data["stairstep_settings_end"]["equal_bonus"] ? $data["stairstep_settings_end"]["equal_bonus"] : 0;
					$insert["breakaway_level"]				= $data["stairstep_settings_end"]["breakaway_level"] ? $data["stairstep_settings_end"]["breakaway_level"] : 0;
					$insert["stairstep_rank_personal"]		= $data["stairstep_settings_end"]["stairstep_rank_personal"] ? $data["stairstep_settings_end"]["stairstep_rank_personal"] : 0;
					$insert["stairstep_rank_personal_all"]	= $data["stairstep_settings_end"]["stairstep_rank_personal_all"] ? $data["stairstep_settings_end"]["stairstep_rank_personal_all"] : 0;
					$insert["stairstep_rank_group_all"]		= $data["stairstep_settings_end"]["stairstep_rank_group_all"] ? $data["stairstep_settings_end"]["stairstep_rank_group_all"] : 0;

					$insert["stairstep_commission"]			= $data["stairstep_settings_end"]["stairstep_commission"] ? $data["stairstep_settings_end"]["stairstep_commission"] : 0;
					$insert["stairstep_advancement_bonus"]	= $data["stairstep_settings_end"]["stairstep_advancement_bonus"] ? $data["stairstep_settings_end"]["stairstep_advancement_bonus"] : 0;
					$insert["check_match_level"]			= $data["stairstep_settings_end"]["check_match_level"] ? $data["stairstep_settings_end"]["check_match_level"] : 0;
					$insert["check_match_percentage"]		= $data["stairstep_settings_end"]["check_match_percentage"] ? $data["stairstep_settings_end"]["check_match_percentage"] : 0;

					$insert["stairstep_rank_upgrade"]		= $data["stairstep_settings_end"]["stairstep_rank_upgrade"] ? $data["stairstep_settings_end"]["stairstep_rank_upgrade"] : 0;
					$insert["stairstep_rank_name_id"]		= $data["stairstep_settings_end"]["stairstep_rank_name_id"] ? $data["stairstep_settings_end"]["stairstep_rank_name_id"] : 0;

					$insert["stairstep_direct_referral"]	= $data["stairstep_settings_end"]["stairstep_direct_referral"] ? $data["stairstep_settings_end"]["stairstep_direct_referral"] : 0;


					$insert["stairstep_rank_date_created"]	= Carbon::now();
					$insert["stairstep_rank_level"]			= $ctr;
					Tbl_stairstep_rank::insert($insert);
					$ctr++;
				}
				else
				{
					$update["stairstep_rank_name"]			= $data["stairstep_settings_end"]["stairstep_rank_name"];
					$update["stairstep_rank_override"]		= $data["stairstep_settings_end"]["stairstep_rank_override"] ? $data["stairstep_settings_end"]["stairstep_rank_override"] : 0;
					$update["equal_bonus"]					= $data["stairstep_settings_end"]["equal_bonus"] ? $data["stairstep_settings_end"]["equal_bonus"] : 0;
					$update["breakaway_level"]				= $data["stairstep_settings_end"]["breakaway_level"] ? $data["stairstep_settings_end"]["breakaway_level"] : 0;
					$update["stairstep_rank_personal"]		= $data["stairstep_settings_end"]["stairstep_rank_personal"] ? $data["stairstep_settings_end"]["stairstep_rank_personal"] : 0;
					$update["stairstep_rank_personal_all"]	= $data["stairstep_settings_end"]["stairstep_rank_personal_all"] ? $data["stairstep_settings_end"]["stairstep_rank_personal_all"] : 0;
					$update["stairstep_rank_group_all"]		= $data["stairstep_settings_end"]["stairstep_rank_group_all"] ? $data["stairstep_settings_end"]["stairstep_rank_group_all"] : 0;

					$update["stairstep_commission"]			= $data["stairstep_settings_end"]["stairstep_commission"] ? $data["stairstep_settings_end"]["stairstep_commission"] : 0;
					$update["stairstep_advancement_bonus"]	= $data["stairstep_settings_end"]["stairstep_advancement_bonus"] ? $data["stairstep_settings_end"]["stairstep_advancement_bonus"] : 0;
					$update["check_match_level"]			= $data["stairstep_settings_end"]["check_match_level"] ? $data["stairstep_settings_end"]["check_match_level"] : 0;
					$update["check_match_percentage"]		= $data["stairstep_settings_end"]["check_match_percentage"] ? $data["stairstep_settings_end"]["check_match_percentage"] : 0;
					$update["archive"]						= 0;

					$update["stairstep_rank_upgrade"]		= $data["stairstep_settings_end"]["stairstep_rank_upgrade"] ? $data["stairstep_settings_end"]["stairstep_rank_upgrade"] : 0;
					$update["stairstep_rank_name_id"]		= $data["stairstep_settings_end"]["stairstep_rank_name_id"] ? $data["stairstep_settings_end"]["stairstep_rank_name_id"] : 0;
					
					$update["stairstep_direct_referral"]	= $data["stairstep_settings_end"]["stairstep_direct_referral"] ? $data["stairstep_settings_end"]["stairstep_direct_referral"] : 0;

					Tbl_stairstep_rank::where("stairstep_rank_level",$ctr)->update($update);
					$ctr++;
				}
			}

			$stairsteps             = Tbl_stairstep_rank::get();
			foreach($stairsteps as $rank=>$stairstep)
			{
				$get_item 		   = Tbl_item::get();
				foreach($get_item as $key => $item)
	        	{
	        		$insert_rank_discount['stairstep_rank_id'] 		= $stairstep->stairstep_rank_id;
	        		$insert_rank_discount['item_id']				= $item->item_id;
	        		$check = DB::table('tbl_item_stairstep_rank_discount')->where('item_id',$item->item_id)->where('stairstep_rank_id',$stairstep->stairstep_rank_id)->count();
	        		if($check == 0)
	        		{
	        			DB::table('tbl_item_stairstep_rank_discount')->insert($insert_rank_discount);
	        		}
	        	}
			}


			$update_archive["archive"] = 1;
			Tbl_stairstep_rank::where("stairstep_rank_level",">=",$ctr)->update($update_archive);
		}


		Plan::update_label($plan,$label);
		$update_plan["mlm_plan_enable"] = 1;
		Tbl_mlm_plan::where("mlm_plan_code",$plan)->update($update_plan);

		$update_stairstep_settings["personal_as_group"]  		  = $data["setup"]["personal_as_group"];
		$update_stairstep_settings["sgpv_to_wallet_conversion"]   = $data["setup"]["sgpv_to_wallet_conversion"];
		$update_stairstep_settings["live_update"]  		          = $data["setup"]["live_update"];
		$update_stairstep_settings["auto_ship"]  		          = $data["setup"]["auto_ship"];
		Tbl_stairstep_settings::where("stairstep_settings_id",1)->update($update_stairstep_settings);

		Plan::update_label("PERSONAL_STAIRSTEP_PV_LABEL",$data["setup"]["personal_stairstep_pv_label"]);
		Plan::update_label("GROUP_STAIRSTEP_PV_LABEL",$data["setup"]["group_stairstep_pv_label"]);
		Plan::update_label("STAIRSTEP_EARNING_POINTS_LABEL",$data["setup"]["earning_label_points"]);

		$new_value = Get_plan::STAIRSTEP();
		Audit_trail::audit(serialize($old_value),serialize($new_value),$user,$action);

		$return["status"]         = "success";
		$return["update_status"]  = $update_plan["mlm_plan_enable"];
		$return["status_code"]    = 201;
		$return["status_message"] = "Settings updated...";

		return $return;
	}


	public static function BINARY($plan,$label,$data)
	{
		$data = json_decode($data,true);
		// dd($data);
		$user      = Request::user()->id;
		$action    = "Update Binary";
		$old_value = Get_plan::BINARY();
		if($data != null)
		{

			$combined_id = array();
			foreach($data["binary_settings_pair"] as $index => $value)
			{
				if($value["binary_pairing_bonus"] != "" && $value["binary_pairing_bonus"] != 0)
				{
					$left       = $value["binary_pairing_left"]  ? $value["binary_pairing_left"]  : 0;
					$right      = $value["binary_pairing_right"] ? $value["binary_pairing_right"] : 0;
					$membership = $value["binary_pairing_membership"] ? $value["binary_pairing_membership"] : null;
					$check      = Tbl_binary_pairing::where("binary_pairing_left",$left)->where("binary_pairing_right",$right)->where("binary_pairing_membership", $membership)->first();
					if(!$check)
					{
						$insert["binary_pairing_left"]	      = $left;
						$insert["binary_pairing_right"]		  = $right;
						$insert["binary_pairing_bonus"]		  = $value["binary_pairing_bonus"] ? $value["binary_pairing_bonus"] : 0;
						$insert["binary_pairing_membership"]  = $value["binary_pairing_membership"] ? $value["binary_pairing_membership"] : 0;
						$insert["binary_pairing_membership"]  = $insert["binary_pairing_membership"] == 0 ? null : $insert["binary_pairing_membership"];
						$id 								  = Tbl_binary_pairing::insertGetId($insert);
						array_push($combined_id,$id);
					}
					else
					{
						$update["binary_pairing_bonus"]       = $value["binary_pairing_bonus"] ? $value["binary_pairing_bonus"] : 0;
						$update["archive"]				      = 0;

						Tbl_binary_pairing::where("binary_pairing_left",$left)->where("binary_pairing_membership", $membership)->where("binary_pairing_right",$right)->update($update);
						array_push($combined_id,$check->binary_pairing_id);
					}
				}
			}
			if($data["binary_settings_pair_end"]["binary_pairing_bonus"] != "" && $data["binary_settings_pair_end"]["binary_pairing_bonus"] != 0)
			{
				$left       = $data["binary_settings_pair_end"]["binary_pairing_left"]  ? $data["binary_settings_pair_end"]["binary_pairing_left"]  : 0;
				$right      = $data["binary_settings_pair_end"]["binary_pairing_right"] ? $data["binary_settings_pair_end"]["binary_pairing_right"] : 0;
				$membership = $data["binary_settings_pair_end"]["binary_pairing_membership"] ? $data["binary_settings_pair_end"]["binary_pairing_membership"] : null;
				
				$check = Tbl_binary_pairing::where("binary_pairing_left",$left)->where("binary_pairing_right",$right)->where("binary_pairing_membership", $membership)->first();
				if(!$check)
				{
					$insert["binary_pairing_left"]	     = $left;
					$insert["binary_pairing_right"]		 = $right;
					$insert["binary_pairing_bonus"]		 = $data["binary_settings_pair_end"]["binary_pairing_bonus"] ? $data["binary_settings_pair_end"]["binary_pairing_bonus"] : 0;
					$insert["binary_pairing_membership"] = $data["binary_settings_pair_end"]["binary_pairing_membership"] ? $data["binary_settings_pair_end"]["binary_pairing_membership"] : 0;
					$insert["binary_pairing_membership"] = $insert["binary_pairing_membership"] == 0 ? null : $insert["binary_pairing_membership"];

					$id 								 = Tbl_binary_pairing::insertGetId($insert);
					array_push($combined_id,$id);
				}
				else
				{
					$update["binary_pairing_bonus"]       = $data["binary_settings_pair_end"]["binary_pairing_bonus"] ? $data["binary_settings_pair_end"]["binary_pairing_bonus"] : 0;
					$update["archive"]				      = 0;

					Tbl_binary_pairing::where("binary_pairing_left",$left)->where("binary_pairing_membership", $membership)->where("binary_pairing_right",$right)->update($update);
					array_push($combined_id,$check->binary_pairing_id);
				}
			}

			$update_archive["archive"] = 1;
			Tbl_binary_pairing::whereNotIn("binary_pairing_id",$combined_id)->update($update_archive);


			foreach($data["binary_settings"] as $key => $value)
			{
				foreach($value as $key2 => $value2)
				{
					$check = Tbl_binary_points_settings::where("membership_id",$key)->where("membership_entry_id",$key2)->first();
					if($check)
					{
						$update_pts["membership_binary_points"] = $value2;
						Tbl_binary_points_settings::where("membership_id",$key)->where("membership_entry_id",$key2)->update($update_pts);
					}
					else
					{
						$insert_pts["membership_id"]			= $key;
						$insert_pts["membership_entry_id"]      = $key2;
						$insert_pts["membership_binary_points"] = $value2;
						Tbl_binary_points_settings::insert($insert_pts);
					}
				}
			}
			foreach($data["binary_slot_limit_settings"] as $key => $slot_limit)
			{
				foreach($slot_limit as $key2 => $limit)
				{
					$check = Tbl_binary_points_settings::where("membership_id",$key)->where("membership_entry_id",$key2)->first();
					if($check)
					{
						$update_slot_limit["max_slot_per_level"] = $limit;
						Tbl_binary_points_settings::where("membership_id",$key)->where("membership_entry_id",$key2)->update($update_slot_limit);
					}
					else
					{
						$insert_slot_limit["membership_id"] = $key;
						$insert_slot_limit["membership_entry_id"] = $key2;
						$insert_slot_limit["max_slot_per_level"] = $limit;
						Tbl_binary_points_settings::insert($insert_slot_limit);
					}
				}
			}

			$level_mentors = array();
			foreach($data["membership_settings"] as $key => $value)
			{
				$update_set["membership_pairings_per_day"] = $value["membership_pairings_per_day"];
				$update_set["max_earnings_per_cycle"] = $value["max_earnings_per_cycle"];
				$update_set["mentors_level"] 			   = $value["mentors_level"];
				$update_set["can_receive_points"] 		   = $value["can_receive_points"];
				$update_set["binary_placement_enable"] 	   = $value["binary_placement_enable"];
				$update_set["flushout_enable"] 	   		   = $value["flushout_enable"];
				$update_set["membership_pairings_per_day"] = $value["membership_pairings_per_day"];
				$update_set["max_points_per_level"] = $value["max_points_per_level"];
				$update_set["max_earnings_per_level"] = $value["max_earnings_per_level"];
				$update_set["binary_required_direct"] = $value["binary_required_direct"];
				$update_set["binary_realtime_commission"] = $value["binary_realtime_commission"];
				$update_set["binary_waiting_commission_reset_days"] = $value["binary_waiting_commission_reset_days"];

				Tbl_membership::where("membership_id",$value["membership_id"])->update($update_set);

				$level_mentors[$value["membership_id"]] = $value["mentors_level"];
			}

			foreach($data["mentors_settings"] as $key => $value)
			{
				$level = 1;
				if($value)
				{
					foreach($value as $key2 => $value2)
					{
						if($key2 != 0)
						{
							$check = $check = Tbl_membership_mentors_level::where("membership_id",$key)->where("membership_level",$key2)->first();
							if($check)
							{
								$update_pts_mentor["mentors_bonus"]     = $value2["mentors_bonus"];
								$update_pts_mentor["mentors_direct"]    = $value2["mentors_direct"];
								Tbl_membership_mentors_level::where("membership_id",$key)->where("membership_level",$key2)->update($update_pts_mentor);
							}
							else
							{
								$insert_pts_mentor["membership_level"]	= $key2;
								$insert_pts_mentor["membership_id"]		= $key;
								$insert_pts_mentor["mentors_bonus"]     = $value2["mentors_bonus"];
								$insert_pts_mentor["mentors_direct"]    = $value2["mentors_direct"];
								Tbl_membership_mentors_level::insert($insert_pts_mentor);
							}

							$level++;
							if(isset($level_mentors[$key])) {
								if($level > $level_mentors[$key])
								{
									Tbl_membership_mentors_level::where("membership_level",">=",$level)->where("membership_id",$key)->delete();
									break;
								}
							} else {
								Tbl_membership_mentors_level::where("membership_id",$key)->delete();
								break;
							}
						}
					}
				}
			}
			$membership_binary_level = Tbl_membership::where("archive",0)->select('membership_id')->get();
			foreach ($membership_binary_level as $key => $value)
			{
				$update_membership_binary_level['membership_binary_level'] =  $data["membership_level"][$value["membership_id"]] ? $data["membership_level"][$value["membership_id"]] :0;
				Tbl_membership::where("membership_id",$value["membership_id"])->update($update_membership_binary_level);
			}
			
			if(isset($data['binary_projected_income_label']))
			{
				Plan::update_label("BINARY_PROJECTED_INCOME",$data['binary_projected_income_label']);
			}

			if(isset($data['mentors_bonus_label']))
			{
				Plan::update_label("MENTORS_BONUS",$data['mentors_bonus_label']);
			}
		}


		$update_plan["mlm_plan_enable"] = 1;
		Tbl_mlm_plan::where("mlm_plan_code",$plan)->update($update_plan);

		Plan::update_label($plan,$label);
		Plan::update_label("BINARY_POINTS_LEFT",$data["setup"]["binary_points_left"]);
		Plan::update_label("BINARY_POINTS_RIGHT",$data["setup"]["binary_points_right"]);
		$update_binary_settings["strong_leg_retention"]  	  = $data["setup"]["strong_leg_retention"];
		$update_binary_settings["gc_pairing_count"]           = $data["setup"]["gc_pairing_count"];
		$update_binary_settings["cycle_per_day"]  			  = $data["setup"]["cycle_per_day"];
		$update_binary_settings["binary_limit_type"]  		  = $data["setup"]["binary_limit_type"];
		$update_binary_settings["gc_paring_amount"]  		  = $data["setup"]["gc_paring_amount"] == "" ? 0 : $data["setup"]["gc_paring_amount"];
		$update_binary_settings["amount_binary_limit"]  	  = $data["setup"]["amount_binary_limit"] == "" ? 0 : $data["setup"]["amount_binary_limit"];
		$update_binary_settings["strong_leg_limit_points"]	  = $data["setup"]["strong_leg_limit_points"];
		$update_binary_settings["crossline"]  			      = (int)$data["setup"]["crossline"];
		$update_binary_settings["included_binary_repurchase"] = $data["setup"]["included_binary_repurchase"];
		$update_binary_settings["sponsor_matching_cycle"]	  = $data["setup"]["sponsor_matching_cycle"];
		$update_binary_settings["sponsor_matching_limit"]	  = $data["setup"]["sponsor_matching_limit"];
		$update_binary_settings["mentors_matching_cycle"]	  = $data["setup"]["mentors_matching_cycle"];
		$update_binary_settings["mentors_matching_limit"]	  = $data["setup"]["mentors_matching_limit"];
		$update_binary_settings["binary_points_enable"]	      = $data["setup"]["binary_points_enable"];
		$update_binary_settings["binary_points_minimum_conversion"]	  = $data["setup"]["binary_points_minimum_conversion"];
		$update_binary_settings["mentors_points_enable"]	      = $data["setup"]["mentors_points_enable"];
		$update_binary_settings["mentors_points_minimum_conversion"]	  = $data["setup"]["mentors_points_minimum_conversion"];
		$update_binary_settings["binary_extreme_position"]	  = $data["setup"]["binary_extreme_position"];
		$update_binary_settings["binary_maximum_slot_per_level_enable"]	  = $data["setup"]["binary_maximum_slot_per_level_enable"];
		$update_binary_settings["binary_maximum_points_per_level_enable"]	  = $data["setup"]["binary_maximum_points_per_level_enable"];
		$update_binary_settings["show_slot_tracker"]	  = $data["setup"]["binary_maximum_slot_per_level_enable"] ? $data["setup"]["show_slot_tracker"] : 0;
		$update_binary_settings["show_earnings_tracker"]	  = $data["setup"]["binary_maximum_points_per_level_enable"] ? $data["setup"]["show_earnings_tracker"] : 0;
		$update_binary_settings["show_earnings_tracker_per_cycle"]	  = $data["setup"]["show_earnings_tracker_per_cycle"];
		$update_binary_settings["binary_required_direct_enable"]	  = $data["setup"]["binary_required_direct_enable"];
		$update_binary_settings["minimum_membership_for_realtime_commission"]	  = $data["setup"]["minimum_membership_for_realtime_commission"];
		$update_binary_settings["binary_auto_placement_based_on_direct"] = $data["setup"]["binary_auto_placement_based_on_direct"];
		$update_binary_settings["binary_number_of_direct_for_auto_placement"] = $data["setup"]["binary_number_of_direct_for_auto_placement"];
		$update_binary_settings["binary_priority_leg_position"] = $data["setup"]["binary_priority_leg_position"];
		$update_binary_settings["binary_default_position_without_spill"] = $data["setup"]["binary_default_position_without_spill"];

		Tbl_binary_settings::where("binary_settings_id",1)->update($update_binary_settings);

		$new_value = Get_plan::BINARY();
		Audit_trail::audit(serialize($old_value),serialize($new_value),$user,$action);

		$return["status"]         = "success";
		$return["update_status"]  = $update_plan["mlm_plan_enable"];
		$return["status_code"]    = 201;
		$return["status_message"] = "Settings updated...";

		return $return;
	}
	
	public static function CASHBACK($plan,$label,$data)
	{
		$data = json_decode($data,true);
		$user      = Request::user()->id;
		$action    = "Update Cashback";
		$old_value = Get_plan::CASHBACK();
		if($data != null)
		{

			foreach($data["membership_settings"] as $key => $value)
			{
				$value["membership_cashback_level"] = $value["membership_cashback_level"];

				if(isset($data["cashback_settings"][$value["membership_id"]]))
				{
					$level = 1;
					foreach($data["cashback_settings"][$value["membership_id"]] as $level_target => $membership_cashback_income)
					{
						// if($level_target > 1)
						// {

							$check = Tbl_membership_cashback_level::where("membership_level",$level_target)->where("membership_id",$value["membership_id"])->where("membership_entry_id",$level_target)->first();
							if($check)
							{
								$update_level["membership_level"]		    = $level_target;
								$update_level["membership_id"]		        = $value["membership_id"];
								$update_level["membership_entry_id"]	    = $level_target;
								$update_level["membership_cashback_income"] = $membership_cashback_income;
								Tbl_membership_cashback_level::where("membership_level",$level_target)->where("membership_id",$value["membership_id"])->where("membership_entry_id",$level_target)->update($update_level);

							}
							else
							{
								$insert["membership_level"]		    	= $level_target;
								$insert["membership_id"]		     	= $value["membership_id"];
								$insert["membership_entry_id"]	    	= $level_target;
								$insert["membership_cashback_income"] = $membership_cashback_income;
								$insert["membership_cashback_income"] = $membership_cashback_income;
								Tbl_membership_cashback_level::insert($insert);
							}
						// }

						$level++;
						if($level > $value["membership_cashback_level"])
						{
							Tbl_membership_cashback_level::where("membership_level",">=",$level)->where("membership_id",$value["membership_id"])->delete();
							break;
						}



					}

				}

				$update["membership_cashback_level"] = count(Tbl_membership_cashback_level::select("membership_level")->where("membership_id",$value["membership_id"])->groupBy("membership_level")->get());
				Tbl_membership::where("membership_id",$value["membership_id"])->update($update);
			}
		}

		Plan::update_label($plan,$label);
		$update_plan["mlm_plan_enable"] = 1;
		Tbl_mlm_plan::where("mlm_plan_code",$plan)->update($update_plan);

		$new_value = Get_plan::CASHBACK();
		Audit_trail::audit(serialize($old_value),serialize($new_value),$user,$action);

		$return["status"]         = "success";
		$return["update_status"]  = $update_plan["mlm_plan_enable"];
		$return["status_code"]    = 201;
		$return["status_message"] = "Settings updated...";

		return $return;

	}

	public static function MONOLINE($plan,$label,$data)
    {
        $data = json_decode($data,true);
		// dd($data);
		$user      = Request::user()->id;
		$action    = "Update Monoline";
		$old_value = Get_plan::MONOLINE();
        if($data != null)
        {

            // dd($data);
            foreach($data as $key => $value)
            {
            // dd($value);
                $check =Tbl_mlm_monoline_settings::where("membership_id",$value["membership_id"])->first();
                if (!$check)
                {
              		if ($value["monoline_settings"]["monoline_percent"] <= 100 && $value["monoline_settings"]["monoline_percent"] >= 0 && $value["monoline_settings"]["max_price"] < 0)
              		{
              			Plan::update_label($plan,$label);
	                    $update_plan["mlm_plan_enable"] = 1;
	                    Tbl_mlm_plan::where("mlm_plan_code",$plan)->update($update_plan);
	                    $insert_monoline_settings["membership_id"]     = $value["membership_id"];
	                    $insert_monoline_settings["max_price"]         = 0;
	                    $insert_monoline_settings["monoline_percent"]  = 0;


	                    Tbl_mlm_monoline_settings::insert($insert_monoline_settings);
	                    $return["status"]         = "success";
	                    $return["update_status"]  = $update_plan["mlm_plan_enable"];
	                    $return["status_code"]    = 201;
	                    $return["status_message"] = "Settings updated...";
              		}
              		elseif ($value["monoline_settings"]["max_price"] < 0 )
              		{
              			Plan::update_label($plan,$label);
	                    $update_plan["mlm_plan_enable"] = 1;
	                    Tbl_mlm_plan::where("mlm_plan_code",$plan)->update($update_plan);
	                    $insert_monoline_settings["membership_id"]     = $value["membership_id"];
	                    $insert_monoline_settings["max_price"]         = 0;
	                    $insert_monoline_settings["monoline_percent"]  = 0;


	                    Tbl_mlm_monoline_settings::insert($insert_monoline_settings);
	                    $return["status"]         = "success";
	                    $return["update_status"]  = $update_plan["mlm_plan_enable"];
	                    $return["status_code"]    = 201;
	                    $return["status_message"] = "Settings updated...";
              		}
              		elseif ($value["monoline_settings"]["monoline_percent"] < 0 && $value["monoline_settings"]["max_price"] < 0 )
              		{
              			Plan::update_label($plan,$label);
	                    $update_plan["mlm_plan_enable"] = 1;
	                    Tbl_mlm_plan::where("mlm_plan_code",$plan)->update($update_plan);
	                    $insert_monoline_settings["membership_id"]     = $value["membership_id"];
	                    $insert_monoline_settings["max_price"]         = 0;
	                    $insert_monoline_settings["monoline_percent"]  = 0;


	                    Tbl_mlm_monoline_settings::insert($insert_monoline_settings);
	                    $return["status"]         = "success";
	                    $return["update_status"]  = $update_plan["mlm_plan_enable"];
	                    $return["status_code"]    = 201;
	                    $return["status_message"] = "Settings updated...";
              		}
              		else
              		{
		                Plan::update_label($plan,$label);
	                    $update_plan["mlm_plan_enable"] = 1;
	                    Tbl_mlm_plan::where("mlm_plan_code",$plan)->update($update_plan);
	                    $insert_monoline_settings["membership_id"]     = $value["membership_id"];
	                    $insert_monoline_settings["max_price"]         = $value["monoline_settings"]["max_price"];
	                    $insert_monoline_settings["monoline_percent"]  = $value["monoline_settings"]["monoline_percent"];


	                    Tbl_mlm_monoline_settings::insert($insert_monoline_settings);
	                    $return["status"]         = "success";
	                    $return["update_status"]  = $update_plan["mlm_plan_enable"];
	                    $return["status_code"]    = 201;
	                    $return["status_message"] = "Settings updated...";
              		}
                }
                else
                {
                	if ($value["monoline_settings"]["monoline_percent"] > 100 || $value["monoline_settings"]["monoline_percent"] < 0)
                	{
              			Plan::update_label($plan,$label);
	                    $update_plan["mlm_plan_enable"] = 1;
	                    Tbl_mlm_plan::where("mlm_plan_code",$plan)->update($update_plan);
	                    $update_monoline_settings["membership_id"]     = $check["membership_id"];
                   		$update_monoline_settings["max_price"]         = $check["max_price"];
                   		$update_monoline_settings["monoline_percent"]  = $check["monoline_percent"];


	                    Tbl_mlm_monoline_settings::where("membership_id",$check["membership_id"])->update($update_monoline_settings);
	                    $return["status"]         = "success";
	                    $return["update_status"]  = $update_plan["mlm_plan_enable"];
	                    $return["status_code"]    = 201;
	                    $return["status_message"] = "Settings updated...";
              		}
              		elseif ($value["monoline_settings"]["max_price"] < 0)
              		{
              			Plan::update_label($plan,$label);
	                    $update_plan["mlm_plan_enable"] = 1;
	                    Tbl_mlm_plan::where("mlm_plan_code",$plan)->update($update_plan);
	                    $update_monoline_settings["membership_id"]     = $check["membership_id"];
                   		$update_monoline_settings["max_price"]         = $check["max_price"];
                   		$update_monoline_settings["monoline_percent"]  = $check["monoline_percent"];


	                    Tbl_mlm_monoline_settings::where("membership_id",$check["membership_id"])->update($update_monoline_settings);
	                    $return["status"]         = "success";
	                    $return["update_status"]  = $update_plan["mlm_plan_enable"];
	                    $return["status_code"]    = 201;
	                    $return["status_message"] = "Settings updated...";
              		}
              		elseif ($value["monoline_settings"]["monoline_percent"] < 0 && $value["monoline_settings"]["max_price"] < 0 )
              		{
              			Plan::update_label($plan,$label);
	                    $update_plan["mlm_plan_enable"] = 1;
	                    Tbl_mlm_plan::where("mlm_plan_code",$plan)->update($update_plan);
	                    $update_monoline_settings["membership_id"]     = $check["membership_id"];
                   		$update_monoline_settings["max_price"]         = $check["max_price"];
                   		$update_monoline_settings["monoline_percent"]  = $check["monoline_percent"];


	                    Tbl_mlm_monoline_settings::where("membership_id",$check["membership_id"])->update($update_monoline_settings);
	                    $return["status"]         = "success";
	                    $return["update_status"]  = $update_plan["mlm_plan_enable"];
	                    $return["status_code"]    = 201;
	                    $return["status_message"] = "Settings updated...";
              		}
              		else
              		{
	                    Plan::update_label($plan,$label);
	                    $update_plan["mlm_plan_enable"] = 1;
	                    Tbl_mlm_plan::where("mlm_plan_code",$plan)->update($update_plan);
	                    $update_monoline_settings["membership_id"]     = $value["membership_id"];
	                    $update_monoline_settings["max_price"]         = $value["monoline_settings"]["max_price"];
	                    $update_monoline_settings["monoline_percent"]  = $value["monoline_settings"]["monoline_percent"];


	                    Tbl_mlm_monoline_settings::where("membership_id",$value["membership_id"])->update($update_monoline_settings);
	                    $return["status"]         = "success";
	                    $return["update_status"]  = $update_plan["mlm_plan_enable"];
	                    $return["status_code"]    = 201;
	                    $return["status_message"] = "Settings updated...";
              		}
                }


			}


		}
		$new_value = Get_plan::MONOLINE();
		Audit_trail::audit(serialize($old_value),serialize($new_value),$user,$action);

        return $return;
    }
    public static function PASS_UP($plan,$label,$data)
    {

		$data = json_decode($data,true);
		//dd($data);
		$user      = Request::user()->id;
		$action    = "Update Pass Up";
		$old_value = Get_plan::PASS_UP();
    	if($data != null)
        {

            // dd($data);
            foreach($data as $key => $value)
            {
            // dd($value);
            	$int  = (int)$value["pass_up_settings"]["pass_up_direction"];
            	$int2 = (int)$value["pass_up_settings"]["direct_direction"];
            	//dd($int2);
                $check =Tbl_mlm_pass_up_settings::where("membership_id",$value["membership_id"])->first();
 				//dd((int)$check["pass_up_settings"]["pass_up"]);
                if (!$check)
                {
              		if ($value["pass_up_settings"]["pass_up_direction"] == 1 && $value["pass_up_settings"]["direct_direction"] == 0 && $value["pass_up_settings"]["pass_up"] < $value["pass_up_settings"]["direct"] && $value["pass_up_settings"]["direct_amount"] >= 0 && $value["pass_up_settings"]["pass_up_amount"] >= 0)
              		{
              			Plan::update_label($plan,$label);
	                    $update_plan["mlm_plan_enable"] = 1;
	                    Tbl_mlm_plan::where("mlm_plan_code",$plan)->update($update_plan);
	                    $insert_pass_up_settings["membership_id"]     		= $value["membership_id"];
	                    $insert_pass_up_settings["pass_up"]           		= $value["pass_up_settings"]["pass_up"];
	                    $insert_pass_up_settings["pass_up_direction"] 		= $int;
	                    $insert_pass_up_settings["pass_up_amount"]      	= $value["pass_up_settings"]["pass_up_amount"];
	                    $insert_pass_up_settings["direct"]  		  		= $value["pass_up_settings"]["direct"];
	                    $insert_pass_up_settings["direct_direction"]  		= $int2;
	                    $insert_pass_up_settings["direct_amount"]     	 	= $value["pass_up_settings"]["direct_amount"];

	                    Tbl_mlm_pass_up_settings::insert($insert_pass_up_settings);
	                    $return["status"]         = "success";
	                    $return["update_status"]  = $update_plan["mlm_plan_enable"];
	                    $return["status_code"]    = 201;
	                    $return["status_message"] = "Settings updated...";
              		}
              		elseif ($value["pass_up_settings"]["pass_up_direction"] == 0 && $value["pass_up_settings"]["direct_direction"] == 1 && $value["pass_up_settings"]["pass_up"] > $value["pass_up_settings"]["direct"] && $value["pass_up_settings"]["direct_amount"] >= 0  && $value["pass_up_settings"]["pass_up_amount"] >= 0)
              		{
              			Plan::update_label($plan,$label);
	                    $update_plan["mlm_plan_enable"] = 1;
	                    Tbl_mlm_plan::where("mlm_plan_code",$plan)->update($update_plan);
	                    $insert_pass_up_settings["membership_id"]     		= $value["membership_id"];
	                    $insert_pass_up_settings["pass_up"]           		= $value["pass_up_settings"]["pass_up"];
	                    $insert_pass_up_settings["pass_up_direction"] 		= $int;
	                    $insert_pass_up_settings["pass_up_amount"]      	= $value["pass_up_settings"]["pass_up_amount"];
	                    $insert_pass_up_settings["direct"]  		  		= $value["pass_up_settings"]["direct"];
	                    $insert_pass_up_settings["direct_direction"]  		= $int2;
	                    $insert_pass_up_settings["direct_amount"]     	 	= $value["pass_up_settings"]["direct_amount"];

	                    Tbl_mlm_pass_up_settings::insert($insert_pass_up_settings);
	                    $return["status"]         = "success";
	                    $return["update_status"]  = $update_plan["mlm_plan_enable"];
	                    $return["status_code"]    = 201;
	                    $return["status_message"] = "Settings updated...";
              		}
              		else
              		{
              			Plan::update_label($plan,$label);
	                    $update_plan["mlm_plan_enable"] = 1;
	                    Tbl_mlm_plan::where("mlm_plan_code",$plan)->update($update_plan);
	                	$insert_pass_up_settings["membership_id"]     		= $value["membership_id"];
	                    $insert_pass_up_settings["pass_up"]           		= 2;
	                    $insert_pass_up_settings["pass_up_direction"] 		= 1;
	                    $insert_pass_up_settings["pass_up_amount"]      	= 0;
	                    $insert_pass_up_settings["direct"]  		  		= 4;
	                    $insert_pass_up_settings["direct_direction"]  		= 0;
	                    $insert_pass_up_settings["direct_amount"]     	 	= 0;

	                    Tbl_mlm_pass_up_settings::insert($insert_pass_up_settings);
	                    $return["status"]         = "success";
	                    $return["update_status"]  = $update_plan["mlm_plan_enable"];
	                    $return["status_code"]    = 201;
	                    $return["status_message"] = "Settings updated...";
              		}

                }
                else
                {
                	if ($value["pass_up_settings"]["pass_up_direction"] == 1 && $value["pass_up_settings"]["direct_direction"] == 0 && $value["pass_up_settings"]["pass_up"] < $value["pass_up_settings"]["direct"] && $value["pass_up_settings"]["direct_amount"] >= 0 && $value["pass_up_settings"]["pass_up_amount"] >= 0)
              		{
              			Plan::update_label($plan,$label);
	                    $update_plan["mlm_plan_enable"] = 1;
	                    Tbl_mlm_plan::where("mlm_plan_code",$plan)->update($update_plan);
	                    $insert_pass_up_settings["membership_id"]     		= $value["membership_id"];
	                    $insert_pass_up_settings["pass_up"]           		= $value["pass_up_settings"]["pass_up"];
	                    $insert_pass_up_settings["pass_up_direction"] 		= $int;
	                    $insert_pass_up_settings["pass_up_amount"]      	= $value["pass_up_settings"]["pass_up_amount"];
	                    $insert_pass_up_settings["direct"]  		  		= $value["pass_up_settings"]["direct"];
	                    $insert_pass_up_settings["direct_direction"]  		= $int2;
	                    $insert_pass_up_settings["direct_amount"]     	 	= $value["pass_up_settings"]["direct_amount"];

	                    Tbl_mlm_pass_up_settings::where("membership_id",$value["membership_id"])->update($insert_pass_up_settings);
	                    $return["status"]         = "success";
	                    $return["update_status"]  = $update_plan["mlm_plan_enable"];
	                    $return["status_code"]    = 201;
	                    $return["status_message"] = "Settings updated...";
              		}
              		elseif ($value["pass_up_settings"]["pass_up_direction"] == 0 && $value["pass_up_settings"]["direct_direction"] == 1 && $value["pass_up_settings"]["pass_up"] > $value["pass_up_settings"]["direct"] && $value["pass_up_settings"]["direct_amount"] >= 0 && $value["pass_up_settings"]["pass_up_amount"] >= 0)
              		{
              			Plan::update_label($plan,$label);
	                    $update_plan["mlm_plan_enable"] = 1;
	                    Tbl_mlm_plan::where("mlm_plan_code",$plan)->update($update_plan);
	                    $insert_pass_up_settings["membership_id"]     		= $value["membership_id"];
	                    $insert_pass_up_settings["pass_up"]           		= $value["pass_up_settings"]["pass_up"];
	                    $insert_pass_up_settings["pass_up_direction"] 		= $int;
	                    $insert_pass_up_settings["pass_up_amount"]      	= $value["pass_up_settings"]["pass_up_amount"];
	                    $insert_pass_up_settings["direct"]  		  		= $value["pass_up_settings"]["direct"];
	                    $insert_pass_up_settings["direct_direction"]  		= $int2;
	                    $insert_pass_up_settings["direct_amount"]     	 	= $value["pass_up_settings"]["direct_amount"];

	                    Tbl_mlm_pass_up_settings::where("membership_id",$value["membership_id"])->update($insert_pass_up_settings);
	                    $return["status"]         = "success";
	                    $return["update_status"]  = $update_plan["mlm_plan_enable"];
	                    $return["status_code"]    = 201;
	                    $return["status_message"] = "Settings updated...";
              		}
              		else
              		{
              			Plan::update_label($plan,$label);
	                    $update_plan["mlm_plan_enable"] = 1;
	                    Tbl_mlm_plan::where("mlm_plan_code",$plan)->update($update_plan);
	                    // $insert_pass_up_settings["membership_id"]     		= $value["membership_id"];
	                    // $insert_pass_up_settings["pass_up"]           		= $check["pass_up_settings"]["pass_up"];
	                    // $insert_pass_up_settings["pass_up_direction"] 		= $check["pass_up_settings"]["pass_up_direction"];
	                    // $insert_pass_up_settings["pass_up_amount"]      	= $check["pass_up_settings"]["pass_up_amount"];
	                    // $insert_pass_up_settings["direct"]  		  		= $check["pass_up_settings"]["direct"];
	                    // $insert_pass_up_settings["direct_direction"]  		= $check["pass_up_settings"]["direct_direction"];
	                    // $insert_pass_up_settings["direct_amount"]     	 	= $check["pass_up_settings"]["direct_amount"];

	                    // Tbl_mlm_pass_up_settings::where("membership_id",$value["membership_id"])->update($insert_pass_up_settings);
	                    $return["status"]         = "success";
	                    $return["update_status"]  = $update_plan["mlm_plan_enable"];
	                    $return["status_code"]    = 201;
	                    $return["status_message"] = "Settings updated...";
              		}
                }

			}

        }
		$new_value = Get_plan::PASS_UP();
		Audit_trail::audit(serialize($old_value),serialize($new_value),$user,$action);
        return $return;
    }
	public static function PASS_UP_COMBINATIONS($plan,$label,$data)
	{
		$data 		= json_decode($data,true);
		$user      	= Request::user()->id;
		$action    	= "Update PASS_UP_COMBINATIONS";
		$old_value 	= Get_plan::PASS_UP_COMBINATIONS();


		foreach($data["pass_up_settings"] as $key => $value)
		{
			foreach($value as $key2 => $value2)
			{
				$check = Tbl_pass_up_combination_income::where("membership_id",$key)->where("membership_entry_id",$key2)->first();
				if($check)
				{
					$update["pass_up_income"] 			= $value2;
					Tbl_pass_up_combination_income::where("membership_id",$key)->where("membership_entry_id",$key2)->update($update);
				}
				else
				{
					$insert["membership_id"]			= $key;
					$insert["membership_entry_id"]      = $key2;
					$insert["pass_up_income"] 			= $value2;
					Tbl_pass_up_combination_income::insert($insert);
				}
			}
		}
		foreach($data["pass_up_settings2"] as $key => $value)
		{
			foreach($value as $key2 => $value2)
			{
				$check = Tbl_pass_up_direct_combination_income::where("membership_id",$key)->where("membership_entry_id",$key2)->first();
				if($check)
				{
					$update2["pass_up_direct_income"] 		= $value2;
					Tbl_pass_up_direct_combination_income::where("membership_id",$key)->where("membership_entry_id",$key2)->update($update2);
				}
				else
				{
					$insert2["membership_id"]				= $key;
					$insert2["membership_entry_id"]      	= $key2;
					$insert2["pass_up_direct_income"] 		= $value2;
					Tbl_pass_up_direct_combination_income::insert($insert2);
				}
			}
		}
		$new_value = Get_plan::PASS_UP_COMBINATIONS();
		Audit_trail::audit(serialize($old_value),serialize($new_value),$user,$action);

	}
    public static function LEVELING_BONUS($plan,$label,$data)
	{
		$data = json_decode($data,true);
		$user      = Request::user()->id;
		$action    = "Update Leveling Bonus";
		$old_value = Get_plan::LEVELING_BONUS();
		if($data != null)
		{

			foreach($data["membership_settings"] as $key => $value)
			{
				$value["membership_leveling_bonus_level"] = $value["membership_leveling_bonus_level"];

				if(isset($data["leveling_bonus_settings"][$value["membership_id"]]))
				{
					$level = 1;
					foreach($data["leveling_bonus_settings"][$value["membership_id"]] as $level_target => $membership_leveling_bonus_income)
					{
						// if($level_target > 1)
						// {

							$check = Tbl_membership_leveling_bonus_level::where("membership_level",$level_target)->where("membership_id",$value["membership_id"])->where("membership_entry_id",$level_target)->first();
							if($check)
							{
								$update_level["membership_level"]		    	  = $level_target;
								$update_level["membership_id"]		        	  = $value["membership_id"];
								$update_level["membership_entry_id"]	    	  = $level_target;
								$update_level["membership_leveling_bonus_income"] = $membership_leveling_bonus_income;
								Tbl_membership_leveling_bonus_level::where("membership_level",$level_target)->where("membership_id",$value["membership_id"])->where("membership_entry_id",$level_target)->update($update_level);

							}
							else
							{
								$insert["membership_level"]		    		= $level_target;
								$insert["membership_id"]		     		= $value["membership_id"];
								$insert["membership_entry_id"]	    		= $level_target;
								$insert["membership_leveling_bonus_income"] = $membership_leveling_bonus_income;
								//$insert["membership_cashback_income"] = $membership_cashback_income;
								Tbl_membership_leveling_bonus_level::insert($insert);
							}
						// }

						$level++;
						if($level > $value["membership_leveling_bonus_level"])
						{
							Tbl_membership_leveling_bonus_level::where("membership_level",">=",$level)->where("membership_id",$value["membership_id"])->delete();
							break;
						}



					}

				}

				$update["membership_leveling_bonus_level"] = count(Tbl_membership_leveling_bonus_level::select("membership_level")->where("membership_id",$value["membership_id"])->groupBy("membership_level")->get());
				Tbl_membership::where("membership_id",$value["membership_id"])->update($update);
			}
		}

		Plan::update_label($plan,$label);
		$update_plan["mlm_plan_enable"] = 1;
		Tbl_mlm_plan::where("mlm_plan_code",$plan)->update($update_plan);

		$new_value = Get_plan::LEVELING_BONUS();
		Audit_trail::audit(serialize($old_value),serialize($new_value),$user,$action);

		$return["status"]         = "success";
		$return["update_status"]  = $update_plan["mlm_plan_enable"];
		$return["status_code"]    = 201;
		$return["status_message"] = "Settings updated...";

		return $return;

	}
	public static function UNILEVEL_OR($plan,$label,$data)
	{
		$data = json_decode($data,true);
		//dd($data);
		$user      = Request::user()->id;
		$action    = "Update Unilevel Or";
		$old_value = Get_plan::UNILEVEL_OR();
		if($data != null)
		{
			foreach($data["membership_settings"] as $key => $value)
			{
				$value["membership_unilevel_or_level"] = $value["membership_unilevel_or_level"];

				if(isset($data["unilevel_or_settings"][$value["membership_id"]]))
				{
					$level = 1;
					foreach($data["unilevel_or_settings"][$value["membership_id"]] as $level_target => $membership_percentage)
					{
						// if($level_target > 1)
						// {

							$check = Tbl_membership_unilevel_or_level::where("membership_level",$level_target)->where("membership_id",$value["membership_id"])->where("membership_entry_id",$level_target)->first();
							if($check)
							{
								$update_level["membership_level"]		    	  = $level_target;
								$update_level["membership_id"]		        	  = $value["membership_id"];
								$update_level["membership_entry_id"]	    	  = $level_target;
								$update_level["membership_percentage"] 			  = $membership_percentage;
								Tbl_membership_unilevel_or_level::where("membership_level",$level_target)->where("membership_id",$value["membership_id"])->where("membership_entry_id",$level_target)->update($update_level);

							}
							else
							{
								$insert["membership_level"]		    		= $level_target;
								$insert["membership_id"]		     		= $value["membership_id"];
								$insert["membership_entry_id"]	    		= $level_target;
								$insert["membership_percentage"]		    = $membership_percentage;
								Tbl_membership_unilevel_or_level::insert($insert);
							}
						// }

						$level++;
						if($level > $value["membership_unilevel_or_level"])
						{
							Tbl_membership_unilevel_or_level::where("membership_level",">=",$level)->where("membership_id",$value["membership_id"])->delete();
							break;
						}



					}

				}

				$update["membership_unilevel_or_level"] = count(Tbl_membership_unilevel_or_level::select("membership_level")->where("membership_id",$value["membership_id"])->groupBy("membership_level")->get());
				$update["membership_required_pv_or"]    = (int) $value["membership_required_pv_or"];
				Tbl_membership::where("membership_id",$value["membership_id"])->update($update);
			}
		}

		Plan::update_label($plan,$label);
		$update_plan["mlm_plan_enable"] = 1;
		Tbl_mlm_plan::where("mlm_plan_code",$plan)->update($update_plan);

		$new_value = Get_plan::UNILEVEL_OR();
		Audit_trail::audit(serialize($old_value),serialize($new_value),$user,$action);

		$return["status"]         = "success";
		$return["update_status"]  = $update_plan["mlm_plan_enable"];
		$return["status_code"]    = 201;
		$return["status_message"] = "Settings updated...";

		return $return;
	}
	public static function UNIVERSAL_POOL_BONUS($plan,$label,$data)
    {
		$data = json_decode($data,true);

		$user      = Request::user()->id;
		$action    = "Update Universal Pool Bonus";
		$old_value = Get_plan::UNIVERSAL_POOL_BONUS();
        if($data != null)
        {
            foreach($data['universal_pool_bonus_settings'] as $key => $value)
            {
				$check =Tbl_mlm_universal_pool_bonus_settings::where("membership_id",$value["membership_id"])->first();
				// dd($check);
                if (!$check)
                {
              		if ($value["percent"] <= 100 && $value["percent"] >= 0 && $value["max_price"] < 0)
              		{
              			Plan::update_label($plan,$label);
	                    $update_plan["mlm_plan_enable"] = 1;
	                    Tbl_mlm_plan::where("mlm_plan_code",$plan)->update($update_plan);
	                    $insert_universal_pool_bonus_settings["membership_id"]     = $value["membership_id"];
	                    $insert_universal_pool_bonus_settings["max_price"]         = 0;
	                    $insert_universal_pool_bonus_settings["percent"]		   = 0;
	                    $insert_universal_pool_bonus_settings["required_direct"]   = 0;


	                    Tbl_mlm_universal_pool_bonus_settings::insert($insert_universal_pool_bonus_settings);
	                    $return["status"]         = "success";
	                    $return["update_status"]  = $update_plan["mlm_plan_enable"];
	                    $return["status_code"]    = 201;
	                    $return["status_message"] = "Settings updated...";
              		}
              		elseif ($value["max_price"] < 0 )
              		{
              			Plan::update_label($plan,$label);
	                    $update_plan["mlm_plan_enable"] = 1;
	                    Tbl_mlm_plan::where("mlm_plan_code",$plan)->update($update_plan);
	                    $insert_universal_pool_bonus_settings["membership_id"]     = $value["membership_id"];
	                    $insert_universal_pool_bonus_settings["max_price"]         = 0;
						$insert_universal_pool_bonus_settings["percent"]		   = 0;
	                    $insert_universal_pool_bonus_settings["required_direct"]   = 0;						


	                    Tbl_mlm_universal_pool_bonus_settings::insert($insert_universal_pool_bonus_settings);
	                    $return["status"]         = "success";
	                    $return["update_status"]  = $update_plan["mlm_plan_enable"];
	                    $return["status_code"]    = 201;
	                    $return["status_message"] = "Settings updated...";
              		}
              		elseif ($value["percent"] < 0 && $value["max_price"] < 0 )
              		{
              			Plan::update_label($plan,$label);
	                    $update_plan["mlm_plan_enable"] = 1;
	                    Tbl_mlm_plan::where("mlm_plan_code",$plan)->update($update_plan);
	                    $insert_universal_pool_bonus_settings["membership_id"]     = $value["membership_id"];
	                    $insert_universal_pool_bonus_settings["max_price"]         = 0;
	                    $insert_universal_pool_bonus_settings["percent"]		   = 0;
	                    $insert_universal_pool_bonus_settings["required_direct"]   = 0;


	                    Tbl_mlm_universal_pool_bonus_settings::insert($insert_universal_pool_bonus_settings);
	                    $return["status"]         = "success";
	                    $return["update_status"]  = $update_plan["mlm_plan_enable"];
	                    $return["status_code"]    = 201;
	                    $return["status_message"] = "Settings updated...";
              		}
              		else
              		{
		                Plan::update_label($plan,$label);
	                    $update_plan["mlm_plan_enable"] = 1;
	                    Tbl_mlm_plan::where("mlm_plan_code",$plan)->update($update_plan);
	                    $insert_universal_pool_bonus_settings["membership_id"]     = $value["membership_id"];
	                    $insert_universal_pool_bonus_settings["max_price"]         = $value["max_price"];
	                    $insert_universal_pool_bonus_settings["percent"]		   = $value["percent"];
	                    $insert_universal_pool_bonus_settings["required_direct"]   = $value["required_direct"];


	                    Tbl_mlm_universal_pool_bonus_settings::insert($insert_universal_pool_bonus_settings);
	                    $return["status"]         = "success";
	                    $return["update_status"]  = $update_plan["mlm_plan_enable"];
	                    $return["status_code"]    = 201;
	                    $return["status_message"] = "Settings updated...";
              		}
                }
                else
                {
                	if ($value["percent"] > 100 || $value["percent"] < 0)
                	{
              			Plan::update_label($plan,$label);
	                    $update_plan["mlm_plan_enable"] = 1;
	                    Tbl_mlm_plan::where("mlm_plan_code",$plan)->update($update_plan);
	                    $update_universal_pool_bonus_settings["membership_id"]     = $check["membership_id"];
                   		$update_universal_pool_bonus_settings["max_price"]         = $check["max_price"];
                   		$update_universal_pool_bonus_settings["percent"] 		   = $check["percent"];
						$update_universal_pool_bonus_settings["required_direct"]   = $value["required_direct"];


	                    Tbl_mlm_universal_pool_bonus_settings::where("membership_id",$check["membership_id"])->update($update_universal_pool_bonus_settings);
	                    $return["status"]         = "success";
	                    $return["update_status"]  = $update_plan["mlm_plan_enable"];
	                    $return["status_code"]    = 201;
	                    $return["status_message"] = "Settings updated...";
              		}
              		elseif ($value["max_price"] < 0)
              		{
              			Plan::update_label($plan,$label);
	                    $update_plan["mlm_plan_enable"] = 1;
	                    Tbl_mlm_plan::where("mlm_plan_code",$plan)->update($update_plan);
	                    $update_universal_pool_bonus_settings["membership_id"]     = $check["membership_id"];
                   		$update_universal_pool_bonus_settings["max_price"]         = $check["max_price"];
                   		$update_universal_pool_bonus_settings["percent"] 	       = $check["percent"];
                   		$update_universal_pool_bonus_settings["required_direct"]   = $check["required_direct"];


	                    Tbl_mlm_universal_pool_bonus_settings::where("membership_id",$check["membership_id"])->update($update_universal_pool_bonus_settings);
	                    $return["status"]         = "success";
	                    $return["update_status"]  = $update_plan["mlm_plan_enable"];
	                    $return["status_code"]    = 201;
	                    $return["status_message"] = "Settings updated...";
              		}
              		elseif ($value["percent"] < 0 && $value["max_price"] < 0 )
              		{
              			Plan::update_label($plan,$label);
	                    $update_plan["mlm_plan_enable"] = 1;
	                    Tbl_mlm_plan::where("mlm_plan_code",$plan)->update($update_plan);
	                    $update_universal_pool_bonus_settings["membership_id"]     = $check["membership_id"];
                   		$update_universal_pool_bonus_settings["max_price"]         = $check["max_price"];
                   		$update_universal_pool_bonus_settings["percent"]           = $check["percent"];
                   		$update_universal_pool_bonus_settings["required_direct"]   = $check["required_direct"];


	                    Tbl_mlm_universal_pool_bonus_settings::where("membership_id",$check["membership_id"])->update($update_universal_pool_bonus_settings);
	                    $return["status"]         = "success";
	                    $return["update_status"]  = $update_plan["mlm_plan_enable"];
	                    $return["status_code"]    = 201;
	                    $return["status_message"] = "Settings updated...";
              		}
              		else
              		{
	                    Plan::update_label($plan,$label);
	                    $update_plan["mlm_plan_enable"] = 1;
	                    Tbl_mlm_plan::where("mlm_plan_code",$plan)->update($update_plan);
	                    $update_universal_pool_bonus_settings["membership_id"]     = $value["membership_id"];
	                    $update_universal_pool_bonus_settings["max_price"]         = $value["max_price"];
	                    $update_universal_pool_bonus_settings["percent"]  		   = $value["percent"];
						$update_universal_pool_bonus_settings["required_direct"]   = $check["required_direct"];


	                    Tbl_mlm_universal_pool_bonus_settings::where("membership_id",$value["membership_id"])->update($update_universal_pool_bonus_settings);
	                    $return["status"]         = "success";
	                    $return["update_status"]  = $update_plan["mlm_plan_enable"];
	                    $return["status_code"]    = 201;
	                    $return["status_message"] = "Settings updated...";
              		}
				}

            $update_universal_maintain_settings['required_direct'] 		= $data['maintain_settings']['required_direct'] > 0 ? $data['maintain_settings']['required_direct'] : 0;
			$update_universal_maintain_settings['maintain_date']   		= $data['maintain_settings']['maintain_date'] != "" ? $data['maintain_settings']['maintain_date'] : null;
			$update_universal_maintain_settings['binary_maintenace']   	= $data['maintain_settings']['binary_maintenace'];
			Tbl_mlm_universal_pool_bonus_maintain_settings::where("universal_pool_settings_id",$data['maintain_settings']['universal_pool_settings_id'])->update($update_universal_maintain_settings);

            }
		}
		$new_value = Get_plan::UNIVERSAL_POOL_BONUS();
		Audit_trail::audit(serialize($old_value),serialize($new_value),$user,$action);

        return $return;
	}
	public static function INCENTIVE_BONUS($plan,$label,$data)
    {
		$user      = Request::user()->id;
		$action    = "Update Incentive Bonus";
		$old_value = Get_plan::INCENTIVE_BONUS();

		$data = json_decode($data,true);
		$incentives_bonus_id 		  = Tbl_mlm_incentive_bonus::first()->incentives_bonus_id;
		$update["incentives_status"]  = (int)$data;
		Tbl_mlm_incentive_bonus::where("incentives_bonus_id",$incentives_bonus_id)->update($update);

		Plan::update_label($plan,$label);
	    $update_plan["mlm_plan_enable"] = 1;
		Tbl_mlm_plan::where("mlm_plan_code",$plan)->update($update_plan);

		$new_value = Get_plan::INCENTIVE_BONUS();
		Audit_trail::audit(serialize($old_value),serialize($new_value),$user,$action);
		$return["status"]         = "success";
	    $return["update_status"]  = $update_plan["mlm_plan_enable"];
	    $return["status_code"]    = 201;
		$return["status_message"] = "Settings updated...";

		return $return;
	}
    public static function BINARY_REPURCHASE($plan,$label,$data)
	{
		$data = json_decode($data,true);
		$user      = Request::user()->id;
		$action    = "Update Binary Repurchase";
		$old_value = Get_plan::BINARY_REPURCHASE();

		// if($data != null)
		// {

		// }

		Plan::update_label($plan,$label);
		$update_plan["mlm_plan_enable"] = 1;
		Tbl_mlm_plan::where("mlm_plan_code",$plan)->update($update_plan);

		$new_value = Get_plan::BINARY_REPURCHASE();
		Audit_trail::audit(serialize($old_value),serialize($new_value),$user,$action);
		$return["status"]         = "success";
		$return["update_status"]  = $update_plan["mlm_plan_enable"];
		$return["status_code"]    = 201;
		$return["status_message"] = "Settings updated...";

		return $return;

	}
	public static function GLOBAL_POOL_BONUS($plan,$label,$data)
	{
		$data = json_decode($data,true);
		$user      = Request::user()->id;
		$action    = "Update Global Pool Bonus";
		$old_value = Get_plan::GLOBAL_POOL_BONUS();

		foreach ($data['membership_settings'] as $key => $value) 
		{
			if(isset($data['membership'][$key]))
			{	
			}
			else 
			{
				$data['membership'][$key] = 0 ;
			}
			Tbl_membership::where("membership_id",$value['membership_id'])->update(['global_pool_enabled'=>$data['membership'][$key]['global_pool_enabled'],'global_pool_pv'=>$data['membership'][$key]['global_pool_pv']]);
		}
		if($data["amount"] != 0 || $data["amount"] != "")
		{
			$first = Tbl_global_pool_bonus_settings::first() ? Tbl_global_pool_bonus_settings::first()->global_pool_bonus_id : null;
			if($first)
			{
				Tbl_global_pool_bonus_settings::where("global_pool_bonus_id",$first)->update(["global_pool_amount"=>$data["amount"]]);
			}
		}
		Plan::update_label($plan,$label);
		$update_plan["mlm_plan_enable"] = 1;
		Tbl_mlm_plan::where("mlm_plan_code",$plan)->update($update_plan);

		$new_value = Get_plan::GLOBAL_POOL_BONUS();
		Audit_trail::audit(serialize($old_value),serialize($new_value),$user,$action);
		$return["status"]         = "success";
		$return["update_status"]  = $update_plan["mlm_plan_enable"];
		$return["status_code"]    = 201;
		$return["status_message"] = "Settings updated...";

		return $return;

	}

	public static function SHARE_LINK($plan,$label,$data)
	{
		$data = json_decode($data,true);
		$user      = Request::user()->id;
		$action    = "Update  Share Link";
		$old_value = Get_plan::SHARE_LINK();
		$update['share_link_maximum_income'] = $data['share_link_maximum_income'];
		$update['share_link_income_per_registration'] = $data['share_link_income_per_registration'];
		$update['share_link_maximum_register_per_day'] = $data['share_link_maximum_register_per_day'];

		Tbl_share_link_settings::where("share_link_settings_id",$data['share_link_settings_id'])->update($update);
		
		Plan::update_label($plan,$label);
		$update_plan["mlm_plan_enable"] = 1;
		Tbl_mlm_plan::where("mlm_plan_code",$plan)->update($update_plan);

		$new_value = Get_plan::SHARE_LINK();
		Audit_trail::audit(serialize($old_value),serialize($new_value),$user,$action);
		$return["status"]         = "success";
		$return["update_status"]  = $update_plan["mlm_plan_enable"];
		$return["status_code"]    = 201;
		$return["status_message"] = "Settings updated...";

		return $return;
	}

	public static function WATCH_EARN($plan,$label,$data)
	{
		$data = json_decode($data,true);
		$user      = Request::user()->id;
		$action    = "Update  Watch and earn";
		$old_value = Get_plan::WATCH_EARN();

		$update['watch_earn_maximum_amount'] = $data['settings']['watch_earn_maximum_amount'] != '' ? $data['settings']['watch_earn_maximum_amount'] : 0 ;
		$update['watch_earn_video_amount']   = $data['settings']['watch_earn_video_amount'] != '' ? $data['settings']['watch_earn_video_amount'] : 0;
		$update['watch_earn_video_max']      = $data['settings']['watch_earn_video_max'] != '' ? $data['settings']['watch_earn_video_max'] : 0;

		Tbl_watch_earn_settings::where("watch_earn_settings_id",$data['settings']['watch_earn_settings_id'])->update($update);	
		
		Plan::update_label($plan,$label);
		$update_plan["mlm_plan_enable"] = 1;
		Tbl_mlm_plan::where("mlm_plan_code",$plan)->update($update_plan);

		$new_value = Get_plan::WATCH_EARN();
		Audit_trail::audit(serialize($old_value),serialize($new_value),$user,$action);
		$return["status"]         = "success";
		$return["update_status"]  = $update_plan["mlm_plan_enable"];
		$return["status_code"]    = 201;
		$return["status_message"] = "Settings updated...";

		return $return;
	}
	public static function PASSIVE_UNILEVEL_PREMIUM($plan,$label,$data)
    {
    	$data = json_decode($data,true);
		//dd($data);
		$user      = Request::user()->id;
		$action    = "Update Passive Unilevel Premium";
		$old_value = Get_plan::PASSIVE_UNILEVEL_PREMIUM();
    	if($data != null) {
            foreach($data as $key => $value) {
				$check = Tbl_passive_unilevel_premium::where('premium_membership_id',$value['membership_id'])->first();
				if($check) {
					$update['premium_upline'] = $value['premium_settings']['premium_upline'] != null ? $value['premium_settings']['premium_upline'] : 0 ;
					$update['premium_downline'] = $value['premium_settings']['premium_downline'] != null ? $value['premium_settings']['premium_downline'] : 0 ;
					$update['premium_percentage'] = $value['premium_settings']['premium_percentage'] != null ? $value['premium_settings']['premium_percentage'] : 0 ;
					$update['premium_is_enable'] = $value['premium_settings']['premium_is_enable'] ;
					$update['premium_earning_limit'] = $value['premium_settings']['premium_earning_limit'] != null ? $value['premium_settings']['premium_earning_limit'] : 0 ;
					$update['premium_earning_cycle'] = $value['premium_settings']['premium_earning_cycle'] ;
					Tbl_passive_unilevel_premium::where('premium_membership_id',$check->premium_membership_id)->update($update);
				}
				else {
					
					$insert['premium_membership_id'] = $value['membership_id'];
					$insert['premium_upline'] = $value['premium_settings']['premium_upline'] != null ? $value['premium_settings']['premium_upline'] : 0 ;
					$insert['premium_downline'] = $value['premium_settings']['premium_downline'] != null ? $value['premium_settings']['premium_downline'] : 0 ;
					$insert['premium_percentage'] = $value['premium_settings']['premium_percentage'] != null ? $value['premium_settings']['premium_percentage'] : 0 ;
					$insert['premium_is_enable'] = $value['premium_settings']['premium_is_enable'] ;
					$insert['premium_earning_limit'] = $value['premium_settings']['premium_earning_limit'] != null ? $value['premium_settings']['premium_earning_limit'] : 0 ;
					$insert['premium_earning_cycle'] = $value['premium_settings']['premium_earning_cycle'] ;
					$insert['premium_date_created'] = Carbon::now() ;
					Tbl_passive_unilevel_premium::insert($insert);
				}
			}
		}
		Plan::update_label($plan,$label);
		$update_plan["mlm_plan_enable"] = 1;
		Tbl_mlm_plan::where("mlm_plan_code",$plan)->update($update_plan);
		$new_value = Get_plan::PASSIVE_UNILEVEL_PREMIUM();
		Audit_trail::audit(serialize($old_value),serialize($new_value),$user,$action);
		$return["status"]         = "success";
		$return["update_status"]  = $update_plan["mlm_plan_enable"];
		$return["status_code"]    = 201;
		$return["status_message"] = "Settings updated...";
        return $return;
	}
	public static function PRODUCT_SHARE_LINK($plan,$label,$data)
	{

		$data 		= json_decode($data,true);
		$user      	= Request::user()->id;
		$action    	= "Update Product Share Link";
		$old_value 	= Get_plan::PRODUCT_SHARE_LINK();
		$times 		= 0;

		if($data != null)
		{
			foreach($data["membership_settings"] as $key => $value)
			{
				// dd($value, $data["product_share_link_settings"]);
				$value["membership_product_share_link_level"] = $value["membership_product_share_link_level"];
				if(isset($data["product_share_link_settings"][$value["membership_id"]]))
				{
					/* GET THE DATA SETTINGS PER MEMBERSHIP */
					foreach($data["product_share_link_settings"][$value["membership_id"]] as $membership_entry_id => $per_membership)
					{
						$level = 1;
						/* GET THE DATA SETTINGS PER LEVEL OF TARGET MEMBERSHIP */
						foreach($per_membership as $level_target => $membership_product_income)
						{
							/* membership_entry_id  = membership_entry_id */
							/* membership_product_income = membership_product_income*/
							$check = Tbl_membership_product_level::where("membership_level",$level)->where("membership_id",$value["membership_id"])->where("membership_entry_id",$membership_entry_id)->first();
							if($check)
							{
								$update_level["membership_level"]		    = $level;
								$update_level["membership_id"]		        = $value["membership_id"];
								$update_level["membership_entry_id"]	    = $membership_entry_id;
								$update_level["membership_product_income"] = $membership_product_income;
								Tbl_membership_product_level::where("membership_level",$level)->where("membership_id",$value["membership_id"])->where("membership_entry_id",$membership_entry_id)->update($update_level);
							}
							else
							{
								$insert["membership_level"]		      = $level;
								$insert["membership_id"]		      = $value["membership_id"];
								$insert["membership_entry_id"]	      = $membership_entry_id;
								$insert["membership_product_income"]  = $membership_product_income;
								Tbl_membership_product_level::insert($insert);
							}

							if($level > $value["membership_product_share_link_level"])
							{
								Tbl_membership_product_level::where("membership_level",">=",$level)->where("membership_id",$value["membership_id"])->delete();
								break;
							}
							$level++;
						}

					}
				}

				$update["membership_product_share_link_level"] = count(Tbl_membership_product_level::select("membership_level")->where("membership_id",$value["membership_id"])->groupBy("membership_level")->get());

				// dd($update["membership_product_share_link_level"]);
				Tbl_membership::where("membership_id",$value["membership_id"])->update($update);
			}
			// $update_indirect['indirect_points_enable'] = $data['indirect_points_settings']['indirect_points_enable'] != null ? $data['indirect_points_settings']['indirect_points_enable'] : 0;
			// $update_indirect['indirect_points_minimum_conversion'] = $data['indirect_points_settings']['indirect_points_minimum_conversion'] != null ? $data['indirect_points_settings']['indirect_points_minimum_conversion'] : 0;
			// Tbl_indirect_settings::where('indirect_settings_id',$data['indirect_points_settings']['indirect_settings_id'])->update($update_indirect);
		}

		Plan::update_label($plan,$label);
		$update_plan["mlm_plan_enable"] = 1;
		Tbl_mlm_plan::where("mlm_plan_code",$plan)->update($update_plan);

		$new_value = Get_plan::PRODUCT_SHARE_LINK();
		Audit_trail::audit(serialize($old_value),serialize($new_value),$user,$action);

		$return["status"]         = "success";
		$return["update_status"]  = $update_plan["mlm_plan_enable"];
		$return["status_code"]    = 201;
		$return["status_message"] = "Settings updated...";

		return $return;
	}	
	public static function OVERRIDING_COMMISSION($plan,$label,$data)
	{

		$data 		= json_decode($data,true);

		$user      	= Request::user()->id;
		$action    	= "Update Overriding Commission";
		$old_value 	= Get_plan::OVERRIDING_COMMISSION();
		$times 		= 0;

		if($data != null)
		{
			foreach($data["membership_settings"] as $key => $value)
			{
				// dd($value, $data["product_share_link_settings"]);
				$value["membership_overriding_commission_level"] = $value["membership_overriding_commission_level"];
				if(isset($data["overriding_commission_settings"][$value["membership_id"]]))
				{
					/* GET THE DATA SETTINGS PER MEMBERSHIP */
					foreach($data["overriding_commission_settings"][$value["membership_id"]] as $membership_entry_id => $per_membership)
					{
						$level = 1;
						/* GET THE DATA SETTINGS PER LEVEL OF TARGET MEMBERSHIP */
						foreach($per_membership as $level_target => $membership_overriding_commission_income)
						{
							/* membership_entry_id  = membership_entry_id */
							/* membership_overriding_commission_income = membership_overriding_commission_income*/
							$check = Tbl_membership_overriding_commission_level::where("membership_level",$level)->where("membership_id",$value["membership_id"])->where("membership_entry_id",$membership_entry_id)->first();
							if($check)
							{
								$update_level["membership_level"]		    				= $level;
								$update_level["membership_id"]		        				= $value["membership_id"];
								$update_level["membership_entry_id"]	    				= $membership_entry_id;
								$update_level["membership_overriding_commission_income"] 	= $membership_overriding_commission_income;
								Tbl_membership_overriding_commission_level::where("membership_level",$level)->where("membership_id",$value["membership_id"])->where("membership_entry_id",$membership_entry_id)->update($update_level);
							}
							else
							{
								$insert["membership_level"]		      						= $level;
								$insert["membership_id"]		      						= $value["membership_id"];
								$insert["membership_entry_id"]	      						= $membership_entry_id;
								$insert["membership_overriding_commission_income"] 			= $membership_overriding_commission_income;
								Tbl_membership_overriding_commission_level::insert($insert);
							}

							if($level > $value["membership_overriding_commission_level"])
							{
								Tbl_membership_overriding_commission_level::where("membership_level",">=",$level)->where("membership_id",$value["membership_id"])->delete();
								break;
							}
							$level++;
						}

					}
				}

				$update["membership_overriding_commission_level"] = count(Tbl_membership_overriding_commission_level::select("membership_level")->where("membership_id",$value["membership_id"])->groupBy("membership_level")->get());
				Tbl_membership::where("membership_id",$value["membership_id"])->update($update);
			}
		}

		Plan::update_label($plan,$label);
		$update_plan["mlm_plan_enable"] = 1;
		Tbl_mlm_plan::where("mlm_plan_code",$plan)->update($update_plan);

		$new_value = Get_plan::OVERRIDING_COMMISSION();
		Audit_trail::audit(serialize($old_value),serialize($new_value),$user,$action);

		$return["status"]         = "success";
		$return["update_status"]  = $update_plan["mlm_plan_enable"];
		$return["status_code"]    = 201;
		$return["status_message"] = "Settings updated...";

		return $return;
	}
	public static function PRODUCT_DIRECT_REFERRAL($plan,$label,$data)
	{
		$data 		= json_decode($data,true);
		$user      	= Request::user()->id;
		$action    	= "Update Product Direct Referral";
		$old_value 	= Get_plan::PRODUCT_DIRECT_REFERRAL();


		Plan::update_label($plan,$label);

		$update_plan["mlm_plan_enable"] = 1;
		Tbl_mlm_plan::where("mlm_plan_code",$plan)->update($update_plan);
		
		
		$new_value = Get_plan::PRODUCT_DIRECT_REFERRAL();
		Audit_trail::audit(serialize($old_value),serialize($new_value),$user,$action);

		Tbl_wallet_log::where('wallet_log_details', $old_value['label'])->update(['wallet_log_details' => $new_value['label']]);
		
		$return["status"]         = "success";
		$return["update_status"]  = $update_plan["mlm_plan_enable"];
		$return["status_code"]    = 201;
		$return["status_message"] = "Settings updated...";

		return $return;

	}	

	public static function PRODUCT_PERSONAL_CASHBACK($plan,$label,$data)
	{
		$data 		= json_decode($data,true);
		$user      	= Request::user()->id;
		$action    	= "Update Product Personal Cashback";
		$old_value 	= Get_plan::PRODUCT_PERSONAL_CASHBACK();


		Plan::update_label($plan,$label);

		$update_plan["mlm_plan_enable"] = 1;
		Tbl_mlm_plan::where("mlm_plan_code",$plan)->update($update_plan);
		
		
		$new_value = Get_plan::PRODUCT_PERSONAL_CASHBACK();
		Audit_trail::audit(serialize($old_value),serialize($new_value),$user,$action);

		Tbl_wallet_log::where('wallet_log_details', $old_value['label'])->update(['wallet_log_details' => $new_value['label']]);
		
		$return["status"]         = "success";
		$return["update_status"]  = $update_plan["mlm_plan_enable"];
		$return["status_code"]    = 201;
		$return["status_message"] = "Settings updated...";

		return $return;

	}	
	public static function PRODUCT_DOWNLINE_DISCOUNT($plan,$label,$data)
	{
		$data 		= json_decode($data,true);
		$user      	= Request::user()->id;
		$action    	= "Update Product Downline Discount";
		$old_value 	= Get_plan::PRODUCT_DOWNLINE_DISCOUNT();


		Plan::update_label($plan,$label);

		$update_plan["mlm_plan_enable"] = 1;
		Tbl_mlm_plan::where("mlm_plan_code",$plan)->update($update_plan);
		
		
		$new_value = Get_plan::PRODUCT_DOWNLINE_DISCOUNT();
		Audit_trail::audit(serialize($old_value),serialize($new_value),$user,$action);

		Tbl_wallet_log::where('wallet_log_details', $old_value['label'])->update(['wallet_log_details' => $new_value['label']]);
		
		$return["status"]         = "success";
		$return["update_status"]  = $update_plan["mlm_plan_enable"];
		$return["status_code"]    = 201;
		$return["status_message"] = "Settings updated...";

		return $return;

	}
	public static function REFERRAL_VOUCHER($plan,$label,$data)
    {
        $data 															= json_decode($data,true);
		$user     														= Request::user()->id;
		$action   														= "Update Referral Voucher";
		$old_value														= Get_plan::REFERRAL_VOUCHER();
        if($data != null)					
        {					
            foreach($data as $key => $value)					
            {					
                $check 													= Tbl_referral_voucher_settings::where("membership_id",$value["membership_id"])->first();
                if (!$check)
                {

					$insert['membership_id']							= $value['membership_id'];	
					$insert['referrer_income']							= $value['referral_voucher_settings']['referrer_income'];
					$insert['referee_income']							= $value['referral_voucher_settings']['referee_income'];
					
					Tbl_referral_voucher_settings::insert($insert);
				}
				else
				{
					$update['membership_id']							= $value['membership_id'];	
					$update['referrer_income']							= $value['referral_voucher_settings']['referrer_income'];
					$update['referee_income']							= $value['referral_voucher_settings']['referee_income'];
					
					Tbl_referral_voucher_settings::where('membership_id',$value['membership_id'])->update($update);
				}

			}

		}
		$new_value 														= Get_plan::REFERRAL_VOUCHER();
		Audit_trail::audit(serialize($old_value),serialize($new_value),$user,$action);
		
		Plan::update_label($plan,$label);

		$update_plan["mlm_plan_enable"] 								= 1;
		Tbl_mlm_plan::where("mlm_plan_code",$plan)->update($update_plan);

		$return["status"]        										= "success";
		$return["update_status"] 										= $update_plan["mlm_plan_enable"];
		$return["status_code"]   										= 201;
		$return["status_message"]										= "Settings updated...";

        return $return;
    }
	public static function OVERRIDING_COMMISSION_V2($plan,$label,$data)
	{
		$data 		= json_decode($data,true);
		$user      	= Request::user()->id;
		$action    	= "Update Overriding Commission v2";
		$old_value 	= Get_plan::OVERRIDING_COMMISSION_V2();
		if($data != null)
		{
			foreach($data["overriding_settings"] as $key => $value)
			{
				foreach($value as $key2 => $value2)
				{
					$check = Tbl_overriding_commission_v2::where("membership_id",$key)->where("membership_entry_id",$key2)->first();
					if($check)
					{
						$update["income"] 					= $value2;
						Tbl_overriding_commission_v2::where("membership_id",$key)->where("membership_entry_id",$key2)->update($update);
					}
					else
					{
						$insert["membership_id"]			= $key;
						$insert["membership_entry_id"]      = $key2;
						$insert["income"] 					= $value2;
						Tbl_overriding_commission_v2::insert($insert);
					}
				}
			}
		}

		Plan::update_label($plan,$label);
		$update_plan["mlm_plan_enable"] = 1;
		Tbl_mlm_plan::where("mlm_plan_code",$plan)->update($update_plan);

		$new_value = Get_plan::OVERRIDING_COMMISSION_V2();
		Audit_trail::audit(serialize($old_value),serialize($new_value),$user,$action);

		$return["direct"]         = "ok";
		$return["status"]         = "success";
		$return["update_status"]  = $update_plan["mlm_plan_enable"];
		$return["status_code"]    = 201;
		$return["status_message"] = "Settings updated...";

		return $return;
	}
	public static function TEAM_SALES_BONUS($plan,$label,$data,$trigger)
	{
		$data 		= json_decode($data,true);
		$user      	= Request::user()->id;
		$action    	= "Update Team Sales Bonus";
		$old_value 	= Get_plan::TEAM_SALES_BONUS();
		if($data != null)
		{
			foreach($data as $key => $value)
			{
				$update['team_sales_bonus_level'] = $value['team_sales_bonus_settings']['team_sales_bonus_level'];
				Tbl_membership::where("membership_id",$value['team_sales_bonus_settings']['membership_id'])->update($update);
			}
		}

		Plan::update_label($plan, $label);
		Plan::update_trigger($plan, $trigger);

		$update_plan["mlm_plan_enable"] = 1;
		Tbl_mlm_plan::where("mlm_plan_code",$plan)->update($update_plan);
		
		
		$new_value = Get_plan::TEAM_SALES_BONUS();
		Audit_trail::audit(serialize($old_value),serialize($new_value),$user,$action);

		Tbl_wallet_log::where('wallet_log_details', $old_value['label'])->update(['wallet_log_details' => $new_value['label']]);
		
		$return["status"]         = "success";
		$return["update_status"]  = $update_plan["mlm_plan_enable"];
		$return["status_code"]    = 201;
		$return["status_message"] = "Settings updated...";

		return $return;

	}	
	public static function OVERRIDING_BONUS($plan,$label,$data)
	{
		$data 		= json_decode($data,true);
		$user      	= Request::user()->id;
		$action    	= "Update Overriding Bonus";
		$old_value 	= Get_plan::OVERRIDING_BONUS();


		Plan::update_label($plan,$label);

		$update_plan["mlm_plan_enable"] = 1;
		Tbl_mlm_plan::where("mlm_plan_code",$plan)->update($update_plan);
		
		
		$new_value = Get_plan::OVERRIDING_BONUS();
		Audit_trail::audit(serialize($old_value),serialize($new_value),$user,$action);

		Tbl_wallet_log::where('wallet_log_details', $old_value['label'])->update(['wallet_log_details' => $new_value['label']]);
		
		$return["status"]         = "success";
		$return["update_status"]  = $update_plan["mlm_plan_enable"];
		$return["status_code"]    = 201;
		$return["status_message"] = "Settings updated...";

		return $return;

	}	
	public static function RETAILER_OVERRIDE($plan,$label,$data)
	{
		$data 		= json_decode($data,true);
		$user      	= Request::user()->id;
		$action    	= "Update Retailer Override";
		$old_value 	= Get_plan::RETAILER_OVERRIDE();


		Plan::update_label($plan,$label);

		$update_plan["mlm_plan_enable"] = 1;
		Tbl_mlm_plan::where("mlm_plan_code",$plan)->update($update_plan);
		
		
		$new_value = Get_plan::RETAILER_OVERRIDE();
		Audit_trail::audit(serialize($old_value),serialize($new_value),$user,$action);

		Tbl_wallet_log::where('wallet_log_details', $old_value['label'])->update(['wallet_log_details' => $new_value['label']]);
		
		$return["status"]         = "success";
		$return["update_status"]  = $update_plan["mlm_plan_enable"];
		$return["status_code"]    = 201;
		$return["status_message"] = "Settings updated...";

		return $return;
	}
	public static function REVERSE_PASS_UP($plan,$label,$data)
    {

    	$data = json_decode($data,true);
		//dd($data);
		$user      = Request::user()->id;
		$action    = "Update Pass Up";
		$old_value = Get_plan::REVERSE_PASS_UP();
    	if($data != null)
        {

            // dd($data);
            foreach($data as $key => $value)
            {
            // dd($value);
            	$int  = (int)$value["pass_up_settings"]["pass_up_direction"];
            	$int2 = (int)$value["pass_up_settings"]["direct_direction"];
            	//dd($int2);
                $check =Tbl_reverse_pass_up_settings::where("membership_id",$value["membership_id"])->first();
 				//dd((int)$check["pass_up_settings"]["pass_up"]);
                if (!$check)
                {
              		if ($value["pass_up_settings"]["pass_up_direction"] == 1 && $value["pass_up_settings"]["direct_direction"] == 0 && $value["pass_up_settings"]["pass_up"] < $value["pass_up_settings"]["direct"] && $value["pass_up_settings"]["direct_amount"] >= 0 && $value["pass_up_settings"]["pass_up_amount"] >= 0)
              		{
              			Plan::update_label($plan,$label);
	                    $update_plan["mlm_plan_enable"] = 1;
	                    Tbl_mlm_plan::where("mlm_plan_code",$plan)->update($update_plan);
	                    $insert_pass_up_settings["membership_id"]     		= $value["membership_id"];
	                    $insert_pass_up_settings["pass_up"]           		= $value["pass_up_settings"]["pass_up"];
	                    $insert_pass_up_settings["pass_up_direction"] 		= $int;
	                    $insert_pass_up_settings["pass_up_amount"]      	= $value["pass_up_settings"]["pass_up_amount"];
	                    $insert_pass_up_settings["direct"]  		  		= $value["pass_up_settings"]["direct"];
	                    $insert_pass_up_settings["direct_direction"]  		= $int2;
	                    $insert_pass_up_settings["direct_amount"]     	 	= $value["pass_up_settings"]["direct_amount"];

	                    Tbl_reverse_pass_up_settings::insert($insert_pass_up_settings);
	                    $return["status"]         = "success";
	                    $return["update_status"]  = $update_plan["mlm_plan_enable"];
	                    $return["status_code"]    = 201;
	                    $return["status_message"] = "Settings updated...";
              		}
              		elseif ($value["pass_up_settings"]["pass_up_direction"] == 0 && $value["pass_up_settings"]["direct_direction"] == 1 && $value["pass_up_settings"]["pass_up"] > $value["pass_up_settings"]["direct"] && $value["pass_up_settings"]["direct_amount"] >= 0  && $value["pass_up_settings"]["pass_up_amount"] >= 0)
              		{
              			Plan::update_label($plan,$label);
	                    $update_plan["mlm_plan_enable"] = 1;
	                    Tbl_mlm_plan::where("mlm_plan_code",$plan)->update($update_plan);
	                    $insert_pass_up_settings["membership_id"]     		= $value["membership_id"];
	                    $insert_pass_up_settings["pass_up"]           		= $value["pass_up_settings"]["pass_up"];
	                    $insert_pass_up_settings["pass_up_direction"] 		= $int;
	                    $insert_pass_up_settings["pass_up_amount"]      	= $value["pass_up_settings"]["pass_up_amount"];
	                    $insert_pass_up_settings["direct"]  		  		= $value["pass_up_settings"]["direct"];
	                    $insert_pass_up_settings["direct_direction"]  		= $int2;
	                    $insert_pass_up_settings["direct_amount"]     	 	= $value["pass_up_settings"]["direct_amount"];

	                    Tbl_reverse_pass_up_settings::insert($insert_pass_up_settings);
	                    $return["status"]         = "success";
	                    $return["update_status"]  = $update_plan["mlm_plan_enable"];
	                    $return["status_code"]    = 201;
	                    $return["status_message"] = "Settings updated...";
              		}
              		else
              		{
              			Plan::update_label($plan,$label);
	                    $update_plan["mlm_plan_enable"] = 1;
	                    Tbl_mlm_plan::where("mlm_plan_code",$plan)->update($update_plan);
	                	$insert_pass_up_settings["membership_id"]     		= $value["membership_id"];
	                    $insert_pass_up_settings["pass_up"]           		= 2;
	                    $insert_pass_up_settings["pass_up_direction"] 		= 1;
	                    $insert_pass_up_settings["pass_up_amount"]      	= 0;
	                    $insert_pass_up_settings["direct"]  		  		= 4;
	                    $insert_pass_up_settings["direct_direction"]  		= 0;
	                    $insert_pass_up_settings["direct_amount"]     	 	= 0;

	                    Tbl_reverse_pass_up_settings::insert($insert_pass_up_settings);
	                    $return["status"]         = "success";
	                    $return["update_status"]  = $update_plan["mlm_plan_enable"];
	                    $return["status_code"]    = 201;
	                    $return["status_message"] = "Settings updated...";
              		}

                }
                else
                {
                	if ($value["pass_up_settings"]["pass_up_direction"] == 1 && $value["pass_up_settings"]["direct_direction"] == 0 && $value["pass_up_settings"]["pass_up"] < $value["pass_up_settings"]["direct"] && $value["pass_up_settings"]["direct_amount"] >= 0 && $value["pass_up_settings"]["pass_up_amount"] >= 0)
              		{
              			Plan::update_label($plan,$label);
	                    $update_plan["mlm_plan_enable"] = 1;
	                    Tbl_mlm_plan::where("mlm_plan_code",$plan)->update($update_plan);
	                    $insert_pass_up_settings["membership_id"]     		= $value["membership_id"];
	                    $insert_pass_up_settings["pass_up"]           		= $value["pass_up_settings"]["pass_up"];
	                    $insert_pass_up_settings["pass_up_direction"] 		= $int;
	                    $insert_pass_up_settings["pass_up_amount"]      	= $value["pass_up_settings"]["pass_up_amount"];
	                    $insert_pass_up_settings["direct"]  		  		= $value["pass_up_settings"]["direct"];
	                    $insert_pass_up_settings["direct_direction"]  		= $int2;
	                    $insert_pass_up_settings["direct_amount"]     	 	= $value["pass_up_settings"]["direct_amount"];

	                    Tbl_reverse_pass_up_settings::where("membership_id",$value["membership_id"])->update($insert_pass_up_settings);
	                    $return["status"]         = "success";
	                    $return["update_status"]  = $update_plan["mlm_plan_enable"];
	                    $return["status_code"]    = 201;
	                    $return["status_message"] = "Settings updated...";
              		}
              		elseif ($value["pass_up_settings"]["pass_up_direction"] == 0 && $value["pass_up_settings"]["direct_direction"] == 1 && $value["pass_up_settings"]["pass_up"] > $value["pass_up_settings"]["direct"] && $value["pass_up_settings"]["direct_amount"] >= 0 && $value["pass_up_settings"]["pass_up_amount"] >= 0)
              		{
              			Plan::update_label($plan,$label);
	                    $update_plan["mlm_plan_enable"] = 1;
	                    Tbl_mlm_plan::where("mlm_plan_code",$plan)->update($update_plan);
	                    $insert_pass_up_settings["membership_id"]     		= $value["membership_id"];
	                    $insert_pass_up_settings["pass_up"]           		= $value["pass_up_settings"]["pass_up"];
	                    $insert_pass_up_settings["pass_up_direction"] 		= $int;
	                    $insert_pass_up_settings["pass_up_amount"]      	= $value["pass_up_settings"]["pass_up_amount"];
	                    $insert_pass_up_settings["direct"]  		  		= $value["pass_up_settings"]["direct"];
	                    $insert_pass_up_settings["direct_direction"]  		= $int2;
	                    $insert_pass_up_settings["direct_amount"]     	 	= $value["pass_up_settings"]["direct_amount"];

	                    Tbl_reverse_pass_up_settings::where("membership_id",$value["membership_id"])->update($insert_pass_up_settings);
	                    $return["status"]         = "success";
	                    $return["update_status"]  = $update_plan["mlm_plan_enable"];
	                    $return["status_code"]    = 201;
	                    $return["status_message"] = "Settings updated...";
              		}
              		else
              		{
              			Plan::update_label($plan,$label);
	                    $update_plan["mlm_plan_enable"] = 1;
	                    Tbl_mlm_plan::where("mlm_plan_code",$plan)->update($update_plan);
	                    // $insert_pass_up_settings["membership_id"]     		= $value["membership_id"];
	                    // $insert_pass_up_settings["pass_up"]           		= $check["pass_up_settings"]["pass_up"];
	                    // $insert_pass_up_settings["pass_up_direction"] 		= $check["pass_up_settings"]["pass_up_direction"];
	                    // $insert_pass_up_settings["pass_up_amount"]      	= $check["pass_up_settings"]["pass_up_amount"];
	                    // $insert_pass_up_settings["direct"]  		  		= $check["pass_up_settings"]["direct"];
	                    // $insert_pass_up_settings["direct_direction"]  		= $check["pass_up_settings"]["direct_direction"];
	                    // $insert_pass_up_settings["direct_amount"]     	 	= $check["pass_up_settings"]["direct_amount"];

	                    // Tbl_reverse_pass_up_settings::where("membership_id",$value["membership_id"])->update($insert_pass_up_settings);
	                    $return["status"]         = "success";
	                    $return["update_status"]  = $update_plan["mlm_plan_enable"];
	                    $return["status_code"]    = 201;
	                    $return["status_message"] = "Settings updated...";
              		}
                }

			}

        }
		$new_value = Get_plan::REVERSE_PASS_UP();
		Audit_trail::audit(serialize($old_value),serialize($new_value),$user,$action);
        return $return;
    }
	public static function REVERSE_PASS_UP_COMBINATIONS($plan,$label,$data)
	{
		$data 		= json_decode($data,true);
		$user      	= Request::user()->id;
		$action    	= "Update REVERSE_PASS_UP_COMBINATIONS";
		$old_value 	= Get_plan::REVERSE_PASS_UP_COMBINATIONS();


		foreach($data["pass_up_settings"] as $key => $value)
		{
			foreach($value as $key2 => $value2)
			{
				$check = Tbl_reverse_pass_up_combination_income::where("membership_id",$key)->where("membership_entry_id",$key2)->first();
				if($check)
				{
					$update["pass_up_income"] 			= $value2;
					Tbl_reverse_pass_up_combination_income::where("membership_id",$key)->where("membership_entry_id",$key2)->update($update);
				}
				else
				{
					$insert["membership_id"]			= $key;
					$insert["membership_entry_id"]      = $key2;
					$insert["pass_up_income"] 			= $value2;
					Tbl_reverse_pass_up_combination_income::insert($insert);
				}
			}
		}
		foreach($data["pass_up_settings2"] as $key => $value)
		{
			foreach($value as $key2 => $value2)
			{
				$check = Tbl_reverse_pass_up_direct_combination_income::where("membership_id",$key)->where("membership_entry_id",$key2)->first();
				if($check)
				{
					$update2["pass_up_direct_income"] 		= $value2;
					Tbl_reverse_pass_up_direct_combination_income::where("membership_id",$key)->where("membership_entry_id",$key2)->update($update2);
				}
				else
				{
					$insert2["membership_id"]				= $key;
					$insert2["membership_entry_id"]      	= $key2;
					$insert2["pass_up_direct_income"] 		= $value2;
					Tbl_reverse_pass_up_direct_combination_income::insert($insert2);
				}
			}
		}
		$new_value = Get_plan::REVERSE_PASS_UP_COMBINATIONS();
		Audit_trail::audit(serialize($old_value),serialize($new_value),$user,$action);

	}
	
	// Created By: Centy - 10-27-2023
	public static function ACHIEVERS_RANK($plan,$label,$data)
	{
		$data 		= json_decode($data,true);
		$user      	= Request::user()->id;
		$action    	= "Update Achievers Rank";
		$old_value 	= Get_plan::ACHIEVERS_RANK();
		
		if($data != null)
		{
			$ctr = 1;
			foreach($data["achievers_rank_settings"] as $index => $value)
			{
				if($value["achievers_rank_name"] != "" && strlen(trim($value["achievers_rank_name"])) != 0)
				{
					$check = Tbl_achievers_rank::where("achievers_rank_level",$ctr)->count();
					if($check == 0)
					{
						$insert["achievers_rank_name"]					= $value["achievers_rank_name"];
						$insert["achievers_rank_binary_points_left"]	= $value["achievers_rank_binary_points_left"] ? $value["achievers_rank_binary_points_left"] : 0;
						$insert["achievers_rank_binary_points_right"]	= $value["achievers_rank_binary_points_right"] ? $value["achievers_rank_binary_points_right"] : 0;
						$insert["achievers_rank_reward"]		    = $value["achievers_rank_reward"] ? $value["achievers_rank_reward"] : 0;
						
						$insert["achievers_rank_level"]			= $ctr;
						$insert["achievers_rank_date_created"]	= Carbon::now();

						Tbl_achievers_rank::insertGetId($insert);

					}
					else
					{
						$update["achievers_rank_name"]			= $value["achievers_rank_name"];
						$update["achievers_rank_binary_points_left"]		= $value["achievers_rank_binary_points_left"] ? $value["achievers_rank_binary_points_left"] : 0;
						$update["achievers_rank_binary_points_right"]				    = $value["achievers_rank_binary_points_right"] ? $value["achievers_rank_binary_points_right"] : 0;
						$update["achievers_rank_reward"]				= $value["achievers_rank_reward"] ? $value["achievers_rank_reward"] : 0;
						
						$update["archive"]						= 0;

						Tbl_achievers_rank::where("achievers_rank_level",$ctr)->update($update);
					}
					$ctr++;
				}
			}
			
			$update_archive["archive"] = 1;
			Tbl_achievers_rank::where("achievers_rank_level",">=",$ctr)->update($update_archive);
		}

		Plan::update_label($plan,$label);

		$update_plan["mlm_plan_enable"] = 1;
		Tbl_mlm_plan::where("mlm_plan_code",$plan)->update($update_plan);
		
		$new_value = Get_plan::ACHIEVERS_RANK();
		Audit_trail::audit(serialize($old_value),serialize($new_value),$user,$action);

		$return["status"]         = "success";
		$return["update_status"]  = $update_plan["mlm_plan_enable"];
		$return["status_code"]    = 201;
		$return["status_message"] = "Settings updated...";

		return $return;
	}
	public static function DROPSHIPPING_BONUS($plan,$label,$data)
	{
		$data 		= json_decode($data,true);
		$user      	= Request::user()->id;
		$action    	= "Update Dropshipping Bonus";
		$old_value 	= Get_plan::DROPSHIPPING_BONUS();

		Plan::update_label($plan,$label);

		$update_plan["mlm_plan_enable"] = 1;
		Tbl_mlm_plan::where("mlm_plan_code",$plan)->update($update_plan);


		$new_value = Get_plan::DROPSHIPPING_BONUS();
		Audit_trail::audit(serialize($old_value),serialize($new_value),$user,$action);

		Tbl_wallet_log::where('wallet_log_details', $old_value['label'])->update(['wallet_log_details' => $new_value['label']]);

		$return["status"]         = "success";
		$return["update_status"]  = $update_plan["mlm_plan_enable"];
		$return["status_code"]    = 201;
		$return["status_message"] = "Settings updated...";

		return $return;
	}	

	public static function WELCOME_BONUS($plan,$label,$data)
	{
		$data 		= json_decode($data,true);
		$user      	= Request::user()->id;
		$action    	= "Update Welcome Bonus";
		$old_value 	= Get_plan::WELCOME_BONUS();
		Plan::update_label($plan,$label);

		if($data) {
			if($data["commission"]) {
				foreach($data["commission"] as $settings) {
					$update["commission"] = $settings["commission"];
					Tbl_welcome_bonus_commissions::where('membership_id', $settings["membership_id"])->update($update);
				}
			}
		}
		$update_plan["mlm_plan_enable"] = 1;
		Tbl_mlm_plan::where("mlm_plan_code",$plan)->update($update_plan);


		$new_value = Get_plan::WELCOME_BONUS();
		Audit_trail::audit(serialize($old_value),serialize($new_value),$user,$action);

		Tbl_wallet_log::where('wallet_log_details', $old_value['label'])->update(['wallet_log_details' => $new_value['label']]);

		$return["status"]         = "success";
		$return["update_status"]  = $update_plan["mlm_plan_enable"];
		$return["status_code"]    = 201;
		$return["status_message"] = "Settings updated...";

		return $return;
	}	

	public static function UNILEVEL_MATRIX_BONUS($plan,$label,$data)
	{
		$data 		= json_decode($data,true);
		$user      	= Request::user()->id;
		$action    	= "Update Unilevel Matrix Bonus";
		$old_value 	= Get_plan::UNILEVEL_MATRIX_BONUS();

		Plan::update_label($plan,$label);

		if($data) {
			
			foreach($data["membership_settings"] as $key => $value) {
				$update_set["matrix_placement"] = $value["matrix_placement"];
				$update_set["unilevel_matrix_level"] = $value["unilevel_matrix_level"];
				Tbl_membership::where("membership_id",$value["membership_id"])->update($update_set);
			}
			Tbl_unilevel_matrix_bonus_levels::truncate();

			foreach($data["membership_settings"] as $key => $value) {
				if(isset($data["unilevel_matrix_bonus_settings"][$value["membership_id"]])) {
					foreach($data["unilevel_matrix_bonus_settings"][$value["membership_id"]] as $membership_id => $per_membership) {
						$level = 1;
						foreach($per_membership as $commission) {

							if($value["unilevel_matrix_level"] >= $level) {
								$insert["level"] = $level;
								$insert["membership_id"] = $value["membership_id"];
								$insert["membership_entry_id"] = $membership_id;
								$insert["matrix_commission"] = $commission;
								$insert["date_created"] = Carbon::now();
								Tbl_unilevel_matrix_bonus_levels::insert($insert);
							}
							$level++;
						}
					}
				}
			}
			$update["matrix_placement_start_at"] = $data["setup"]["matrix_placement_start_at"];
			Tbl_unilevel_matrix_bonus_settings::where('id', $data['setup']['id'])->update($update);
		}		

		$update_plan["mlm_plan_enable"] = 1;
		Tbl_mlm_plan::where("mlm_plan_code",$plan)->update($update_plan);
		
		$new_value = Get_plan::UNILEVEL_MATRIX_BONUS();
		Audit_trail::audit(serialize($old_value),serialize($new_value),$user,$action);

		Tbl_wallet_log::where('wallet_log_details', $old_value['label'])->update(['wallet_log_details' => $new_value['label']]);
		

		$return["status"]         = "success";
		$return["update_status"]  = $update_plan["mlm_plan_enable"];
		$return["status_code"]    = 201;
		$return["status_message"] = "Settings updated...";

		return $return;
	}

	public static function LIVEWELL_RANK($plan,$label,$data)
	{
		$data 		= json_decode($data,true);
		$user      	= Request::user()->id;
		$action    	= "Update Livewell Rank";
		$old_value 	= Get_plan::LIVEWELL_RANK();
		
		if($data != null)
		{
			$ctr = 1;
			foreach($data["rank_settings"] as $index => $value)
			{
				if($value["livewell_rank_name"] != "" && strlen(trim($value["livewell_rank_name"])) != 0)
				{	
					$check = Tbl_livewell_rank::where("livewell_rank_level",$ctr)->count();
					if($check == 0)
					{
						$insert["livewell_rank_name"] = $value["livewell_rank_name"];
						$insert["livewell_bind_membership"] = $value["livewell_bind_membership"] ? $value["livewell_bind_membership"] : 0;
						$insert["livewell_rank_level"] = $ctr;
						$insert["livewell_rank_date_created"] = Carbon::now();

						Tbl_livewell_rank::insertGetId($insert);

					}
					else
					{
						$update["livewell_rank_name"] = $value["livewell_rank_name"];
						$update["livewell_bind_membership"] = $value["livewell_bind_membership"] ? $value["livewell_bind_membership"] : 0;
						$update["livewell_rank_date_created"] = Carbon::now();
						$update["archive"] = 0;
						Tbl_livewell_rank::where("livewell_rank_level", $ctr)->update($update);
					}
					$ctr++;
				}
			}

			$update_archive["archive"] = 1;
			Tbl_livewell_rank::where("livewell_rank_level",">=",$ctr)->update($update_archive);
		}
		Plan::update_label($plan,$label);

		$update_plan["mlm_plan_enable"] = 1;
		Tbl_mlm_plan::where("mlm_plan_code",$plan)->update($update_plan);
		
		$new_value = Get_plan::LIVEWELL_RANK();
		Audit_trail::audit(serialize($old_value),serialize($new_value),$user,$action);

		$return["status"]         = "success";
		$return["update_status"]  = $update_plan["mlm_plan_enable"];
		$return["status_code"]    = 201;
		$return["status_message"] = "Settings updated...";

		return $return;
	}

	public static function REWARD_POINTS($plan,$label,$data)
	{
		$data 		= json_decode($data,true);
		$user      	= Request::user()->id;
		$action    	= "Update Reward Points";
		$old_value 	= Get_plan::REWARD_POINTS();
		
		
		Plan::update_label($plan,$label);

		$update_plan["mlm_plan_enable"] = 1;
		Tbl_mlm_plan::where("mlm_plan_code",$plan)->update($update_plan);
		
		$new_value = Get_plan::REWARD_POINTS();
		Audit_trail::audit(serialize($old_value),serialize($new_value),$user,$action);

		$return["status"]         = "success";
		$return["update_status"]  = $update_plan["mlm_plan_enable"];
		$return["status_code"]    = 201;
		$return["status_message"] = "Settings updated...";

		return $return;
	}

	public static function PRIME_REFUND($plan,$label,$data)
	{
		$data 		= json_decode($data,true);
		$user      	= Request::user()->id;
		$action    	= "Update Prime Refund";
		$old_value 	= Get_plan::PRIME_REFUND();
		
		if ($data) {
			
			// Batch update membership settings
			if (!empty($data["membership_settings"])) {
				foreach ($data["membership_settings"] as $memb) {
					Tbl_membership::where('membership_id', $memb['membership_id'])
						->update([
							'prime_refund_accumulated_points' => $memb['prime_refund_accumulated_points'],
							'prime_refund_enable' => $memb['prime_refund_enable'],
						]);
				}
			}
			
			if (!empty($data["setup"])) {
				// Get enabled memberships in one query
				$enabledMemberships = Tbl_membership::whereIn('membership_id', array_keys($data["setup"]))
					->pluck('prime_refund_enable', 'membership_id');
		
				// Get existing entries to update
				$existingEntries = Tbl_prime_refund_setup::whereIn('membership_id', array_keys($data["setup"]))
					->pluck('membership_entry_id', 'membership_id');
		
				$updates = [];
		
				foreach ($data["setup"] as $membershipId => $membershipEntries) {
					$isEnabled = !empty($enabledMemberships[$membershipId]);
		
					foreach ($membershipEntries as $entryId => $points) {
						$updates[] = [
							'membership_id' => $membershipId,
							'membership_entry_id' => $entryId,
							'prime_refund_points' => $isEnabled ? $points : 0, // Set to 0 if disabled
						];
					}
				}
		
				// Perform batch update/insert
				if (!empty($updates)) {
					foreach ($updates as $update) {
						Tbl_prime_refund_setup::updateOrInsert(
							[
								'membership_id' => $update['membership_id'],
								'membership_entry_id' => $update['membership_entry_id'],
							],
							[
								'prime_refund_points' => $update['prime_refund_points'],
							]
						);
					}
				}
			}

			if(isset($data['prime_refund_points_label']))
			{
				Plan::update_label("PRIME_REFUND_POINTS",$data['prime_refund_points_label']);
			}
		}
		
		Plan::update_label($plan,$label);

		$update_plan["mlm_plan_enable"] = 1;
		Tbl_mlm_plan::where("mlm_plan_code",$plan)->update($update_plan);
		
		$new_value = Get_plan::PRIME_REFUND();
		Audit_trail::audit(serialize($old_value),serialize($new_value),$user,$action);

		$return["status"]         = "success";
		$return["update_status"]  = $update_plan["mlm_plan_enable"];
		$return["status_code"]    = 201;
		$return["status_message"] = "Settings updated...";

		return $return;
	}

	public static function INCENTIVE($plan,$label,$data)
	{
		$data 		= json_decode($data,true);
		$user      	= Request::user()->id;
		$action    	= "Update Lavin Rank";
		$old_value 	= Get_plan::INCENTIVE();
		if ($data != null) {
			$existingSetups = Tbl_incentive_setup::where('archive', 0)->pluck('setup_id')->toArray(); // Get all existing records
		
			$currentItemIds = []; // Store item_ids that are still valid

			foreach ($data["setup"] as $setup) {
				if (!isset($setup["setup_id"]) || empty($setup["setup_id"])) {
					// Insert new record without setup_id (since it's auto-increment)
					$inserted = Tbl_incentive_setup::insertGetId([
						"item_id" => $setup["item_id"],
						"reward_item_id" => $setup["reward_item_id"],
						"number_of_purchase" => $setup["number_of_purchase"],
					]);
			
					// Store the newly created setup_id
					$currentItemIds[] = $inserted;
				} else {
					// Update existing record
					Tbl_incentive_setup::where("setup_id", $setup["setup_id"])->update([
						"item_id" => $setup["item_id"],
						"reward_item_id" => $setup["reward_item_id"],
						"number_of_purchase" => $setup["number_of_purchase"],
					]);
			
					$currentItemIds[] = $setup["setup_id"];
				}
			}
			
		
			// Find items that exist in the database but not in the new data
			$itemsToArchive = array_diff($existingSetups, $currentItemIds);
 
			// Archive these items (update `archived` status)
			if (!empty($itemsToArchive)) {
				Tbl_incentive_setup::whereIn('setup_id', $itemsToArchive)->update(['archive' => 1]);
			}
		}
		
		Plan::update_label($plan,$label);

		$update_plan["mlm_plan_enable"] = 1;
		Tbl_mlm_plan::where("mlm_plan_code",$plan)->update($update_plan);
		
		$new_value = Get_plan::INCENTIVE();
		Audit_trail::audit(serialize($old_value),serialize($new_value),$user,$action);

		$return["status"]         = "success";
		$return["update_status"]  = $update_plan["mlm_plan_enable"];
		$return["status_code"]    = 201;
		$return["status_message"] = "Settings updated...";

		return $return;
	}

	public static function MILESTONE_BONUS($plan,$label,$data)
	{

		$data 		= json_decode($data,true);
		$user      	= Request::user()->id;
		$action    	= "Update Milestone Bonus";
		$old_value 	= Get_plan::MILESTONE_BONUS();
		if ($data != null) {
			if(!empty($data["settings"])) {
				foreach($data["settings"] as $label_name => $setup) {
					$update_settings[$label_name] = $setup;
				}
				Tbl_milestone_bonus_settings::where("milestone_settings_id", 1)
				->update($update_settings);
			}
			if (!empty($data["membership_settings"])) {
				foreach ($data["membership_settings"] as $value) {
					Tbl_membership::where("membership_id", $value["membership_id"])
						->update(["milestone_maximum_limit" => $value["milestone_maximum_limit"]]);
				}
			}

			if (!empty($data["points_setup"])) {
				foreach ($data["points_setup"] as $membership_id => $entries) {
					foreach ($entries as $membership_entry_id => $points) {
						$existing = Tbl_milestone_points_setup::where("membership_id", $membership_id)
							->where("membership_entry_id", $membership_entry_id)
							->exists();

						$payload = [
							"membership_id" => $membership_id,
							"membership_entry_id" => $membership_entry_id,
							"milestone_points" => $points,
						];

						if ($existing) {
							Tbl_milestone_points_setup::where("membership_id", $membership_id)
								->where("membership_entry_id", $membership_entry_id)
								->update(["milestone_points" => $points]);
						} else {
							Tbl_milestone_points_setup::insert($payload);
						}
					}
				}
			}
			if (!empty($data["pairing_setup"])) {
				$combined_id = [];

				foreach ($data["pairing_setup"] as $value) {
					$bonus = floatval($value["milestone_pairing_bonus"] ?? 0);
					if ($bonus <= 0) {
						continue;
					}

					$left = intval($value["milestone_pairing_left"] ?? 0);
					$right = intval($value["milestone_pairing_right"] ?? 0);
					$membership = !empty($value["membership_id"]) ? $value["membership_id"] : null;

					$existing = Tbl_milestone_pairing_points_setup::where("milestone_pairing_left", $left)
						->where("milestone_pairing_right", $right)
						->where("membership_id", $membership)
						->first();


					if (!$existing) {
						
						$insert = [
							"milestone_pairing_left" => $left,
							"milestone_pairing_right" => $right,
							"milestone_pairing_bonus" => $bonus,
							"membership_id" => $membership ?: null,
							"created_at" => now(),
						];
						$id = Tbl_milestone_pairing_points_setup::insertGetId($insert);
						$combined_id[] = $id;
					} else {
						
						Tbl_milestone_pairing_points_setup::where("points_setup_id", $existing->points_setup_id)
							->update([
								"milestone_pairing_bonus" => $bonus,
								"updated_at" => now(),
								"archive" => 0
							]);
						$combined_id[] = $existing->points_setup_id;
					}
				}

				$update_archive["archive"] = 1;
				Tbl_milestone_pairing_points_setup::whereNotIn("points_setup_id", $combined_id)->update($update_archive);
			}
		}
		Plan::update_label($plan,$label);

		$update_plan["mlm_plan_enable"] = 1;
		Tbl_mlm_plan::where("mlm_plan_code",$plan)->update($update_plan);
		
		$new_value = Get_plan::MILESTONE_BONUS();
		Audit_trail::audit(serialize($old_value),serialize($new_value),$user,$action);

		$return["status"]         = "success";
		$return["update_status"]  = $update_plan["mlm_plan_enable"];
		$return["status_code"]    = 201;
		$return["status_message"] = "Settings updated...";

		return $return;
	}

	public static function INFINITY_BONUS($plan,$label,$data)
	{

		$data = json_decode($data,true);
		$user      = Request::user()->id;
		$action    = "Update Infinity Bonus";
		$old_value = Get_plan::INFINITY_BONUS();
		if($data != null)
		{
			Tbl_infinity_bonus_setup::truncate();
			foreach($data["membership_settings"] as $key => $value) {
				if(isset($data["infinity_bonus_settings"][$value["membership_id"]]))
				{
					/* GET THE DATA SETTINGS PER MEMBERSHIP */
					foreach($data["infinity_bonus_settings"][$value["membership_id"]] as $membership_entry_id => $per_membership)
					{
						$level = 1;
						/* GET THE DATA SETTINGS PER LEVEL OF TARGET MEMBERSHIP */
						foreach($per_membership as $level_target => $percentage)
						{
							$check = Tbl_infinity_bonus_setup::where("level",$level)->where("membership_id",$value["membership_id"])->where("membership_entry_id",$membership_entry_id)->first();
							if($check)
							{
								$update_level["level"] = $level;
								$update_level["membership_id"] = $value["membership_id"];
								$update_level["membership_entry_id"] = $membership_entry_id;
								$update_level["percentage"] = $percentage;
								Tbl_infinity_bonus_setup::where("level",$level)->where("membership_id",$value["membership_id"])->where("membership_entry_id",$membership_entry_id)->update($update_level);
							}
							else
							{
								$insert["level"] = $level;
								$insert["membership_id"] = $value["membership_id"];
								$insert["membership_entry_id"] = $membership_entry_id;
								$insert["percentage"] = $percentage;
								Tbl_infinity_bonus_setup::insert($insert);
							}

							$level++;
							if($level > $value["infinity_bonus_level"])
							{
								Tbl_infinity_bonus_setup::where("level",">=",$level)->where("membership_id",$value["membership_id"])->delete();
								break;
							}
						}

					}
				}

				$update["infinity_bonus_level"] = count(Tbl_infinity_bonus_setup::select("level")->where("membership_id",$value["membership_id"])->groupBy("level")->get());
				Tbl_membership::where("membership_id",$value["membership_id"])->update($update);
			}
		}

		Plan::update_label($plan,$label);
		$update_plan["mlm_plan_enable"] = 1;
		Tbl_mlm_plan::where("mlm_plan_code",$plan)->update($update_plan);

		$new_value = Get_plan::INFINITY_BONUS();
		Audit_trail::audit(serialize($old_value),serialize($new_value),$user,$action);

		$return["status"]         = "success";
		$return["update_status"]  = $update_plan["mlm_plan_enable"];
		$return["status_code"]    = 201;
		$return["status_message"] = "Settings updated...";

		return $return;
	}

	public static function MARKETING_SUPPORT($plan,$label,$data)
	{

		$data 		= json_decode($data,true);
		$user      	= Request::user()->id;
		$action    	= "Update Marketing Support";
		$old_value 	= Get_plan::MARKETING_SUPPORT();
		if ($data != null) {
			if(!empty($data["settings"])) {
				foreach($data["settings"] as $label_name => $setup) {
					$update_settings[$label_name] = $setup;
				}
				Tbl_marketing_support_settings::where("settings_id", 1)
				->update($update_settings);
			}
			if (!empty($data["membership_settings"])) {
				foreach ($data["membership_settings"] as $value) {
					Tbl_membership::where("membership_id", $value["membership_id"])
						->update([
							"marketing_support_enable" => $value["marketing_support_enable"],
							"marketing_support_left_required_directs_to_activate" => $value["marketing_support_left_required_directs_to_activate"],
							"marketing_support_right_required_directs_to_activate" => $value["marketing_support_right_required_directs_to_activate"],
							"marketing_support_required_directs_for_recurring" => $value["marketing_support_required_directs_for_recurring"]
						]);
				}
			}

			if (!empty($data["setup"])) {
				foreach ($data["setup"] as $membership_id => $entries) {
					foreach ($entries as $membership_entry_id => $income) {
						$existing = Tbl_marketing_support_setup::where("membership_id", $membership_id)
							->where("membership_entry_id", $membership_entry_id)
							->exists();

						$income = Tbl_membership::where("membership_id", $membership_id)->value("marketing_support_enable") ? $income : 0;

						$payload = [
							"membership_id" => $membership_id,
							"membership_entry_id" => $membership_entry_id,
							"income" => $income,
						];

						if ($existing) {
							Tbl_marketing_support_setup::where("membership_id", $membership_id)
								->where("membership_entry_id", $membership_entry_id)
								->update(["income" => $income]);
						} else {
							Tbl_marketing_support_setup::insert($payload);
						}
					}
				}
			}
			
		}
		Plan::update_label($plan,$label);

		$update_plan["mlm_plan_enable"] = 1;
		Tbl_mlm_plan::where("mlm_plan_code",$plan)->update($update_plan);
		
		$new_value = Get_plan::MARKETING_SUPPORT();
		Audit_trail::audit(serialize($old_value),serialize($new_value),$user,$action);

		$return["status"]         = "success";
		$return["update_status"]  = $update_plan["mlm_plan_enable"];
		$return["status_code"]    = 201;
		$return["status_message"] = "Settings updated...";

		return $return;
	}

	public static function LEADERS_SUPPORT($plan,$label,$data)
	{

		$data 		= json_decode($data,true);
		$user      	= Request::user()->id;
		$action    	= "Update Leader's Support";
		$old_value 	= Get_plan::LEADERS_SUPPORT();
		if ($data != null) {
			if(!empty($data["settings"])) {
				foreach($data["settings"] as $label_name => $setup) {
					$update_settings[$label_name] = $setup;
				}
				$update_settings['updated_at'] = Carbon::now();
				Tbl_leaders_support_settings::where("settings_id", 1)
				->update($update_settings);
			}
			if (!empty($data["membership_settings"])) {
				foreach ($data["membership_settings"] as $value) {
					Tbl_membership::where("membership_id", $value["membership_id"])
						->update([
							"leaders_support_enable" => $value["leaders_support_enable"]
						]);
				}
			}

			if (!empty($data["setup"])) {
				foreach ($data["setup"] as $membership_id => $entries) {
					foreach ($entries as $membership_entry_id => $income) {
						$existing = Tbl_leaders_support_setup::where("membership_id", $membership_id)
							->where("membership_entry_id", $membership_entry_id)
							->exists();

						$income = Tbl_membership::where("membership_id", $membership_id)->value("leaders_support_enable") ? $income : 0;

						$payload = [
							"membership_id" => $membership_id,
							"membership_entry_id" => $membership_entry_id,
							"income" => $income,
						];

						if ($existing) {
							Tbl_leaders_support_setup::where("membership_id", $membership_id)
								->where("membership_entry_id", $membership_entry_id)
								->update(["income" => $income]);
						} else {
							Tbl_leaders_support_setup::insert($payload);
						}
					}
				}
			}
			
		}
		Plan::update_label($plan,$label);

		$update_plan["mlm_plan_enable"] = 1;
		Tbl_mlm_plan::where("mlm_plan_code",$plan)->update($update_plan);
		
		$new_value = Get_plan::LEADERS_SUPPORT();
		Audit_trail::audit(serialize($old_value),serialize($new_value),$user,$action);

		$return["status"]         = "success";
		$return["update_status"]  = $update_plan["mlm_plan_enable"];
		$return["status_code"]    = 201;
		$return["status_message"] = "Settings updated...";

		return $return;
	}
}
