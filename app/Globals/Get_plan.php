<?php
namespace App\Globals;

use App\Models\Tbl_achievers_rank;
use App\Models\Tbl_welcome_bonus_commissions;
use Carbon\Carbon;
use App\Models\Tbl_membership;
use App\Models\Tbl_membership_income;
use App\Models\Tbl_mlm_plan;
use App\Models\Tbl_membership_indirect_level;
use App\Models\Tbl_membership_unilevel_level;
use App\Models\Tbl_membership_unilevel_or_level;
use App\Models\Tbl_membership_cashback_level;
use App\Models\Tbl_membership_leveling_bonus_level;
use App\Models\Tbl_mlm_unilevel_settings;
use App\Models\Tbl_mlm_monoline_settings;
use App\Models\Tbl_mlm_pass_up_settings;
use App\Models\Tbl_stairstep_settings;
use App\Models\Tbl_stairstep_rank;
use App\Models\Tbl_binary_pairing;
use App\Models\Tbl_binary_settings;
use App\Models\Tbl_binary_points_settings;
use App\Models\Tbl_mlm_universal_pool_bonus_settings;
use App\Models\Tbl_mlm_universal_pool_bonus_maintain_settings;
use App\Models\Tbl_mlm_incentive_bonus;
use App\Models\Tbl_membership_mentors_level;
use App\Models\Tbl_global_pool_bonus_settings;
use App\Models\Tbl_membership_vortex;
use App\Models\Tbl_vortex_settings;
use App\Models\Tbl_membership_gc_income;
use App\Models\Tbl_membership_upgrade_settings;
use App\Models\Tbl_direct_bonus;
use App\Models\Tbl_incentive_setup;
use App\Models\Tbl_sponsor_matching;
use App\Models\Tbl_share_link_settings;
use App\Models\Tbl_watch_earn_settings;
use App\Models\Tbl_indirect_settings;
use App\Models\Tbl_infinity_bonus_setup;
use App\Models\Tbl_item;
use App\Models\Tbl_leaders_support_settings;
use App\Models\Tbl_leaders_support_setup;
use App\Models\Tbl_passive_unilevel_premium;
use App\Models\Tbl_membership_product_level;
use App\Models\Tbl_membership_overriding_commission_level;
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
use App\Models\Tbl_milestone_bonus_settings;
use App\Models\Tbl_milestone_pairing_points_setup;
use App\Models\Tbl_milestone_points_setup;
use App\Models\Tbl_prime_refund_setup;

class Get_plan
{
	public static function SPONSOR_MATCHING_BONUS()
	{

		$plan 				   = Tbl_mlm_plan::where("mlm_plan_code","SPONSOR_MATCHING_BONUS")->first();
		$data["label"]         = Plan::get_label($plan->mlm_plan_code);
		$data["status"]        = $plan->mlm_plan_enable;

		$data["settings"]      = [];

		$data["settings"]["setup"] 							      = [];
		$data["settings"]["setup"]["sponsor_matching_percent"]    = Tbl_sponsor_matching::first() ? Tbl_sponsor_matching::first()->sponsor_matching_percent  : 0;

		return $data;
	}

	public static function VORTEX_PLAN()
	{

		$plan 				   = Tbl_mlm_plan::where("mlm_plan_code","VORTEX_PLAN")->first();
		$data["label"]         = Plan::get_label($plan->mlm_plan_code);
		$data["status"]        = $plan->mlm_plan_enable;

		$data["settings"]      = [];

		$get                   = Tbl_membership::where("archive",0)->get();

		$data["settings"]["setup"] 							  = [];
		$data["settings"]["setup"]["vortex_slot_required"]    = Tbl_vortex_settings::first() ? Tbl_vortex_settings::first()->vortex_slot_required  : 0;
		$data["settings"]["setup"]["vortex_token_required"]   = Tbl_vortex_settings::first() ? Tbl_vortex_settings::first()->vortex_token_required : 0;
		$data["settings"]["setup"]["vortex_token_reward"]     = Tbl_vortex_settings::first() ? Tbl_vortex_settings::first()->vortex_token_reward   : 0;


		foreach($get as $g)
		{
			foreach($get as $g2)
			{
				$check = Tbl_membership_vortex::where("membership_id",$g->membership_id)->where("membership_entry_id",$g2->membership_id)->first();
				if($check)
				{
					$data["settings"]["vortex_settings"][$g->membership_id][$g2->membership_id] = $check->membership_vortex_token;
				}
				else
				{
					$data["settings"]["vortex_settings"][$g->membership_id][$g2->membership_id] = 0;
				}
			}
		}

		return $data;
	}

	public static function DIRECT()
	{

		$plan 				   = Tbl_mlm_plan::where("mlm_plan_code","DIRECT")->first();
		$data["label"]         = Plan::get_label($plan->mlm_plan_code);
		$data["status"]        = $plan->mlm_plan_enable;

		$data["settings"]      = [];


		$data["settings"]["direct_settings"] = [];

		$get                   = Tbl_membership::where("archive",0)->get();
		foreach($get as $g)
		{
			foreach($get as $g2)
			{
				$check = Tbl_membership_income::where("membership_id",$g->membership_id)->where("membership_entry_id",$g2->membership_id)->first();
				if($check)
				{
					$data["settings"]["direct_settings"][$g->membership_id][$g2->membership_id] = $check->membership_direct_income;
				}
				else
				{
					$data["settings"]["direct_settings"][$g->membership_id][$g2->membership_id] = 0;
				}
				$check2 = Tbl_membership_gc_income::where("membership_id",$g->membership_id)->where("membership_entry_id",$g2->membership_id)->first();
				if($check2)
				{
					$data["settings"]["direct_settings2"][$g->membership_id][$g2->membership_id] = $check2->membership_gc_income;
				}
				else
				{
					$data["settings"]["direct_settings2"][$g->membership_id][$g2->membership_id] = 0;
				}
			}
		}
		$check12 = Tbl_direct_bonus::where("archive",0)->select("direct_bonus_id","hierarchy","direct_bonus_checkpoint","direct_bonus_amount","archive")->first();
		if (!$check12) 
		{
			$insert["hierarchy"]         	   = 1;
			$insert["direct_bonus_checkpoint"] = 0;
			$insert["direct_bonus_amount"]     = 0;

			Tbl_direct_bonus::insert($insert);
		}
		$data["settings"]["manage_direct_bonus"] = Tbl_direct_bonus::where("archive",0)->select("direct_bonus_id","hierarchy","direct_bonus_checkpoint","direct_bonus_amount","archive")->get();

		return $data;
	}

