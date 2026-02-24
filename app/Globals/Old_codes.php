<?php
namespace App\Globals;

use DB;
use Carbon\Carbon;
use Validator;

use App\Globals\Log;
use App\Globals\Stairstep;

use App\Models\Tbl_slot;
use App\Models\Tbl_mlm_plan;
use App\Models\Tbl_membership_income;
use App\Models\Tbl_binary_points_settings;
use App\Models\Tbl_tree_placement;
use App\Models\Tbl_tree_sponsor;
use App\Models\Tbl_binary_pairing;
use App\Models\Tbl_membership_unilevel_level;
use App\Models\Tbl_unilevel_or_points;
use App\Models\Tbl_stairstep_settings;
use App\Models\Tbl_stairstep_rank;
use App\Models\Tbl_unileveL_points;
use App\Models\Tbl_stairstep_points;
use App\Models\Tbl_membership_cashback_level;
use App\Models\Tbl_membership;
use App\Models\Tbl_item;
use App\Models\Tbl_item_stairstep_rank_discount;
use App\Models\Tbl_item_membership_discount;
use App\Models\Tbl_binary_settings;
use App\Models\Tbl_earning_log;
use App\Models\Tbl_currency;
use App\Models\Tbl_binary_points;
use App\Models\Tbl_membership_mentors_level;
use App\Models\Tbl_mlm_lockdown_plan;
use App\Models\Tbl_other_settings;

