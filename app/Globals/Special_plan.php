<?php
namespace App\Globals;

use DB;

use App\Models\Tbl_sponsor_matching;
use App\Models\Tbl_mlm_plan;
use App\Models\Tbl_mlm_slot;
use App\Models\Tbl_membership;
use App\Models\Tbl_tree_sponsor;
use App\Models\Tbl_mlm_lockdown_plan;
use App\Models\Tbl_membership_mentors_level;
use App\Models\Tbl_slot;
use App\Models\Tbl_binary_settings;
use App\Models\Tbl_wallet_log;
use App\Models\Tbl_currency;
use App\Models\Tbl_livewell_rank;

use Carbon\Carbon;

use App\Globals\Wallet;
use App\Globals\Log;
use App\Models\Tbl_infinity_bonus_log;
use App\Models\Tbl_infinity_bonus_setup;

class Special_plan
{
	public static function sponsor_matching($slot_id,$amount,$date_pairing)
	{
        $sponsor_matching_plan = Tbl_mlm_plan::where("mlm_plan_code","SPONSOR_MATCHING_BONUS")->where("mlm_plan_enable",1)->first();
        $binary_settings       = Tbl_binary_settings::first();
        $matching_limit        = $binary_settings->sponsor_matching_limit;

        if($sponsor_matching_plan && $amount != 0)
        {
        	$settings = Tbl_sponsor_matching::first();
        	if($settings)
        	{
        		if($settings->sponsor_matching_percent != 0)
        		{
					$date_today       = Carbon::now();
					// dd($date_today);
        			$lockdown_setting = Tbl_mlm_lockdown_plan::plan()->where("mlm_plan_code","SPONSOR_MATCHING_BONUS")->where("is_lockdown_enabled",1)->first();
                    if($lockdown_setting)
                    {
                        $tree_query 	  = Tbl_tree_sponsor::where("sponsor_parent_id",$slot_id)->child()->membership()->where("maintained_until_date",">=",$date_today)->where("sponsor_level",1)->where("enable_sponsor_matching",1);
                    }
                    else
                    {
                        $tree_query       = Tbl_tree_sponsor::where("sponsor_parent_id",$slot_id)->child()->membership()->where("sponsor_level",1)->where("enable_sponsor_matching",1);
                    }
        			

                    $tree       	  = $tree_query->get();
        			$tree_count 	  = $tree_query->count();

        			if($tree_count != 0)
        			{
        				$percentage      = $amount * ($settings->sponsor_matching_percent/100);
        				$computed_amount = number_format($percentage / $tree_count,2);
        				$details         = "";
                        foreach($tree as $child)
                        {
                            $current_income = Special_plan::sponsor_matching_check_cycle($child->slot_id,$date_pairing);
                            $flushed_out    = 0;
                            $total_income   =  $current_income + $computed_amount;
 
                            if($total_income > $matching_limit && $matching_limit != 0)
                            {   
                                $flushed_out            = $total_income - $matching_limit;
                                $computed_from_flushout = $computed_amount - $flushed_out;

                                $flushed_out            = $computed_from_flushout < 0 ? $computed_amount : $flushed_out;
                                $computed_from_flushout = $computed_from_flushout < 0 ? 0                : $computed_from_flushout;

                                $wallet_log_id = Log::insert_wallet($child->slot_id,$computed_from_flushout,"SPONSOR_MATCHING_BONUS");
                                                 Log::insert_earnings($child->slot_id,$computed_from_flushout,"SPONSOR_MATCHING_BONUS","SLOT PLACEMENT",$slot_id,$details,1);
    
                                Log::flushout_logs($flushed_out,$wallet_log_id);

                            }
                            else
                            {
            					Log::insert_wallet($child->slot_id,$computed_amount,"SPONSOR_MATCHING_BONUS");
                                Log::insert_earnings($child->slot_id,$computed_amount,"SPONSOR_MATCHING_BONUS","SLOT PLACEMENT",$slot_id,$details,1);
                            }
                        }
        			}
        		}
        	}
        }
	}