	public static function INDIRECT()
	{
		$plan 				   = Tbl_mlm_plan::where("mlm_plan_code","INDIRECT")->first();
		$data["label"]         = Plan::get_label($plan->mlm_plan_code);
		$data["status"]        = $plan->mlm_plan_enable;

		$data["settings"]      = [];
		$get                   = Tbl_membership_indirect_level::get();
		$membership            = Tbl_membership::where("archive",0)->get();

		$data["settings"]["indirect_settings"] = [];
		$data["settings"]["membership_level"]  = [];

		$check_points_settings = Tbl_indirect_settings::first();
		if(!$check_points_settings)
		{
			$insert['indirect_points_enable'] = 0 ;
			$insert['indirect_points_minimum_conversion'] = 0 ;
			$check_points_settings = Tbl_indirect_settings::insert($insert);
		}

		$data["settings"]["indirect_points_settings"] = $check_points_settings;
		foreach($membership as $memb)
		{
			$data["settings"]["membership_level"][$memb->membership_id] = array_fill(0, $memb->membership_indirect_level, "");
		}

		// foreach($get as $g)
		// {
		// 	$data["settings"]["indirect_settings"][$g->membership_id][$g->membership_level][$g->membership_entry_id] = $g->membership_indirect_income;
		// }


		foreach($membership as $memb)
		{
			foreach($membership as $memb2)
			{
				$membership_indirect_level = $memb->membership_indirect_level + 1;
				for($level = 2; $level <= $membership_indirect_level ; $level++)
				{
					$percent_value = Tbl_membership_indirect_level::where("membership_id",$memb->membership_id)->where("membership_entry_id",$memb2->membership_id)->where("membership_level",$level)->first();
					$percent_value = $percent_value ? $percent_value->membership_indirect_income : 0;

					$data["settings"]["indirect_settings"][$memb->membership_id][$memb2->membership_id][$level] = $percent_value;
				}
			}
		}

		return $data;
	}

	public static function UNILEVEL()
	{

		$plan 				   = Tbl_mlm_plan::where("mlm_plan_code","UNILEVEL")->first();
		$data["label"]         = Plan::get_label($plan->mlm_plan_code);
		$data["status"]        = $plan->mlm_plan_enable;

		$data["settings"]      = [];
		$get                   = Tbl_membership_unilevel_level::get();
		$membership            = Tbl_membership::where("archive",0)->get();

		$check_exist = Tbl_mlm_unilevel_settings::first();
		if(!$check_exist)
		{
			$settings["personal_as_group"]		  	= 0;
			$settings["gpv_to_wallet_conversion"] = 0;
			$settings["auto_ship"] 				  			= 0;
			$settings["is_dynamic"] 				  		= 'normal';
			Tbl_mlm_unilevel_settings::insert($settings);
		}

		$data["settings"]["setup"]             		  = Tbl_mlm_unilevel_settings::first();
		$data["settings"]["setup"]->personal_pv       = Plan::get_label("PERSONAL_PV");
		$data["settings"]["setup"]->group_pv          = Plan::get_label("GROUP_PV");
		$data["settings"]["unilevel_settings"]        = [];
		$data["settings"]["membership_level"]         = [];

		foreach($membership as $memb)
		{
			$data["settings"]["membership_level"][$memb->membership_id] = array_fill(0, $memb->membership_unilevel_level, "");
		}


		foreach($membership as $memb)
		{
			foreach($membership as $memb2)
			{
				for($level = 1; $level <= $memb->membership_unilevel_level ; $level++)
				{
					$percent_value = Tbl_membership_unilevel_level::where("membership_id",$memb->membership_id)->where("membership_entry_id",$memb2->membership_id)->where("membership_level",$level)->first();
					$percent_value = $percent_value ? $percent_value->membership_percentage : 0;

					$data["settings"]["unilevel_settings"][$memb->membership_id][$memb2->membership_id][$level] = $percent_value;
				}
			}
		}

		// echo "<pre>";
		// var_dump($data);
		// echo "</pre>";

		// dd(123);
		return $data;
	}

	public static function STAIRSTEP()
	{
		$plan 				   = Tbl_mlm_plan::where("mlm_plan_code","STAIRSTEP")->first();
		$data["label"]         = Plan::get_label($plan->mlm_plan_code);
		$data["status"]        = $plan->mlm_plan_enable;

		$data["settings"]      = [];

		$check_exist = Tbl_stairstep_settings::first();
		if(!$check_exist)
		{
			$settings["personal_as_group"]	= 0;
			$settings["live_update"]		= 0;
			$settings["auto_ship"]			= 0;
			Tbl_stairstep_settings::insert($settings);
		}

		$data["settings"]["setup"]              	            = Tbl_stairstep_settings::first();
		$data["settings"]["setup"]->personal_stairstep_pv_label = Plan::get_label("PERSONAL_STAIRSTEP_PV_LABEL");
		$data["settings"]["setup"]->group_stairstep_pv_label    = Plan::get_label("GROUP_STAIRSTEP_PV_LABEL");
		$data["settings"]["setup"]->earning_label_points        = Plan::get_label("STAIRSTEP_EARNING_POINTS_LABEL");
		$data["settings"]["stairstep_settings"] 	            = [];
		$data["settings"]["stairstep_settings_end"]             = (object)array("stairstep_rank_name"=>"","breakaway_level"=>"","equal_bonus"=>"","stairstep_rank_override"=>"","stairstep_rank_personal"=>"","stairstep_rank_personal_all"=>"","stairstep_rank_group_all"=>"","stairstep_rank_upgrade"=>"","stairstep_rank_name_id"=>"");
		$data["settings"]["membership_level"]  		            = [];

		$stairstep_rank    = Tbl_stairstep_rank::where("archive",0)
											   ->select("breakaway_level","check_match_percentage","equal_bonus","check_match_level","stairstep_advancement_bonus","stairstep_commission","stairstep_rank_level","stairstep_rank_id","stairstep_rank_name","stairstep_rank_override","stairstep_rank_personal","stairstep_rank_personal_all","stairstep_rank_group_all","stairstep_rank_upgrade","stairstep_rank_name_id","stairstep_direct_referral")
											   ->get();

		$array = array();
		foreach($stairstep_rank as $srank)
		{
			array_push($array,$srank);
		}
		$data["settings"]["stairstep_settings"]      = $array;
		$data["settings"]["count_stairstep_settings"] = count($array);

		// dd($data);
		return $data;
	}