class Old_codes
{
    public static function binary_repurchase($slot_info, $binary_pts)
    {
        $is_included = Tbl_binary_settings::first() ? Tbl_binary_settings::first()->included_binary_repurchase: 0;
        if($binary_pts != 0)
        {
            if($is_included != 0)
            {
                $limit_membership = Tbl_membership::where("membership_id",$slot_info->slot_membership)->first()->membership_binary_level;
                $tree_placement   = Tbl_tree_placement::where("placement_child_id",$slot_info->slot_id)->orderBy("placement_level","ASC")->limit($limit_membership)->get();
            }
            else 
            {
                $tree_placement = Tbl_tree_placement::where("placement_child_id",$slot_info->slot_id)->orderBy("placement_level","ASC")->get();
            }
           
            foreach($tree_placement as $tree)
            {
                $slot_placement  = Tbl_slot::where("slot_id",$tree->placement_parent_id)->first();
                $leg_limit       = Tbl_binary_settings::first() ? Tbl_binary_settings::first()->strong_leg_limit_points : 0;
                $points          = $binary_pts;
   
                        
                $receive["left"]   = 0;
                $receive["right"]  = 0;
                $old["left"]       = $slot_placement->slot_left_points;
                $old["right"]      = $slot_placement->slot_right_points;
                $new["left"]       = $slot_placement->slot_left_points;
                $new["right"]      = $slot_placement->slot_right_points;
                $log_earnings      = 0;
                $log_flushout      = 0;
                $gc_gained         = 0;
                $proceed_flushout  = 0;

                if($points != 0)
                {
                    $position = strtolower($tree->placement_position);
                    if($position == "left" || $position == "right")
                    {
                        /* MAXIMUM POINTS */
                        if($leg_limit != 0)
                        {
                            if( ($new[$position] + $points) >= $leg_limit)
                            {
                                $new[$position] = $new[$position] + $points;
                                $diff = $new[$position] - $leg_limit;
                                $receive[$position] = $points - $diff;
                                $new[$position]     = $new[$position] - $diff;
                            }
                            else 
                            {
                                $receive[$position] = $points;
                                $new[$position]     = $new[$position] + $points;
                            }
                        }
                        else 
                        {
                            $receive[$position] = $points;
                            $new[$position]     = $new[$position] + $points;
                        }

                        $update        = null;
                        $update_string = "slot_".$position."_points";
                        $update[$update_string] = Tbl_slot::where("slot_id",$slot_placement->slot_id)->first()->$update_string + $points;
                        $lockdown_plan = Tbl_mlm_lockdown_plan::plan()->where("mlm_plan_code","BINARY")->first() ? Tbl_mlm_lockdown_plan::plan()->where("mlm_plan_code","BINARY")->first()->is_lockdown_enabled : 0;
                        $enabled       = Tbl_other_settings::where("key","lockdown_enable")->first() ? Tbl_other_settings::where("key","lockdown_enable")->first()->value : 0;
                        
                        if($lockdown_plan == 1 && $enabled == 1)
                        {
                            if($slot_placement->maintained_until_date)
                            {
                                if ($slot_placement->maintained_until_date >= Carbon::now()) 
                                {
                                    Tbl_slot::where("slot_id",$slot_placement->slot_id)->update($update);
                                }
                            }
                        }
                        else 
                        {
                            Tbl_slot::where("slot_id",$slot_placement->slot_id)->update($update);
                        }


                        $plan_type = "BINARY_".strtoupper($tree->placement_position);
                        Log::insert_points($slot_placement->slot_id,$points,$plan_type,$slot_info->slot_id, $tree->placement_level);

                        $binary["left"]  = Tbl_slot::where("slot_id",$slot_placement->slot_id)->first()->slot_left_points;
                        $binary["right"] = Tbl_slot::where("slot_id",$slot_placement->slot_id)->first()->slot_right_points;

                        $pairing_settings = Tbl_binary_pairing::where("archive",0)->orderBy("binary_pairing_right","DESC")->orderBy("binary_pairing_left","DESC")->where("binary_pairing_bonus","!=",0)->where("binary_pairing_left","!=",0)->where("binary_pairing_right","!=",0)->get();

                        foreach($pairing_settings as $pairing)
                        {
                            while($binary["left"] >= $pairing->binary_pairing_left && $binary["right"] >= $pairing->binary_pairing_right)
                            {
                                /* PAIR THE POINTS */
                                $binary["left"]  = $binary["left"] - $pairing->binary_pairing_left;
                                $binary["right"] = $binary["right"] - $pairing->binary_pairing_right;

                                /* FOR LOGS BINARY PTS RECORD */
                                $new["left"]     = $new["left"] - $pairing->binary_pairing_left; 
                                $new["right"]    = $new["right"] - $pairing->binary_pairing_right;
                                $log_earnings    = $log_earnings + $pairing->binary_pairing_bonus;
                                $income_binary   = $pairing->binary_pairing_bonus;

                                /* ANOTHER RECORD FOR POINTS LOG */
                                $plan_type = "BINARY_LEFT";
                                Log::insert_points($slot_placement->slot_id,(-1 * $pairing->binary_pairing_left),$plan_type,$slot_info->slot_id, $tree->placement_level);
                               
                                $plan_type = "BINARY_RIGHT";
                                Log::insert_points($slot_placement->slot_id,(-1 * $pairing->binary_pairing_right),$plan_type,$slot_info->slot_id, $tree->placement_level);
                               

                                /* UPDATE POINTS AND WALLET*/
                                $update_slot["slot_left_points"]    = $binary["left"];
                                $update_slot["slot_right_points"]   = $binary["right"];
                                Tbl_slot::where("slot_id",$slot_placement->slot_id)->update($update_slot);

                                $currency_id     = 0;
                                $binary_settings = Tbl_binary_settings::first();

                                $gc = 0;

                                /* GC VALIDATION */
                                if($binary_settings->gc_pairing_count != 0 && $binary_settings->gc_pairing_count != 1)
                                {
                                    $count_pairing = Tbl_earning_log::where("earning_log_slot_id",$slot_placement->slot_id)->where("earning_log_plan_type","BINARY")->first() ? Tbl_earning_log::where("earning_log_slot_id",$slot_placement->slot_id)->where("earning_log_plan_type","BINARY")->count() : 0;
                                    $count_pairing = $count_pairing + 1;

                                    if($count_pairing % $binary_settings->gc_pairing_count == 0)
                                    {
                                        $gc = 1;
                                    }

                                    if($gc == 1)
                                    {
                                        $gc_currency = Tbl_currency::where("currency_abbreviation","GC")->first();

                                        if($gc_currency)
                                        {
                                            $currency_id   = $gc_currency->currency_id;
                                            if($binary_settings->gc_paring_amount != 0 )
                                            {
                                                $income_binary = $binary_settings->gc_paring_amount; 
                                                $gc_gained     = $gc_gained + $binary_settings->gc_paring_amount;
                                            }
                                            else 
                                            {
                                                $income_binary = $income_binary; 
                                                $gc_gained     = $gc_gained + $income_binary;
                                            }

                                            $log_earnings  = 0;
                                        }  
                                    }
                                }

                                /* CONDITIONAL PAIRING BINARY VALIDATION */
                                $has_paired_today = 0;
                                 
                                if($binary_settings->cycle_per_day == 1)
                                {
                                    $compare_date   = Carbon::parse($slot_info->slot_date_placed)->format("m-d-Y");
                                    $today_paired   = $slot_placement->slot_pairs_per_day_date == $compare_date ? 1 : 0;
                                }
                                else if($binary_settings->cycle_per_day == 2)
                                {
                                    $compare_date   = Carbon::parse($slot_info->slot_date_placed)->format("m-d-Y");
                                    $compare_date_a = Carbon::parse($slot_info->slot_date_placed)->format("A"); 
                                    $today_paired   = $slot_placement->slot_pairs_per_day_date == $compare_date && $slot_placement->meridiem == $compare_date_a ? 1 : 0;                                       
                                }
                                else if($binary_settings->cycle_per_day == 3)
                                {
                                    $compare_date   = Carbon::parse($slot_info->slot_date_placed)->endofweek()->format("m-d-Y");
                                    $today_paired   = $slot_placement->slot_pairs_per_day_date == $compare_date ? 1 : 0;
                                }  


                                /* PAIRINGS PER DAY FLUSHOUT CHECKING */
                                $membership_per_day = Tbl_membership::where("membership_id",$slot_placement->slot_membership)->first();
                                if($membership_per_day)
                                {
                                    if($membership_per_day->membership_pairings_per_day != 0)
                                    {
                                        if($has_paired_today == 1)
                                        {
                                            if($slot_placement->slot_pairs_per_day >= $membership_per_day->membership_pairings_per_day)
                                            {
                                                $proceed_flushout = 1;
                                                $log_flushout  = $log_flushout + $income_binary;
                                                if($gc == 1)
                                                { 
                                                    $gc_gained     = $gc_gained - $income_binary;
                                                }
                                                else
                                                {
                                                    $log_earnings  = $log_earnings - $income_binary;
                                                }

                                                $income_binary = 0;
                                            }
                                            else
                                            {
                                                $update_pairing_slot_mem["slot_pairs_per_day"] = $slot_placement->slot_pairs_per_day + 1;
                                                $update_pairing_slot_mem["meridiem"]           = Carbon::parse($slot_info->slot_date_placed)->format("A");
                                                Tbl_slot::where("slot_id",$slot_placement->slot_id)->update($update_pairing_slot_mem);
                                            }
                                        }
                                        else
                                        {
                                            $update_pairing_slot_mem["slot_pairs_per_day_date"]  = Carbon::parse($slot_info->slot_date_placed)->format("m-d-Y");
                                            $update_pairing_slot_mem["meridiem"]                 = Carbon::parse($slot_info->slot_date_placed)->format("A");
                                            $update_pairing_slot_mem["slot_pairs_per_day"]       = 1;
                                            Tbl_slot::where("slot_id",$slot_placement->slot_id)->update($update_pairing_slot_mem);
                                        } 
                                    }
                                }

                                /* AMOUNT LIMIT PER DAY CHECKING */
                                if($binary_settings->amount_binary_limit != 0)
                                {
                                    if($has_paired_today == 1)
                                    {
                                        $balance = Tbl_binary_points::where("binary_points_slot_id",$slot_placement->slot_id)
                                                                        ->whereDate("binary_points_date_received",">=",$slot_placement->slot_pairs_per_day_date)
                                                                        ->sum("binary_points_income");
                                                                        
                                        $total = $balance + $income_binary;
                                        if($total > $binary_settings->amount_binary_limit)
                                        {
                                            $diff = $total - $binary_settings->amount_binary_limit;
                                            $income_binary = $income_binary - $diff; 
                                            $log_earnings  = $income_binary;
                                        }
                                    }
                                }

                                Log::insert_wallet($slot_placement->slot_id,$income_binary,"BINARY",$currency_id);

                                /* MENTORS BONUS */
                                if($gc == 0 && $income_binary != 0)
                                {
                                    $highest_level = Tbl_membership::orderBy("mentors_level","DESC")->first();
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
                                                    $details = "Paired by Slot ".$slot_info->slot_no;
                                                    if($check_mentor_settings->mentors_bonus != 0)
                                                    {                                                    
                                                        $income_mentors = $income_binary * ($check_mentor_settings->mentors_bonus/100);
                                                        if($income_mentors != 0)
                                                        {
                                                            Log::insert_wallet($mentor_slot->slot_id,$income_mentors,"MENTORS_BONUS",$currency_id);
                                                            Log::insert_earnings($mentor_slot->slot_id,$income_mentors,"MENTORS_BONUS","SLOT PLACEMENT",$slot_placement->slot_id,$details,$mentor_tree->sponsor_level,$currency_id);
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                                
                                /*LOGS*/
                                $details = "";
                                Log::insert_earnings($slot_placement->slot_id,$income_binary,"BINARY","SLOT PLACEMENT",$slot_info->slot_id,$details,$tree->placement_level,$currency_id);
        
                                /* REFRESH GET DATA ON POINTS */  
                                $binary["left"]  = Tbl_slot::where("slot_id",$slot_placement->slot_id)->first()->slot_left_points;
                                $binary["right"] = Tbl_slot::where("slot_id",$slot_placement->slot_id)->first()->slot_right_points;
                            }
                        }

                        $binary_settings = Tbl_binary_settings::first();

                        if($binary_settings->strong_leg_retention == 0)
                        {                   
                            if($proceed_flushout == 1)
                            {
                                    if($new["left"] != 0)
                                    {
                                        $plan_type = "BINARY_LEFT_FLUSHOUT";
                                        Log::insert_points($slot_placement->slot_id,(-1 * $new["left"]),$plan_type,$slot_info->slot_id, 0);                                 
                                        $new["left"]  = $new["left"] - $new["left"];   
                                    }
                                    
                                    if($new["right"] != 0)
                                    {
                                        $plan_type = "BINARY_RIGHT_FLUSHOUT";
                                        Log::insert_points($slot_placement->slot_id,(-1 * $new["right"]),$plan_type,$slot_info->slot_id, 0);                                 
                                        $new["right"] = $new["right"] - $new["right"];
                                    }

                                    $update_slot_flush["slot_left_points"]    = $new["left"];
                                    $update_slot_flush["slot_right_points"]   = $new["right"];
                                    Tbl_slot::where("slot_id",$slot_placement->slot_id)->update($update_slot_flush);
                            }
                        }

                        Log::insert_binary_points($slot_placement->slot_id,$receive,$old,$new,$slot_info->slot_id,$log_earnings,$log_flushout,$tree->placement_level,"Slot Placement",$gc_gained);
                    }
                }
            }
        }
    }
}