    public static function mentors_bonus($slot_id,$amount,$date_pairing)
    {
        $binary_settings = Tbl_binary_settings::first();
        $mentors_limit   = $binary_settings->mentors_matching_limit;           
        $slot_placement  = Tbl_slot::where("slot_id",$slot_id)->first();
        $highest_level   = Tbl_membership::orderBy("mentors_level","DESC")->first();
        if($highest_level)
        {
            $get_mentor_tree = Tbl_tree_sponsor::where("sponsor_child_id",$slot_placement->slot_id)->where("sponsor_level","<=",$highest_level->mentors_level)->orderBy("sponsor_level","ASC")->get();

            foreach($get_mentor_tree as $mentor_tree)
            {
                $mentor_slot           = Tbl_slot::where("slot_id",$mentor_tree->sponsor_parent_id)->first();
                $check_mentor_settings = Tbl_membership_mentors_level::where("membership_level",$mentor_tree->sponsor_level)->where("membership_id",$mentor_slot->slot_membership)->first();
                if($check_mentor_settings)
                {
                    $count_direct = Tbl_tree_sponsor::where("sponsor_parent_id",$mentor_slot->slot_id)->where("sponsor_level",1)->count();
                    if($count_direct >= $check_mentor_settings->mentors_direct)
                    {
                        /*LOGS*/
                        // $details = "Paired by Slot ".$slot_info->slot_no;
                        $details = "";
                        if($check_mentor_settings->mentors_bonus != 0)
                        {                                                    
                            $income_mentors = $amount * ($check_mentor_settings->mentors_bonus/100);
                            if($income_mentors != 0)
                            {
                                $current_income = Special_plan::mentors_bonus_check_cycle($mentor_slot->slot_id,$date_pairing);
                                $flushed_out    = 0;
                                $total_income   = $current_income + $income_mentors;

                                if($total_income > $mentors_limit && $mentors_limit != 0)
                                { 
                                    $flushed_out            = $total_income - $mentors_limit;
                                    $computed_from_flushout = $income_mentors - $flushed_out;
                                    $flushed_out            = $computed_from_flushout < 0 ? $income_mentors : $flushed_out;
                                    $computed_from_flushout = $computed_from_flushout < 0 ? 0               : $computed_from_flushout;

                                    $_binary_settings = Tbl_binary_settings::first();
                                    $_proceed = false;
                                    if($_binary_settings->mentors_points_enable == 1)
                                    {
                                        if($_binary_settings->mentors_points_minimum_conversion > 0)
                                        {
                                            $_OLP = Tbl_currency::where("currency_abbreviation","OLP")->first();
                                            if($_OLP)
                                            {
                                                $wallet_log_id = Log::insert_wallet($mentor_slot->slot_id,$income_mentors,"MENTORS_BONUS",$_OLP->currency_id);
                                                                 Log::insert_earnings($mentor_slot->slot_id,$income_mentors,"MENTORS_BONUS","SLOT PLACEMENT",$slot_placement->slot_id,$details,$mentor_tree->sponsor_level,$_OLP->currency_id);

                                                $_OLP_wallet = Tbl_slot::where("slot_id",$mentor_slot->slot_id)->Wallet($_OLP->currency_id)->first();
                                                if ($_OLP_wallet->wallet_amount >= $_binary_settings->mentors_points_minimum_conversion) 
                                                {
                                                    Log::insert_wallet($mentor_slot->slot_id,$_OLP_wallet->wallet_amount * -1,"MENTORS_BONUS_CONVERSION",$_OLP->currency_id);
                                                    Log::insert_earnings($mentor_slot->slot_id,$_OLP_wallet->wallet_amount * -1,"MENTORS_BONUS_CONVERSION","SLOT PLACEMENT",$slot_placement->slot_id,$details,$mentor_tree->sponsor_level,$_OLP->currency_id);

                                                    Log::insert_wallet($mentor_slot->slot_id,$_OLP_wallet->wallet_amount,"ONE_LEG_POINTS_CONVERSION");
                                                    Log::insert_earnings($mentor_slot->slot_id,$_OLP_wallet->wallet_amount,"ONE_LEG_POINTS_CONVERSION","SLOT PLACEMENT",$slot_placement->slot_id,$details,$mentor_tree->sponsor_level);
                                                }
                                            }
                                            else 
                                            {
                                                $_proceed = true;
                                            }
                                        }
                                        else 
                                        {
                                            $_proceed = true;
                                        }
                                    }
                                    else 
                                    {
                                        $_proceed = true;
                                    }
                                    
                                    if($_proceed)
                                    {
                                         $wallet_log_id = Log::insert_wallet($mentor_slot->slot_id,$computed_from_flushout,"MENTORS_BONUS");
                                                          Log::insert_earnings($mentor_slot->slot_id,$computed_from_flushout,"MENTORS_BONUS","SLOT PLACEMENT",$slot_placement->slot_id,$details,$mentor_tree->sponsor_level);
                                    }
                                    
                                    Log::flushout_logs($flushed_out,$wallet_log_id);

                                }
                                else
                                {
                                    $_binary_settings = Tbl_binary_settings::first();
                                    $_proceed = false;
                                    if($_binary_settings->mentors_points_enable == 1)
                                    {
                                        if($_binary_settings->mentors_points_minimum_conversion > 0)
                                        {
                                            $_OLP = Tbl_currency::where("currency_abbreviation","OLP")->first();
                                            if($_OLP)
                                            {
                                                Log::insert_wallet($mentor_slot->slot_id,$income_mentors,"MENTORS_BONUS",$_OLP->currency_id);
                                                Log::insert_earnings($mentor_slot->slot_id,$income_mentors,"MENTORS_BONUS","SLOT PLACEMENT",$slot_placement->slot_id,$details,$mentor_tree->sponsor_level,$_OLP->currency_id);

                                                $_OLP_wallet = Tbl_slot::where("tbl_slot.slot_id",$mentor_slot->slot_id)->Wallet($_OLP->currency_id)->first();
                                                if ($_OLP_wallet->wallet_amount >= $_binary_settings->mentors_points_minimum_conversion) 
                                                {
                                                    Log::insert_wallet($mentor_slot->slot_id,$_OLP_wallet->wallet_amount * -1,"MENTORS_BONUS_CONVERSION",$_OLP->currency_id);
                                                    Log::insert_earnings($mentor_slot->slot_id,$_OLP_wallet->wallet_amount * -1,"MENTORS_BONUS_CONVERSION","SLOT PLACEMENT",$slot_placement->slot_id,$details,$mentor_tree->sponsor_level,$_OLP->currency_id);

                                                    Log::insert_wallet($mentor_slot->slot_id,$_OLP_wallet->wallet_amount,"ONE_LEG_POINTS_CONVERSION");
                                                    Log::insert_earnings($mentor_slot->slot_id,$_OLP_wallet->wallet_amount,"ONE_LEG_POINTS_CONVERSION","SLOT PLACEMENT",$slot_placement->slot_id,$details,$mentor_tree->sponsor_level);
                                                }
                                            }
                                            else 
                                            {
                                                $_proceed = true;
                                            }
                                        }
                                        else 
                                        {
                                            $_proceed = true;
                                        }
                                    }
                                    else 
                                    {
                                        $_proceed = true;
                                    }
                                    
                                    if($_proceed)
                                    {
                                        Log::insert_wallet($mentor_slot->slot_id,$income_mentors,"MENTORS_BONUS");
                                        Log::insert_earnings($mentor_slot->slot_id,$income_mentors,"MENTORS_BONUS","SLOT PLACEMENT",$slot_placement->slot_id,$details,$mentor_tree->sponsor_level);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    public static function sponsor_matching_check_cycle($slot_id,$date_pairing)
    {
        $slot_placement  = Tbl_slot::where("slot_id",$slot_id)->first();
        $total_earned    = 0;
        $binary_settings = Tbl_binary_settings::first();
        $balance         = Tbl_wallet_log::where("wallet_log_slot_id",$slot_id)->where("wallet_log_details","SPONSOR MATCHING BONUS");

        if($binary_settings->sponsor_matching_cycle == 1)
        {
            $balance->where("wallet_log_date_created",">=",$date_pairing->format("Y-m-d 00:00:00"));

            $total_earned = $balance->sum("wallet_log_amount");
        }
        else if($binary_settings->sponsor_matching_cycle == 2)
        {   
            $meridiem  = Carbon::parse($date_pairing)->format("A"); 
            if($meridiem == "AM")
            {   
                $date  = Carbon::parse($date_pairing)->format("Y-m-d 00:00:00");
            }
            else 
            {
                $date  = Carbon::parse($date_pairing)->format("Y-m-d 12:00:00");
            }
            $balance->where("wallet_log_date_created",">=",$date);

            $total_earned = $balance->sum("wallet_log_amount");
        }
        else if($binary_settings->sponsor_matching_cycle == 3)
        {
            $date  = Carbon::parse($date_pairing)->startofweek();
            $balance->where("wallet_log_date_created",">=",$date);

            $total_earned = $balance->sum("wallet_log_amount");
        }
        


        return $total_earned;
    }

    public static function mentors_bonus_check_cycle($slot_id,$date_pairing)
    {
        $slot_placement  = Tbl_slot::where("slot_id",$slot_id)->first();
        $total_earned    = 0;
        $binary_settings = Tbl_binary_settings::first();
        $balance         = Tbl_wallet_log::where("wallet_log_slot_id",$slot_id)->where("wallet_log_details","MENTORS BONUS");

        if($binary_settings->sponsor_matching_cycle == 1)
        {
            $balance->where("wallet_log_date_created",">=",$date_pairing->format("Y-m-d 00:00:00"));

            $total_earned = $balance->sum("wallet_log_amount");
        }
        else if($binary_settings->sponsor_matching_cycle == 2)
        {   
            $meridiem  = Carbon::parse($date_pairing)->format("A"); 
            if($meridiem == "AM")
            {   
                $date  = Carbon::parse($date_pairing)->format("Y-m-d 00:00:00");
            }
            else 
            {
                $date  = Carbon::parse($date_pairing)->format("Y-m-d 12:00:00");
            }
            $balance->where("wallet_log_date_created",">=",$date);

            $total_earned = $balance->sum("wallet_log_amount");
        }
        else if($binary_settings->sponsor_matching_cycle == 3)
        {
            $date  = Carbon::parse($date_pairing)->startofweek();
            $balance->where("wallet_log_date_created",">=",$date);

            $total_earned = $balance->sum("wallet_log_amount");
        }
        


        return $total_earned;
    }

    public static function check_livewell_rank($slot) {
        $check_plan_livewell_rank = Tbl_mlm_plan::where('mlm_plan_code','=','LIVEWELL_RANK')->first() ? Tbl_mlm_plan::where('mlm_plan_code','=','LIVEWELL_RANK')->first()->mlm_plan_enable : 0;
        if($check_plan_livewell_rank) {
            $highestRank = null;
            $highestRankId = null;

            // Get all memberships for the owner's slots in one query
            $membershipIds = Tbl_slot::where("slot_owner", $slot->slot_owner)->pluck('slot_membership');

            // Get all ranks related to the memberships in one query
            $ranks = Tbl_livewell_rank::whereIn('livewell_bind_membership', $membershipIds)->get()->keyBy('livewell_bind_membership');

            // Iterate through slots and assign ranks
            $slots = Tbl_slot::where("slot_owner", $slot->slot_owner)->get();

            foreach ($slots as $slot) {
                if (isset($ranks[$slot->slot_membership])) {
                    $rank = $ranks[$slot->slot_membership];
                    $slot->livewell_rank = $rank->livewell_rank_id;
                    $slot->livewell_rank_level = $rank->livewell_rank_level;

                    if ($highestRank === null || $rank->livewell_rank_level > $highestRank) {
                        $highestRank = $rank->livewell_rank_level;
                        $highestRankId = $rank->livewell_rank_id;
                    }
                }
            }
            // Update all slots to the highest rank ID
            Tbl_slot::where("slot_owner", $slot->slot_owner)->update(['slot_livewell_rank' => $highestRankId]);
        }
    }

    public static function infinity_bonus($entry_slot, $plan, $earnings = 0) {
        $infintyBonusPlan = Tbl_mlm_plan::where('mlm_plan_code', 'INFINITY_BONUS')->first();
        $checkPlanEnable = $infintyBonusPlan ? $infintyBonusPlan->mlm_plan_enable : 0;
        if($checkPlanEnable && $entry_slot && $earnings) {
            $sponsorTree = Tbl_tree_sponsor::where('sponsor_child_id', $entry_slot->slot_id)->get();
            foreach($sponsorTree as $tree) {
                $slot = Tbl_slot::JoinMembership()->where('slot_id', $tree->sponsor_parent_id)->first();
                if($slot && ($tree->sponsor_level <= $slot->infinity_bonus_level)) {
                    $setup = Tbl_infinity_bonus_setup::where('membership_id', $slot->slot_membership)
                    ->where('membership_entry_id', $slot->slot_membership)
                    ->where('level', $tree->sponsor_level)->first();
                    if($setup) {
                        $income = round(($earnings * ($setup->percentage / 100)), 2);
                        Log::insert_wallet($slot->slot_id, $income, "INFINITY_BONUS");
                        $earningLogId = Log::insert_earnings($slot->slot_id, $income, "INFINITY_BONUS", "SPECIAL PLAN", $entry_slot->slot_id, "", $tree->sponsor_level);
                        
                        if($earningLogId) {
                            Tbl_infinity_bonus_log::insert([
                                'earning_log_id' => $earningLogId,
                                'plan_trigger' => $plan
                            ]);
                        }
                    }
                }
            }
        }
    }
}