	public static function BINARY()
	{
		$plan 				   = Tbl_mlm_plan::where("mlm_plan_code","BINARY")->first();
		$data["label"]         = Plan::get_label($plan->mlm_plan_code);
		$data["status"]        = $plan->mlm_plan_enable;

		$data["settings"]      = [];
		
		$data["settings"]["mentors_bonus_label"] = Plan::get_label("MENTORS_BONUS");
		$data["settings"]["binary_projected_income_label"] = Plan::get_label("BINARY_PROJECTED_INCOME");
		$data["settings"]["binary_slot_limit_settings"] = [];

		$check_exist = Tbl_binary_settings::first();
		if(!$check_exist)
		{
			$setting["auto_placement"]               	 = 0;
			$setting["auto_placement_type"]          	 = 0;
			$setting["member_disable_auto_position"] 	 = 0;
			$setting["member_default_position"]      	 = 0;
			$setting["strong_leg_retention"]         	 = 0;
			$setting["gc_pairing_count"]             	 = 0;
			$setting["crossline"]             		 	 = 0;
			$setting["cycle_per_day"]                	 = 1;
			$setting["included_binary_repurchase"]   	 = 0;
			$setting["amount_binary_limit"]          	 = 0;
			$setting["sponsor_matching_cycle"]       	 = 1;
			$setting["sponsor_matching_limit"]       	 = 0;
			$setting["mentors_matching_cycle"]       	 = 1;
			$setting["mentors_matching_limit"]       	 = 0;
			$setting["binary_points_enable"]         	 = 0;
			$setting["binary_points_minimum_conversion"] = 0;
			$setting["mentors_points_enable"]         	 = 0;
			$setting["mentors_points_minimum_conversion"] = 0;
			$setting["binary_extreme_position"] = 0;
			Tbl_binary_settings::insert($setting);
		}

		$data["settings"]["setup"]                               = Tbl_binary_settings::first();
		$data["settings"]["setup"]->binary_points_left           = Plan::get_label("BINARY_POINTS_LEFT");
		$data["settings"]["setup"]->binary_points_right          = Plan::get_label("BINARY_POINTS_RIGHT");

		$data["settings"]["binary_settings_pair"] 	  			 = [];
		$data["settings"]["binary_settings_pair_end"] 			 = (object)array("binary_pairing_id"=>"","binary_pairing_left"=>"","binary_pairing_right"=>"","binary_pairing_bonus"=>"","binary_pairing_membership" => "");

		$data["settings"]["label_log"]["binary_points_left"]  = "Left Points";
		$data["settings"]["label_log"]["binary_points_right"] = "Right Points";

		$binary_pairing    = Tbl_binary_pairing::where("archive",0)
											   ->get();
		$array = array();
		foreach($binary_pairing as $key => $bpair)
		{
			$binary_pairing[$key]->binary_pairing_membership = $binary_pairing[$key]->binary_pairing_membership == null ? 0 : $binary_pairing[$key]->binary_pairing_membership;
			array_push($array,$bpair);
		}

		$data["settings"]["mentors_level"]    = [];
		$data["settings"]["mentors_settings"] = [];
		$membership 					   = Tbl_membership::where("archive",0)->get();
		foreach($membership as $memb)
		{
			$data["settings"]["mentors_level"][$memb->membership_id] = array_fill(0, $memb->mentors_level, "");
		}

		foreach($membership as $key => $memb)
		{
			$data["settings"]["membership_level"][$memb->membership_id] = $memb->membership_binary_level;
		}

		$get = Tbl_membership_mentors_level::get();

		foreach($get as $g)
		{
			$data["settings"]["mentors_settings"][$g->membership_id][$g->membership_level] = [];
			$data["settings"]["mentors_settings"][$g->membership_id][$g->membership_level] = [];

			$data["settings"]["mentors_settings"][$g->membership_id][$g->membership_level]["mentors_bonus"] = $g->mentors_bonus;
			$data["settings"]["mentors_settings"][$g->membership_id][$g->membership_level]["mentors_direct"] = $g->mentors_direct;
		}

		$data["settings"]["binary_settings_pair"]        = $array;
		$data["settings"]["count_binary_settings_pair"]  = count($array);


		$data["settings"]["binary_settings"] 	      = [];
		$get                                          = Tbl_membership::where("archive",0)->get();
		foreach($get as $g)
		{
			foreach($get as $g2)
			{
				$check = Tbl_binary_points_settings::where("membership_id",$g->membership_id)->where("membership_entry_id",$g2->membership_id)->first();
				if($check)
				{
					$data["settings"]["binary_settings"][$g->membership_id][$g2->membership_id] = $check->membership_binary_points;
					$data["settings"]["binary_slot_limit_settings"][$g->membership_id][$g2->membership_id] = $check->max_slot_per_level;
				}
				else
				{
					$data["settings"]["binary_settings"][$g->membership_id][$g2->membership_id] = 0;
					$data["settings"]["binary_slot_limit_settings"][$g->membership_id][$g2->membership_id] = 0;
				}
			}
		}
		return $data;
	}

	public static function CASHBACK()
	{
		$plan 				   = Tbl_mlm_plan::where("mlm_plan_code","CASHBACK")->first();
		$data["label"]         = Plan::get_label($plan->mlm_plan_code);
		$data["status"]        = $plan->mlm_plan_enable;

		$data["settings"]      = [];
		$get                   = Tbl_membership_cashback_level::get();
		$membership            = Tbl_membership::where("archive",0)->get();

		$data["settings"]["cashback_settings"] = [];
		$data["settings"]["membership_level"]  = [];
		foreach($membership as $memb)
		{
			$data["settings"]["membership_level"][$memb->membership_id] = array_fill(0, $memb->membership_cashback_level, "");
		}

		foreach($get as $g)
		{
			$data["settings"]["cashback_settings"][$g->membership_id][$g->membership_level] = $g->membership_cashback_income;
		}
		return $data;

	}

	public static function BOARD()
	{
		$plan 				   = Tbl_mlm_plan::where("mlm_plan_code","BOARD")->first();
		$data["label"]         = Plan::get_label($plan->mlm_plan_code);
		$data["status"]        = $plan->mlm_plan_enable;
		return $data;
	}

	public static function MONOLINE()
    {
        $plan                    = Tbl_mlm_plan::where("mlm_plan_code","MONOLINE")->first();
        $data["label"]         = Plan::get_label($plan->mlm_plan_code);
        $data["status"]        = $plan->mlm_plan_enable;

        $data["settings"]      = [];

        $check_exist = Tbl_mlm_monoline_settings::first();
        if(!$check_exist)
        {
            $settings["membership_id"]              = 0;
            $settings["max_price"]                  = 0;
            $settings["monoline_percent"]           = 0;
            Tbl_mlm_monoline_settings::insert($settings);
        }

        // $data["settings"]["monoline_settings"]                = Tbl_mlm_monoline_settings::get();
        $null_array = array('mlm_monoline_settings_id'=>0,'membership_id'=>0,'monoline_percent'=>0,'max_price'=>0);

        $_membership                                          = Tbl_membership::where("archive",0)->get();


        foreach($_membership as $key => $membership)
        {
            $check= Tbl_mlm_monoline_settings::where("membership_id",$membership["membership_id"])->first();

            if ($check)
            {
                $_membership[$key]["monoline_settings"] = $check;

            }
            else
            {
                $_membership[$key]["monoline_settings"] = $null_array;

            }
            $data["settings"] = $_membership;
        }
        return $data;
    }
    public static function PASS_UP()
    {
        $plan                    = Tbl_mlm_plan::where("mlm_plan_code","PASS_UP")->first();
        $data["label"]         = Plan::get_label($plan->mlm_plan_code);
        $data["status"]        = $plan->mlm_plan_enable;

        $data["settings"]      = [];

        $check_exist = Tbl_mlm_pass_up_settings::first();
        if(!$check_exist)
        {
            $settings["membership_id"]              = 0;
            $settings["pass_up"]                  	= 2;
            $settings["pass_up_direction"]          = 1;
            $settings["pass_up_amount"]          	= 0;
            $settings["direct"]                  	= 4;
            $settings["direct_direction"]           = 0;
            $settings["direct_amount"]          	= 0;
            Tbl_mlm_pass_up_settings::insert($settings);
        }

        // $data["settings"]["pass_up_settings"]                = Tbl_mlm_pass_up_settings::get();
        $null_array = array('pass_up_settings_id'=>0,'membership_id'=>0,'pass_up'=>2,'pass_up_direction'=>1,'pass_up_amount'=>0,'direct'=>4,'direct_direction'=>0,'direct_amount'=>0);

        $_membership                                          = Tbl_membership::where("archive",0)->get();


        foreach($_membership as $key => $membership)
        {
            $check= Tbl_mlm_pass_up_settings::where("membership_id",$membership["membership_id"])->first();

            if ($check)
            {
                $_membership[$key]["pass_up_settings"] = $check;

            }
            else
            {
                $_membership[$key]["pass_up_settings"] = $null_array;

            }
            $data["settings"] = $_membership;
        }
        return $data;
    }
	public static function PASS_UP_COMBINATIONS()
	{
		$data["settings"]      = [];


		$data["settings"]["pass_up_settings"] = [];
		$data["settings"]["pass_up_settings2"] = [];

		$get                   = Tbl_membership::where("archive",0)->get();
		foreach($get as $g)
		{
			foreach($get as $g2)
			{
				$check = Tbl_pass_up_combination_income::where("membership_id",$g->membership_id)->where("membership_entry_id",$g2->membership_id)->first();
				if($check)
				{
					$data["settings"]["pass_up_settings"][$g->membership_id][$g2->membership_id] = $check->pass_up_income;
				}
				else
				{
					$data["settings"]["pass_up_settings"][$g->membership_id][$g2->membership_id] = 0;
				}
				$check2 = Tbl_pass_up_direct_combination_income::where("membership_id",$g->membership_id)->where("membership_entry_id",$g2->membership_id)->first();
				if($check2)
				{
					$data["settings"]["pass_up_settings2"][$g->membership_id][$g2->membership_id] = $check2->pass_up_direct_income;
				}
				else
				{
					$data["settings"]["pass_up_settings2"][$g->membership_id][$g2->membership_id] = 0;
				}
			}
		}

		return $data;
	}
    public static function LEVELING_BONUS()
    {
    	$plan                    = Tbl_mlm_plan::where("mlm_plan_code","LEVELING_BONUS")->first();
        $data["label"]         = Plan::get_label($plan->mlm_plan_code);
        $data["status"]        = $plan->mlm_plan_enable;

        $data["settings"]      = [];

       	$get                   = Tbl_membership_leveling_bonus_level::get();
		$membership            = Tbl_membership::where("archive",0)->get();

		$data["settings"]["leveling_bonus_settings"] = [];
		$data["settings"]["membership_level"]  = [];
		foreach($membership as $memb)
		{
			$data["settings"]["membership_level"][$memb->membership_id] = array_fill(0, $memb->membership_leveling_bonus_level, "");
		}

		foreach($get as $g)
		{
			$data["settings"]["leveling_bonus_settings"][$g->membership_id][$g->membership_level] = $g->membership_leveling_bonus_income;
		}
		return $data;

    }

    public static function UNILEVEL_OR()
	{

		$plan 				   = Tbl_mlm_plan::where("mlm_plan_code","UNILEVEL_OR")->first();
		$data["label"]         = Plan::get_label($plan->mlm_plan_code);
		$data["status"]        = $plan->mlm_plan_enable;

		$data["settings"]      = [];

		$get                   = Tbl_membership_unilevel_or_level::get();
		$membership            = Tbl_membership::where("archive",0)->get();

		$data["settings"]["unilevel_or_settings"] = [];
		$data["settings"]["membership_level"]  = [];


		foreach($membership as $memb)
		{
			$data["settings"]["membership_level"][$memb->membership_id] = array_fill(0, $memb->membership_unilevel_or_level, "");
		}

		foreach($get as $g)
		{
			$data["settings"]["unilevel_or_settings"][$g->membership_id][$g->membership_level] = $g->membership_percentage;
		}
		return $data;
	}
	public static function UNIVERSAL_POOL_BONUS()
    {
        $plan                  = Tbl_mlm_plan::where("mlm_plan_code","UNIVERSAL_POOL_BONUS")->first();
        $data["label"]         = Plan::get_label($plan->mlm_plan_code);
		$data["status"]        = $plan->mlm_plan_enable;


		$data["settings"]          = [];
		//check if have maintain setting for univeral pool
		$maintain_settings_check = Tbl_mlm_universal_pool_bonus_maintain_settings::first();
		if(!$maintain_settings_check)
        {
            $maintain_settings["required_direct"]         = 0;
			$maintain_settings["maintain_date"]           = "disable";
			$maintain_settings["binary_maintenace"]       = 0;
			Tbl_mlm_universal_pool_bonus_maintain_settings::insert($maintain_settings);
		}
		$universal_maintain = Tbl_mlm_universal_pool_bonus_maintain_settings::first();

		// //check if have univeral pool membership settings
        // $check_exist = Tbl_mlm_universal_pool_bonus_settings::first();
        // if(!$check_exist)
        // {
        //     $settings["membership_id"]              = 0;
        //     $settings["max_price"]                  = 0;
        //     $settings["percent"]           			= 0;
        //     Tbl_mlm_universal_pool_bonus_settings::insert($settings);
        // }

        // $data["settings"]["monoline_settings"]                = Tbl_mlm_monoline_settings::get();
        $null_array = array('universal_pool_bonus_id'=>0,'membership_id'=>0,'max_price'=>0,'percent'=>0);

        $_membership                                          = Tbl_membership::where("archive",0)->get();


        foreach($_membership as $key => $membership)
        {
            $check= Tbl_mlm_universal_pool_bonus_settings::where("membership_id",$membership["membership_id"])->first();

            if ($check)
            {
                $data["settings"]["universal_pool_bonus_settings"][$key] = $check;

            }
            else
            {
				$settings["membership_id"]              = $membership["membership_id"];
				$settings["max_price"]                  = 0;
				$settings["percent"]           			= 0;
				$settings["required_direct"]           	= 0;
				Tbl_mlm_universal_pool_bonus_settings::insert($settings);
				$check2 = Tbl_mlm_universal_pool_bonus_settings::where("membership_id",$membership["membership_id"])->first();
                $data["settings"] ["universal_pool_bonus_settings"][$key] = $check2;
            }
            // $data["settings"] = $_membership;
		}
		$data["settings"]["maintain_settings"] = $universal_maintain;
		// dd($data);
        return $data;
	}
	public static function INCENTIVE_BONUS()
	{
		$plan                  = Tbl_mlm_plan::where("mlm_plan_code","INCENTIVE_BONUS")->first();
        $data["label"]         = Plan::get_label($plan->mlm_plan_code);
		$data["status"]        = $plan->mlm_plan_enable;
        $check_exist = Tbl_mlm_incentive_bonus::first();
        if(!$check_exist)
        {
			$settings["incentives_status"]          = 0;
            Tbl_mlm_incentive_bonus::insert($settings);
		}
		$incentives = Tbl_mlm_incentive_bonus::first();
		$data['settings']	    = $incentives->incentives_status;
		return $data;
	}
    public static function BINARY_REPURCHASE()
    {
    	$plan                  = Tbl_mlm_plan::where("mlm_plan_code","BINARY_REPURCHASE")->first();
        $data["label"]         = Plan::get_label($plan->mlm_plan_code);
        $data["status"]        = $plan->mlm_plan_enable;
		return $data;
	}

	public static function MEMBERSHIP_UPGRADE()
	{
		$plan                  = Tbl_mlm_plan::where("mlm_plan_code","MEMBERSHIP_UPGRADE")->first();
		$data["label"]         = Plan::get_label($plan->mlm_plan_code);
		$data["status"]        = $plan->mlm_plan_enable;
		$check                 = Tbl_membership_upgrade_settings::first();
		if(!$check)
		{
			$insert["membership_upgrade_settings_method"] = "direct_downlines";
			Tbl_membership_upgrade_settings::insert($insert);
		}
		$data["settings"] = Tbl_membership_upgrade_settings::first();
		return $data;
	}
	public static function SIGN_UP_BONUS()
	{
		$plan                  = Tbl_mlm_plan::where("mlm_plan_code","SIGN_UP_BONUS")->first();
		$data["label"]         = Plan::get_label($plan->mlm_plan_code);
		$data["status"]        = $plan->mlm_plan_enable;
		return $data;
	}
	public static function GLOBAL_POOL_BONUS()
    {
    	$plan                  = Tbl_mlm_plan::where("mlm_plan_code","GLOBAL_POOL_BONUS")->first();
        $data["label"]         = Plan::get_label($plan->mlm_plan_code);
		$data["status"]        = $plan->mlm_plan_enable;
		$check                 = Tbl_global_pool_bonus_settings::first();
		$data["settings"]      = [];
		if(!$check)
		{
			$insert["global_pool_amount"] = 0;
			Tbl_global_pool_bonus_settings::insert($insert);
		}
		$data["settings"]["amount"]     = Tbl_global_pool_bonus_settings::first()->global_pool_amount;
		$_membership                    = Tbl_membership::where("archive",0)->orderby("hierarchy","ASC")->get();
		foreach ($_membership as $key => $value) 
		{
			$data["settings"]["membership"][$key] = $value;
		}
		return $data;
	}
	public static function PERSONAL_CASHBACK()
	{
		$plan                  = Tbl_mlm_plan::where("mlm_plan_code","PERSONAL_CASHBACK")->first();
        $data["label"]         = Plan::get_label($plan->mlm_plan_code);
		$data["status"]        = $plan->mlm_plan_enable;
		return $data;
	}
	public static function SHARE_LINK()
	{
		$plan                  = Tbl_mlm_plan::where("mlm_plan_code","SHARE_LINK")->first();
        $data["label"]         = Plan::get_label($plan->mlm_plan_code);
		$data["status"]        = $plan->mlm_plan_enable;

		$check                 = Tbl_share_link_settings::first();

		if(!$check)
		{
			$insert["share_link_maximum_income"] = 0;
			$insert["share_link_maximum_register_per_day"] = 0;
			$insert["share_link_income_per_registration"] = 0;
			Tbl_share_link_settings::insert($insert);
		}
		$data["settings"]      = Tbl_share_link_settings::first();
				
		return $data;
	}
	public static function SHARE_LINK_V2()
	{
		$plan                  = Tbl_mlm_plan::where("mlm_plan_code","SHARE_LINK_V2")->first();
        $data["label"]         = Plan::get_label($plan->mlm_plan_code);
		$data["status"]        = $plan->mlm_plan_enable;
		return $data;
	}

	public static function WATCH_EARN()
	{
		$plan                  = Tbl_mlm_plan::where("mlm_plan_code","WATCH_EARN")->first();
        $data["label"]         = Plan::get_label($plan->mlm_plan_code);
		$data["status"]        = $plan->mlm_plan_enable;
		$data["settings"]      = [];
		$check                 = Tbl_watch_earn_settings::first();
		if(!$check)
		{
			$insert["watch_earn_maximum_amount"] = 0;
			$insert["watch_earn_video_amount"] = 0;
			$insert["watch_earn_video_max"] = 0;
			Tbl_watch_earn_settings::insert($insert);
		}
		$data["settings"]['settings']   = Tbl_watch_earn_settings::first();
		// dd($data);	
		return $data;
	}
	public static function PASSIVE_UNILEVEL_PREMIUM()
    {
        $plan                    = Tbl_mlm_plan::where("mlm_plan_code","PASSIVE_UNILEVEL_PREMIUM")->first();
        $data["label"]         = Plan::get_label($plan->mlm_plan_code);
        $data["status"]        = $plan->mlm_plan_enable;
        $data["settings"]      = [];
        $check_exist = Tbl_passive_unilevel_premium::first();
        if(!$check_exist)
        {
            $settings["premium_membership_id"]              = 0;
            $settings["premium_upline"]                  	= 0;
            $settings["premium_downline"]          			= 0;
            $settings["premium_percentage"]          		= 0;
            $settings["premium_is_enable"]                  = 0;
            $settings["premium_earning_limit"]           	= 0;
            $settings["premium_earning_cycle"]          	= 0;
            $settings["premium_date_created"]          		= Carbon::now();
            Tbl_passive_unilevel_premium::insert($settings);
        }
        // $data["settings"]["pass_up_settings"]                = Tbl_mlm_pass_up_settings::get();
        $null_array = array('premium_id'=>0,'premium_membership_id'=>0,'premium_upline'=>0,'premium_downline'=>0,'premium_percentage'=>0,'premium_is_enable'=>0,'premium_earning_limit'=>0,'premium_earning_cycle'=>0);
        $_membership                                          = Tbl_membership::where("archive",0)->get();
        foreach($_membership as $key => $membership)
        {
            $check= Tbl_passive_unilevel_premium::where("premium_membership_id",$membership["membership_id"])->first();
            if ($check)
            {
                $_membership[$key]["premium_settings"] = $check;
            }
            else
            {
                $_membership[$key]["premium_settings"] = $null_array;
            }
            $data["settings"] = $_membership;
        }
        return $data;
	}
	
	public static function RETAILER_COMMISSION()
	{
		$plan                  = Tbl_mlm_plan::where("mlm_plan_code","RETAILER_COMMISSION")->first();
        $data["label"]         = Plan::get_label($plan->mlm_plan_code);
		$data["status"]        = $plan->mlm_plan_enable;
		return $data;
	}
	public static function PRODUCT_SHARE_LINK()
	{
		$plan 				   = Tbl_mlm_plan::where("mlm_plan_code","PRODUCT_SHARE_LINK")->first();
		$data["label"]         = Plan::get_label($plan->mlm_plan_code);
		$data["status"]        = $plan->mlm_plan_enable;

		$data["settings"]      = [];
		$get                   = Tbl_membership_product_level::get();
		$membership            = Tbl_membership::where("archive",0)->get();

		$data["settings"]["product_share_link_settings"] = [];
		$data["settings"]["membership_level"]  = [];

		foreach($membership as $memb)
		{
			$data["settings"]["membership_level"][$memb->membership_id] = array_fill(0, $memb->membership_product_share_link_level, "");
		}

		foreach($membership as $memb)
		{
			foreach($membership as $memb2)
			{
				$membership_product_share_link_level = $memb->membership_product_share_link_level;
				for($level = 1; $level <= $membership_product_share_link_level ; $level++)
				{
					$percent_value = Tbl_membership_product_level::where("membership_id",$memb->membership_id)->where("membership_entry_id",$memb2->membership_id)->where("membership_level",$level)->first();
					$percent_value = $percent_value ? $percent_value->membership_product_income : 0;

					$data["settings"]["product_share_link_settings"][$memb->membership_id][$memb2->membership_id][$level] = $percent_value;
				}
			}
		}
		return $data;
	}
	public static function OVERRIDING_COMMISSION()
	{
		$plan 				   = Tbl_mlm_plan::where("mlm_plan_code","OVERRIDING_COMMISSION")->first();
		$data["label"]         = Plan::get_label($plan->mlm_plan_code);
		$data["status"]        = $plan->mlm_plan_enable;

		$data["settings"]      = [];
		$get                   = Tbl_membership_overriding_commission_level::get();
		$membership            = Tbl_membership::where("archive",0)->get();

		$data["settings"]["overriding_commission_settings"] = [];
		$data["settings"]["membership_level"]  = [];
		
		foreach($membership as $memb)
		{
			$data["settings"]["membership_level"][$memb->membership_id] = array_fill(0, $memb->membership_overriding_commission_level, "");
		}

		foreach($membership as $memb)
		{
			foreach($membership as $memb2)
			{
				$membership_overriding_commission_level = $memb->membership_overriding_commission_level;
				for($level = 1; $level <= $membership_overriding_commission_level ; $level++)
				{
					$percent_value = Tbl_membership_overriding_commission_level::where("membership_id",$memb->membership_id)->where("membership_entry_id",$memb2->membership_id)->where("membership_level",$level)->first();
					$percent_value = $percent_value ? $percent_value->membership_overriding_commission_income : 0;

					$data["settings"]["overriding_commission_settings"][$memb->membership_id][$memb2->membership_id][$level] = $percent_value;
				}
			}
		}
		return $data;
	}
	public static function PRODUCT_DIRECT_REFERRAL()
    {
    	$plan                  = Tbl_mlm_plan::where("mlm_plan_code","PRODUCT_DIRECT_REFERRAL")->first();
        $data["label"]         = Plan::get_label($plan->mlm_plan_code);
        $data["status"]        = $plan->mlm_plan_enable;
		return $data;
	}
	public static function DIRECT_PERSONAL_CASHBACK()
	{
		$plan                  = Tbl_mlm_plan::where("mlm_plan_code","DIRECT_PERSONAL_CASHBACK")->first();
        $data["label"]         = Plan::get_label($plan->mlm_plan_code);
		$data["status"]        = $plan->mlm_plan_enable;
		
		return $data;
	}
	public static function PRODUCT_PERSONAL_CASHBACK()
    {
    	$plan                  = Tbl_mlm_plan::where("mlm_plan_code","PRODUCT_PERSONAL_CASHBACK")->first();
        $data["label"]         = Plan::get_label($plan->mlm_plan_code);
        $data["status"]        = $plan->mlm_plan_enable;
		return $data;
	}
	public static function PRODUCT_DOWNLINE_DISCOUNT()
    {
    	$plan                  = Tbl_mlm_plan::where("mlm_plan_code","PRODUCT_DOWNLINE_DISCOUNT")->first();
        $data["label"]         = Plan::get_label($plan->mlm_plan_code);
        $data["status"]        = $plan->mlm_plan_enable;
		return $data;
	}
	public static function REFERRAL_VOUCHER()
    {
        $plan                    									= Tbl_mlm_plan::where("mlm_plan_code","REFERRAL_VOUCHER")->first();
        $data["label"]         	 									= Plan::get_label($plan->mlm_plan_code);
        $data["status"]        	 									= $plan->mlm_plan_enable;

        $data["settings"]      	 									= [];

        $null_array = array('id'=>0,'membership_id'=>0,'referrer_income'=>0,'referee_income'=>0);

        $_membership                                          		= Tbl_membership::where("archive",0)->get();


        foreach($_membership as $key => $membership)
        {
            $check= Tbl_referral_voucher_settings::where("membership_id",$membership["membership_id"])->first();

            if ($check)
            {
                $_membership[$key]["referral_voucher_settings"] 				= $check;

            }
            else
            {
                $_membership[$key]["referral_voucher_settings"] 				= $null_array;

            }
            $data["settings"] 													= $_membership;
        }
        return $data;
    }
	public static function OVERRIDING_COMMISSION_V2()
	{

		$plan 				   = Tbl_mlm_plan::where("mlm_plan_code","OVERRIDING_COMMISSION_V2")->first();
		$data["label"]         = Plan::get_label($plan->mlm_plan_code);
		$data["status"]        = $plan->mlm_plan_enable;

		$data["settings"]      = [];


		$data["settings"]["overriding_settings"] = [];

		$get                   = Tbl_membership::where("archive",0)->get();
		foreach($get as $g)
		{
			foreach($get as $g2)
			{
				$check = Tbl_overriding_commission_v2::where("membership_id",$g->membership_id)->where("membership_entry_id",$g2->membership_id)->first();
				if($check)
				{
					$data["settings"]["overriding_settings"][$g->membership_id][$g2->membership_id] = $check->income;
				}
				else
				{
					$data["settings"]["overriding_settings"][$g->membership_id][$g2->membership_id] = 0;
				}				
			}
		}	
		return $data;
	}
	public static function TEAM_SALES_BONUS()
    {
    	$plan                  = Tbl_mlm_plan::where("mlm_plan_code","TEAM_SALES_BONUS")->first();
        $data["label"]         = Plan::get_label($plan->mlm_plan_code);
		$data["trigger"]       = Plan::get_trigger($plan->mlm_plan_code);
        $data["status"]        = $plan->mlm_plan_enable;

		$data["settings"]      = [];
        $_membership           = Tbl_membership::where("archive",0)->get();

        foreach($_membership as $key => $membership)
        {
            $data["settings"][$key]["team_sales_bonus_settings"] = Tbl_membership::where('membership_id',$membership['membership_id'])->where("archive",0)->select('membership_id','team_sales_bonus_level')->first();
        }
        return $data;
	}
	public static function OVERRIDING_BONUS()
    {
    	$plan                  = Tbl_mlm_plan::where("mlm_plan_code","OVERRIDING_BONUS")->first();
        $data["label"]         = Plan::get_label($plan->mlm_plan_code);
        $data["status"]        = $plan->mlm_plan_enable;
		return $data;
	}	
	public static function RETAILER_OVERRIDE()
    {
    	$plan                  = Tbl_mlm_plan::where("mlm_plan_code","RETAILER_OVERRIDE")->first();
        $data["label"]         = Plan::get_label($plan->mlm_plan_code);
        $data["status"]        = $plan->mlm_plan_enable;
		return $data;
	}	
	public static function REVERSE_PASS_UP()
    {
        $plan                  = Tbl_mlm_plan::where("mlm_plan_code","REVERSE_PASS_UP")->first();
        $data["label"]         = Plan::get_label($plan->mlm_plan_code);
        $data["status"]        = $plan->mlm_plan_enable;

        $data["settings"]      = [];

        $check_exist = Tbl_reverse_pass_up_settings::first();
        if(!$check_exist)
        {
            $settings["membership_id"]              = 0;
            $settings["pass_up"]                  	= 2;
            $settings["pass_up_direction"]          = 1;
            $settings["pass_up_amount"]          	= 0;
            $settings["direct"]                  	= 4;
            $settings["direct_direction"]           = 0;
            $settings["direct_amount"]          	= 0;
            Tbl_reverse_pass_up_settings::insert($settings);
        }

        // $data["settings"]["pass_up_settings"]                = Tbl_reverse_pass_up_settings::get();
        $null_array = array('pass_up_settings_id'=>0,'membership_id'=>0,'pass_up'=>2,'pass_up_direction'=>1,'pass_up_amount'=>0,'direct'=>4,'direct_direction'=>0,'direct_amount'=>0);

        $_membership                                          = Tbl_membership::where("archive",0)->get();


        foreach($_membership as $key => $membership)
        {
            $check= Tbl_reverse_pass_up_settings::where("membership_id",$membership["membership_id"])->first();

            if ($check)
            {
                $_membership[$key]["pass_up_settings"] = $check;

            }
            else
            {
                $_membership[$key]["pass_up_settings"] = $null_array;

            }
            $data["settings"] = $_membership;
        }
        return $data;
    }
	public static function REVERSE_PASS_UP_COMBINATIONS()
	{
		$data["settings"]      = [];


		$data["settings"]["pass_up_settings"] = [];
		$data["settings"]["pass_up_settings2"] = [];

		$get                   = Tbl_membership::where("archive",0)->get();
		foreach($get as $g)
		{
			foreach($get as $g2)
			{
				$check = Tbl_reverse_pass_up_combination_income::where("membership_id",$g->membership_id)->where("membership_entry_id",$g2->membership_id)->first();
				if($check)
				{
					$data["settings"]["pass_up_settings"][$g->membership_id][$g2->membership_id] = $check->pass_up_income;
				}
				else
				{
					$data["settings"]["pass_up_settings"][$g->membership_id][$g2->membership_id] = 0;
				}
				$check2 = Tbl_reverse_pass_up_direct_combination_income::where("membership_id",$g->membership_id)->where("membership_entry_id",$g2->membership_id)->first();
				if($check2)
				{
					$data["settings"]["pass_up_settings2"][$g->membership_id][$g2->membership_id] = $check2->pass_up_direct_income;
				}
				else
				{
					$data["settings"]["pass_up_settings2"][$g->membership_id][$g2->membership_id] = 0;
				}
			}
		}

		return $data;
	}

	// Created By: Centy - 10-27-2023
	public static function ACHIEVERS_RANK()
    {
    	$plan                  = Tbl_mlm_plan::where("mlm_plan_code","ACHIEVERS_RANK")->first();
		$data["settings"]      = [];
		$data["settings"]["achievers_rank_settings"]  = [];
		$data["settings"]["achievers_rank_settings_end"]  = (object)array("achievers_rank_name"=>"","achievers_rank_binary_points_left"=>"","achievers_rank_binary_points_right"=>"","achievers_rank_reward"=>"");
		$achievers_rank    = Tbl_achievers_rank::where("archive",0)
											   ->select("achievers_rank_id","achievers_rank_level","achievers_rank_name","achievers_rank_binary_points_left","achievers_rank_binary_points_right","achievers_rank_reward","achievers_rank_date_created")
											   ->get();

		$array = array();
		foreach($achievers_rank as $srank)
		{
			array_push($array,$srank);
		}
		
		$data["settings"]["achievers_rank_settings"]      = $array;
		$data["settings"]["count_achievers_rank_settings"] = count($array);
        $data["label"]         = Plan::get_label($plan->mlm_plan_code);
        $data["status"]        = $plan->mlm_plan_enable;
		return $data;
	}
	public static function DROPSHIPPING_BONUS()
    {
    	$plan                  = Tbl_mlm_plan::where("mlm_plan_code","DROPSHIPPING_BONUS")->first();
        $data["label"]         = Plan::get_label($plan->mlm_plan_code);
        $data["status"]        = $plan->mlm_plan_enable;
		return $data;
	}
	public static function WELCOME_BONUS()
    {
    	$plan = Tbl_mlm_plan::where("mlm_plan_code","WELCOME_BONUS")->first();
		$data["settings"] = [];
		$data["settings"]["commission"] = [];
		$membership_list = Tbl_membership::where('archive', 0)->get();
		
		foreach($membership_list as $membership) {
			$commissions = Tbl_welcome_bonus_commissions::where('membership_id', $membership->membership_id)->first();
			if(!$commissions) {
				$insert["membership_id"] = $membership->membership_id;
				$insert["commission"] = 0;
				Tbl_welcome_bonus_commissions::insert($insert);
			} 
		}
		foreach (Tbl_welcome_bonus_commissions::get() as $settings) {
			$data["settings"]["commission"][$settings->membership_id] = $settings;
		}
        $data["label"]         = Plan::get_label($plan->mlm_plan_code);
        $data["status"]        = $plan->mlm_plan_enable;
		return $data;
	}

	public static function UNILEVEL_MATRIX_BONUS()
    {
    	$plan = Tbl_mlm_plan::where("mlm_plan_code","UNILEVEL_MATRIX_BONUS")->first();
        $data["label"] = Plan::get_label($plan->mlm_plan_code);
        $data["status"] = $plan->mlm_plan_enable;
		$data["settings"] = [];
		$data["settings"]["setup"] = [];
		$data["settings"]["membership_level"] = [];
		$data["settings"]["unilevel_matrix_bonus_settings"] = [];
		$settings = Tbl_unilevel_matrix_bonus_settings::first();
		$membership = Tbl_membership::where("archive",0)->get();
		foreach($membership as $memb)
		{
			$data["settings"]["membership_level"][$memb->membership_id] = array_fill(0, $memb->unilevel_matrix_level, "");
		}
		foreach($membership as $memb) {
			if($memb->unilevel_matrix_level) {
				foreach($membership as $memb2) {
					for($level = 1; $level <= $memb->unilevel_matrix_level; $level++) {

						$commission_settings = Tbl_unilevel_matrix_bonus_levels::where("membership_id",$memb->membership_id)->where("membership_entry_id",$memb2->membership_id)->where("level",$level)->first();
						$commission = $commission_settings ? $commission_settings->matrix_commission : 0;
						$data["settings"]["unilevel_matrix_bonus_settings"][$memb->membership_id][$memb2->membership_id][$level] = $commission;
					}
				}
			}
		}
		$data["settings"]["setup"] = $settings;
		return $data;
	}

	public static function LIVEWELL_RANK()
    {
    	$plan = Tbl_mlm_plan::where("mlm_plan_code","LIVEWELL_RANK")->first();
		$data["settings"] = [];
		$data["settings"]["rank_settings"]  = [];
		
		$livewell_rank = Tbl_livewell_rank::where("archive",0)->get();

		$array = array();
		foreach($livewell_rank as $srank)
		{
			array_push($array,$srank);
		}
		
		$data["settings"]["rank_settings"]      = $array;
		$data["settings"]["count_rank_settings"] = count($array);
        $data["label"] = Plan::get_label($plan->mlm_plan_code);
        $data["status"] = $plan->mlm_plan_enable;
		return $data;
	}

	public static function REWARD_POINTS()
    {
    	$plan = Tbl_mlm_plan::where("mlm_plan_code","REWARD_POINTS")->first();
		$data["settings"] = [];

        $data["label"] = Plan::get_label($plan->mlm_plan_code);
        $data["status"] = $plan->mlm_plan_enable;
		return $data;
	}

	public static function PRIME_REFUND()
    {
    	$plan = Tbl_mlm_plan::where("mlm_plan_code","PRIME_REFUND")->first();
		$data["settings"] = [];

		$data["settings"]["setup"] = [];
		$data["settings"]["prime_refund_points_label"] = Plan::get_label("PRIME_REFUND_POINTS");

		$prime_refund_exists = Tbl_prime_refund_setup::exists();

		$memberships = Tbl_membership::where("archive", 0)->pluck("membership_id");

		if (!$prime_refund_exists) {
			$insertData = [];

			foreach ($memberships as $memb) {
				foreach ($memberships as $memb2) {
					$insertData[] = [
						"membership_id" => $memb,
						"membership_entry_id" => $memb2,
						"prime_refund_points" => 0,
					];
				}
			}

			if (!empty($insertData)) {
				Tbl_prime_refund_setup::insert($insertData);
			}
		} else {
			
			$insertNewMembership = [];
			foreach ($memberships as $memb) {
				foreach ($memberships as $memb2) {
					$isExist = Tbl_prime_refund_setup::where("membership_id", $memb)->where("membership_entry_id", $memb2)->exists();
					if (!$isExist) {
						$insertNewMembership[] = [
							"membership_id" => $memb,
							"membership_entry_id" => $memb2,
							"prime_refund_points" => 0,
						];
					}
				}
			}

			if (!empty($insertNewMembership)) {
				Tbl_prime_refund_setup::insert($insertNewMembership);
			}
		}


		$prime_refund = Tbl_prime_refund_setup::get();
		foreach ($prime_refund as $setup) {
			
			$data["settings"]["setup"][$setup->membership_id][$setup->membership_entry_id] = $setup["prime_refund_points"];
		}

        $data["label"] = Plan::get_label($plan->mlm_plan_code);
        $data["status"] = $plan->mlm_plan_enable;
		return $data;
	}

	public static function INCENTIVE()
    {
    	$plan = Tbl_mlm_plan::where("mlm_plan_code","INCENTIVE")->first();
		$data["settings"] = [];
		$data["settings"]["setup"] = [];
		if (!Tbl_incentive_setup::where("archive", 0)->exists()) {
			$firstItem = Tbl_item::where("archived", 0)->where("item_type", "product")->first();
			if ($firstItem) {
				Tbl_incentive_setup::insert([
					"number_of_purchase" => 1,
				]);
			}
		}
		
		$data["settings"]["setup"] = Tbl_incentive_setup::where("archive", 0)->get();
		

        $data["label"] = Plan::get_label($plan->mlm_plan_code);
        $data["status"] = $plan->mlm_plan_enable;
		return $data;
	}

	public static function MILESTONE_BONUS()
    {
    	$plan = Tbl_mlm_plan::where("mlm_plan_code","MILESTONE_BONUS")->first();
		$data["settings"] = [];
		$data["settings"]["settings"] = [];
		$data["settings"]["points_setup"] = [];
		$settingsExists = Tbl_milestone_bonus_settings::exists();
		if(!$settingsExists) {
			$insertData = [
				"milestone_type_limit" => 'pairs',
				"milestone_cycle_limit" => null,
				"created_at" => Carbon::now(),
			];
			Tbl_milestone_bonus_settings::insert($insertData);
		}
		$data["settings"]["settings"] = Tbl_milestone_bonus_settings::first();
		
		$membership_list = Tbl_membership::where("archive",0)->get();
		foreach($membership_list as $membership) {
			foreach($membership_list as $membership2) {
				$setup = Tbl_milestone_points_setup::where("membership_id", $membership->membership_id)
					->where("membership_entry_id", $membership2->membership_id)
					->first();
				if($setup) {
					$data["settings"]["points_setup"][$membership->membership_id][$membership2->membership_id] = $setup->milestone_points;
				} else {
					$data["settings"]["points_setup"][$membership->membership_id][$membership2->membership_id] = 0;
				}
			}
		}

		$milestone_pairing = Tbl_milestone_pairing_points_setup::where("archive",0)->get();
		$array = array();
		foreach($milestone_pairing as $key => $points_setup)
		{
			$milestone_pairing[$key]->membership_id = $milestone_pairing[$key]->membership_id == null ? 0 : $milestone_pairing[$key]->membership_id;
			array_push($array, $points_setup);
		}
		$data["settings"]["pairing_setup"] = $array;

		$data["settings"]["points_label"] = [
			'milestone_points_left' => Plan::get_label("MILESTONE_POINTS_LEFT"),
			'milestone_points_right' => Plan::get_label("MILESTONE_POINTS_RIGHT"),
		];
        $data["label"] = Plan::get_label($plan->mlm_plan_code);
        $data["status"] = $plan->mlm_plan_enable;
		return $data;
	}

	public static function INFINITY_BONUS()
	{
		$plan = Tbl_mlm_plan::where("mlm_plan_code", "INFINITY_BONUS")->first();

		$data["settings"] = [];
		$data["settings"]["infinity_bonus_settings"] = [];
		$data["settings"]["membership_level"]  = [];

		$membership_list = Tbl_membership::where("archive", 0)->get();
		foreach($membership_list as $membership) {
			$data["settings"]["membership_level"][$membership->membership_id] = array_fill(0, $membership->infinity_bonus_level, "");
		}

		foreach ($membership_list as $membership) {
			foreach ($membership_list as $membership_entry) {
				$infinity_bonus_level = $membership->infinity_bonus_level;
				for ($level = 1; $level <= $infinity_bonus_level; $level++) {
					$percent_value = Tbl_infinity_bonus_setup::where("membership_id", $membership->membership_id)
						->where("membership_entry_id", $membership_entry->membership_id)
						->where("level", $level)
						->value("percentage") ?? 0;

					$data["settings"]["infinity_bonus_settings"][$membership->membership_id][$membership_entry->membership_id][$level] = $percent_value;
				}
			}
		}
		$data["label"] = Plan::get_label($plan->mlm_plan_code);
		$data["status"] = $plan->mlm_plan_enable;
		return $data;
	}
	
	public static function MARKETING_SUPPORT()
	{
		$plan = Tbl_mlm_plan::where("mlm_plan_code", "MARKETING_SUPPORT")->first();

		$data["settings"] = [];
		$data["settings"]["setup"] = [];
		$data["settings"]["settings"] = [];
		
		$settingsExists = Tbl_marketing_support_settings::exists();
		if(!$settingsExists) {
			$insertData = [
				"number_of_days_to_earn" => 1,
				"number_of_income" => 1,
				"created_at" => Carbon::now(),
			];
			Tbl_marketing_support_settings::insert($insertData);
		}
		$data["settings"]["settings"] = Tbl_marketing_support_settings::first();


		$membership_list = Tbl_membership::where("archive",0)->get();
		foreach($membership_list as $membership) {
			foreach($membership_list as $membership2) {
				$setup = Tbl_marketing_support_setup::where("membership_id", $membership->membership_id)
					->where("membership_entry_id", $membership2->membership_id)
					->first();
				if($setup) {
					$data["settings"]["setup"][$membership->membership_id][$membership2->membership_id] = $setup->income;
				} else {
					$data["settings"]["setup"][$membership->membership_id][$membership2->membership_id] = 0;
				}
			}
		}
		$data["label"] = Plan::get_label($plan->mlm_plan_code);
		$data["status"] = $plan->mlm_plan_enable;
		return $data;
	}

	public static function LEADERS_SUPPORT()
	{
		$plan = Tbl_mlm_plan::where("mlm_plan_code", "LEADERS_SUPPORT")->first();

		$data["settings"] = [];
		$data["settings"]["setup"] = [];
		$data["settings"]["settings"] = [];
		
		$settingsExists = Tbl_leaders_support_settings::exists();
		if(!$settingsExists) {
			$insertData = [
				"number_of_days_to_earn" => 1,
				"number_of_income" => 1,
				"created_at" => Carbon::now(),
			];
			Tbl_leaders_support_settings::insert($insertData);
		}
		$data["settings"]["settings"] = Tbl_leaders_support_settings::first();

		$membership_list = Tbl_membership::where("archive",0)->get();
		foreach($membership_list as $membership) {
			foreach($membership_list as $membership2) {
				$setup = Tbl_leaders_support_setup::where("membership_id", $membership->membership_id)
					->where("membership_entry_id", $membership2->membership_id)
					->first();
				if($setup) {
					$data["settings"]["setup"][$membership->membership_id][$membership2->membership_id] = $setup->income;
				} else {
					$data["settings"]["setup"][$membership->membership_id][$membership2->membership_id] = 0;
				}
			}
		}
		$data["label"] = Plan::get_label($plan->mlm_plan_code);
		$data["status"] = $plan->mlm_plan_enable;
		return $data;
	}
}
