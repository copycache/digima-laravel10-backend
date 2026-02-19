<?php
namespace App\Globals;

use App\Models\Tbl_welcome_bonus_commissions;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

use App\Globals\Wallet;
use App\Globals\Log;
use App\Globals\Vortex;
use App\Globals\Special_plan;
use App\Globals\Slot;

use App\Models\Tbl_slot;
use App\Models\Tbl_binary_points;
use App\Models\Tbl_mlm_plan;
use App\Models\Tbl_membership_income;
use App\Models\Tbl_binary_points_settings;
use App\Models\Tbl_tree_placement;
use App\Models\Tbl_tree_sponsor;
use App\Models\Tbl_binary_pairing;
use App\Models\Tbl_mlm_monoline_settings;
use App\Models\Tbl_mlm_pass_up_settings;
use App\Models\Tbl_monoline_points;
use App\Models\Tbl_membership_indirect_level;
use App\Models\Tbl_binary_settings;
use App\Models\Tbl_earning_log;
use App\Models\Tbl_currency;
use App\Models\Tbl_membership;
use App\Models\Tbl_item;
use App\Models\Tbl_membership_leveling_bonus_level;
use App\Models\Tbl_leveling_bonus_points;
use App\Models\Tbl_mlm_board_settings;
use App\Models\Tbl_mlm_board_placement;
use App\Models\Tbl_mlm_board_slot;
use App\Models\Tbl_mlm_universal_pool_bonus_settings;
use App\Models\Tbl_mlm_universal_pool_bonus_points;
use App\Models\Tbl_membership_mentors_level;
use App\Models\Tbl_mlm_universal_pool_bonus_maintain_settings;
use App\Models\Tbl_membership_vortex;
use App\Models\Tbl_membership_gc_income;
use App\Models\Tbl_membership_upgrade_settings;
use App\Models\Tbl_other_settings;
use App\Models\Tbl_mlm_lockdown_plan;
use App\Models\Tbl_direct_bonus;
use App\Models\Tbl_share_link_settings;
use App\Models\Tbl_passive_unilevel_premium;
use App\Models\Tbl_indirect_settings;
use App\Models\Tbl_signup_bonus_logs;
use App\Models\Tbl_reverse_pass_up_settings;
use App\Models\Tbl_pass_up_combination_income;
use App\Models\Tbl_pass_up_direct_combination_income;
use App\Models\Tbl_reverse_pass_up_combination_income;
use App\Models\Tbl_reverse_pass_up_direct_combination_income;
use App\Models\Tbl_matrix_placement;
use App\Models\Tbl_unilevel_matrix_bonus_settings;
use App\Models\Tbl_unilevel_matrix_bonus_levels;
use App\Models\Users;
use App\Models\Tbl_membership_unilevel_level;
use App\Models\Tbl_binary_projected_income_log;
use App\Models\Tbl_leaders_support_log;
use App\Models\Tbl_leaders_support_settings;
use App\Models\Tbl_leaders_support_setup;
use App\Models\Tbl_prime_refund_setup;
use App\Models\Tbl_prime_refund_points_log;
use App\Models\Tbl_milestone_bonus_settings;
use App\Models\Tbl_milestone_points_setup;
use App\Models\Tbl_milestone_pairing_points_setup;
use App\Models\Tbl_marketing_support_setup;
use App\Models\Tbl_marketing_support_settings;
use App\Models\Tbl_marketing_support_log;

class Mlm_complan_manager
{
	public static function binary($slot_info , $binary_repurchase_pts = 0)
	{
        $is_included      = Tbl_binary_settings::first() ? Tbl_binary_settings::first()->included_binary_repurchase: 0;
        $limit_membership = Tbl_membership::where("membership_id",$slot_info->slot_membership)->first()->membership_binary_level;
        $binary_settings = Tbl_binary_settings::first();
        
        if($limit_membership == 0 || ($binary_repurchase_pts != 0 && $is_included == 0))
        {
            $tree_placement = Tbl_tree_placement::where("placement_child_id",$slot_info->slot_id)->orderBy("placement_level","ASC")->get();
        }
        else 
        {   
            $tree_placement = Tbl_tree_placement::where("placement_child_id",$slot_info->slot_id)->orderBy("placement_level","ASC")->limit($limit_membership)->get();            
        }

		foreach($tree_placement as $tree)
		{
            $can_recieve_points  = Tbl_slot::where("slot_id",$tree->placement_parent_id)->JoinMembership()->first() ? Tbl_slot::where("slot_id",$tree->placement_parent_id)->JoinMembership()->first()->can_receive_points : 1;
            if($can_recieve_points == 1)
            {
                $slot_placement  = Tbl_slot::JoinMembership()->where("slot_id",$tree->placement_parent_id)->first();
                $points_settings = Tbl_binary_points_settings::where("membership_id",$slot_placement->slot_membership)->where("membership_entry_id",$slot_info->slot_membership)->first();
                $leg_limit       = Tbl_binary_settings::first() ? Tbl_binary_settings::first()->strong_leg_limit_points : 0;
                $max_binary_points_per_level = Tbl_membership::where('membership_id', $slot_placement->slot_membership)->first()->max_points_per_level;
                $max_binary_earnings_per_level = Tbl_membership::where('membership_id', $slot_placement->slot_membership)->first()->max_earnings_per_level;
                $maxed_slot = Tbl_binary_points_settings::where("membership_id", $slot_placement->slot_membership)->where("membership_entry_id", $slot_info->slot_membership)->first()->max_slot_per_level;
                $tree_details = Tbl_tree_placement::where('placement_parent_id', $slot_placement->slot_id)->where('placement_child_id', $slot_info->slot_id)->first();
                if($binary_repurchase_pts != 0)
                {
                    $points = $binary_repurchase_pts;
                }
                else if($points_settings)
                {
                    $points = $points_settings->membership_binary_points;

                    if($binary_settings->binary_maximum_points_per_level_enable && $max_binary_points_per_level) {
                        $plan_type = "BINARY_" . strtoupper($tree->placement_position);
                        $binaryPoints = Tbl_binary_points::where('binary_points_slot_id', $slot_placement->slot_id)
                            ->where('binary_cause_level', $tree->placement_level)
                            ->selectRaw(
                                'SUM(binary_receive_left) as left_points, 
                                SUM(binary_receive_right) as right_points, 
                                SUM(binary_points_income) as earnings'
                            )
                            ->first(); 
                        $binarySlots = Tbl_binary_points::where('binary_points_slot_id', $slot_placement->slot_id)
                            ->where('binary_cause_membership_id', $slot_info->slot_membership)
                            ->where('binary_cause_level', $tree->placement_level)
                            ->selectRaw(
                                'GROUP_CONCAT(binary_points_id) as all_tree_id'
                            )
                            ->first(); 
    
                        $left_slot = 0;
                        $right_slot = 0;
                        $number_of_slot = 0;
                        if($binarySlots->all_tree_id) {
                            $tree_ids = explode(',', $binarySlots->all_tree_id);
                            foreach ($tree_ids as $log_id) {
                                $tree_log = Tbl_binary_points::where('binary_points_id', $log_id)->first();
                                $log_details = Tbl_slot::where('slot_id', $tree_log->binary_cause_slot_id)
                                ->leftJoin('tbl_tree_placement', 'tbl_slot.slot_id', '=', 'tbl_tree_placement.placement_child_id')
                                ->where('tbl_tree_placement.placement_parent_id', $slot_placement->slot_id)
                                ->first();
        
                                if($log_details->placement_position == 'LEFT') {
                                    $left_slot++;
                                } else if($log_details->placement_position == 'RIGHT') {
                                    $right_slot++;
                                }
                            }
                        }
    
                        if($tree_details->placement_position == 'LEFT') {
                            $number_of_slot = $left_slot;
                        } else if($tree_details->placement_position == 'RIGHT') {
                            $number_of_slot = $right_slot;
                        }
                        
                        if($number_of_slot < $maxed_slot || !$maxed_slot) {
                            if ($plan_type == 'BINARY_LEFT') {
                                if ($binaryPoints->left_points < $max_binary_points_per_level || !$max_binary_points_per_level) {
                                    $points = min($points, $max_binary_points_per_level - $binaryPoints->left_points);
                                } else {
                                    $points = 0;
                                }
                            } elseif ($plan_type == 'BINARY_RIGHT') {
                                if ($binaryPoints->right_points < $max_binary_points_per_level || !$max_binary_points_per_level) {
                                    $points = min($points, $max_binary_points_per_level - $binaryPoints->right_points);
                                } else {
                                    $points = 0;
                                }
                            }
                        } else {
                            $points = 0;
                        }
                    }
                }
                else
                {
                    $points = 0;
                }
                        
                $receive["left"]          = 0;
                $receive["right"]         = 0;
                $old["left"]              = $slot_placement->slot_left_points;
                $old["right"]             = $slot_placement->slot_right_points;
                $new["left"]              = $slot_placement->slot_left_points;
                $new["right"]             = $slot_placement->slot_right_points;
                $flushout_points["right"] = 0;
                $flushout_points["left"]  = 0;
                $log_earnings             = 0;
                $log_flushout             = 0;
                $gc_gained                = 0;
                $proceed_flushout         = 0;
                $total_earnings           = 0;
                $insert_binary_points_log = 0;

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
                                $new[$position]             = $new[$position] + $points;
                                $diff                       = $new[$position] - $leg_limit;
                                $flushout_points[$position] = $flushout_points[$position] + $diff;
                                $receive[$position]         = $points - $diff;
                                $new[$position]             = $new[$position] - $diff;
                                $points                     = $points - $diff;
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
                        $temp_log_earnings = 0;
                        $update        = null;
                        $update_string = "slot_".$position."_points";
                        $update[$update_string] = Tbl_slot::where("slot_id",$slot_placement->slot_id)->first()->$update_string + $points;
                        $lockdown_plan = Tbl_mlm_lockdown_plan::plan()->where("mlm_plan_code","BINARY")->first() ? Tbl_mlm_lockdown_plan::plan()->where("mlm_plan_code","BINARY")->first()->is_lockdown_enabled : 0;
                        $enabled 	   = Tbl_other_settings::where("key","lockdown_enable")->first() ? Tbl_other_settings::where("key","lockdown_enable")->first()->value : 0;
    
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
    
                        $count_direct = 0;
                        $direct_required = 0;

                        if ($binary_settings->binary_required_direct_enable) {
                            $count_direct = Tbl_slot::where("slot_sponsor", $slot_placement->slot_id)->count();
                            $direct_required = Tbl_membership::where("membership_id", $slot_placement->slot_membership)->value('binary_required_direct');
                        }
                        // Check if the condition is met or the feature is disabled
                        if (!$binary_settings->binary_required_direct_enable || $count_direct >= $direct_required) {
                            $plan_type = "BINARY_" . strtoupper($tree->placement_position);
                            Log::insert_points($slot_placement->slot_id, $points, $plan_type, $slot_info->slot_id, $tree->placement_level);
                       
                            $binary["left"]  = Tbl_slot::where("slot_id",$slot_placement->slot_id)->first()->slot_left_points;
                            $binary["right"] = Tbl_slot::where("slot_id",$slot_placement->slot_id)->first()->slot_right_points;
        
                            $pairing_settings = Tbl_binary_pairing::where("archive",0)
                                ->orderBy("binary_pairing_right","DESC")
                                ->orderBy("binary_pairing_left","DESC")
                                ->where("binary_pairing_bonus","!=",0)
                                ->where("binary_pairing_left","!=",0)
                                ->where("binary_pairing_right","!=",0)
                                ->where(function ($query) use ($slot_placement)
                                {
                                    $query->where('binary_pairing_membership', $slot_placement->slot_membership)
                                        ->orWhereNull('binary_pairing_membership', '=', null);
                                })
                                ->get();
                        
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
                                    $update_slot["slot_left_points"]	= $binary["left"];
                                    $update_slot["slot_right_points"]	= $binary["right"];
                                    Tbl_slot::where("slot_id",$slot_placement->slot_id)->update($update_slot);
        
                                    $currency_id     = 0;
        
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
                                    $has_paired_today  = 0;
                                    if($binary_repurchase_pts != 0)
                                    {
                                        $slot_date_pairing = Carbon::now();
                                    }
                                    else
                                    {
                                        $slot_date_pairing = Carbon::parse($slot_info->slot_date_placed);
                                    }
        
                                    $logs = Tbl_earning_log::where('earning_log_slot_id', $slot_placement->slot_id)->where('earning_log_plan_type','=','BINARY');

                                    if($binary_settings->cycle_per_day == 1)
                                    {
                                        $compare_date       = Carbon::parse($slot_date_pairing)->format("m-d-Y");
                                        $has_paired_today   = $slot_placement->slot_pairs_per_day_date == $compare_date ? 1 : 0;
                                        $today = Carbon::now()->format('Y-m-d');
                                        if ($slot_placement->binary_realtime_commission == 1) {
                                            $total_earnings_per_cycle = $logs->whereDate('earning_log_date_created', $today)->sum('earning_log_amount');
                                            $total_pairs_per_cycle = $logs->whereDate('earning_log_date_created', $today)->count();
                                        } else {
                                            $total_pairs_per_cycle = Tbl_binary_projected_income_log::where('slot_id', $slot_placement->slot_id)->where('wallet_amount', '!=', 0)->wheredate('date_created',$today)->count();
                                            $total_earnings_per_cycle = Tbl_binary_projected_income_log::where('slot_id', $slot_placement->slot_id)->where('wallet_amount', '!=', 0)->wheredate('date_created',$today)->sum('wallet_amount');

                                        }
                                    }
                                    else if($binary_settings->cycle_per_day == 2)
                                    {
                                        $compare_date       = Carbon::parse($slot_date_pairing)->format("m-d-Y");
                                        $compare_date_a     = Carbon::parse($slot_date_pairing)->format("A"); 
                                        $has_paired_today   = $slot_placement->slot_pairs_per_day_date == $compare_date && $slot_placement->meridiem == $compare_date_a ? 1 : 0;                                       
                                        $meridiem = Carbon::now()->format('A');
                                        
                                        if($meridiem == "AM")
                                        {
                                            // For AM, calculate earnings from the start of the day to noon
                                            $start_of_day = Carbon::now()->format('Y-m-d 00:00:00');
                                            $end_of_am = Carbon::now()->format('Y-m-d 11:59:59');
                                            $total_earnings_per_cycle = $logs
                                                ->where('earning_log_date_created', '>=', $start_of_day)
                                                ->where('earning_log_date_created', '<=', $end_of_am)
                                                ->sum('earning_log_amount');
                                            $total_pairs_per_cycle = $logs
                                                ->where('earning_log_date_created', '>=', $start_of_day)
                                                ->where('earning_log_date_created', '<=', $end_of_am)
                                                ->count();
                                        }
                                        else 
                                        {
                                            // For PM, calculate earnings from noon to the end of the day
                                            $start_of_pm = Carbon::now()->format('Y-m-d 12:00:00');
                                            $end_of_day = Carbon::now()->format('Y-m-d 23:59:59');
                                            $total_earnings_per_cycle = $logs
                                                ->where('earning_log_date_created', '>=', $start_of_pm)
                                                ->where('earning_log_date_created', '<=', $end_of_day)
                                                ->sum('earning_log_amount');
                                            $total_pairs_per_cycle = $logs
                                                ->where('earning_log_date_created', '>=', $start_of_pm)
                                                ->where('earning_log_date_created', '<=', $end_of_day)
                                                ->count();
                                        }
                                    }
                                    else if($binary_settings->cycle_per_day == 3)
                                    {
                                        $compare_date       = Carbon::parse($slot_date_pairing)->endofweek()->format("m-d-Y");
                                        $has_paired_today   = $slot_placement->slot_pairs_per_day_date == $compare_date ? 1 : 0;
                                        $start = Carbon::now()->startofWeek();
                                        $end = Carbon::now()->endofWeek();
                                        $total_earnings_per_cycle = $logs->where('earning_log_date_created',">=",$start)->where('earning_log_date_created',"<=",$end)->sum('earning_log_amount');
                                        $total_pairs_per_cycle = $logs->where('earning_log_date_created',">=",$start)->where('earning_log_date_created',"<=",$end)->count();
                                    } else if ($binary_settings->cycle_per_day == 4) {
                                        $compare_date = Carbon::parse($slot_date_pairing)->format("m-d-Y");
                                        $has_paired_today = $slot_placement->slot_pairs_per_day_date == $compare_date ? 1 : 0;
                                        $total_earnings_per_cycle = $logs->sum('earning_log_amount');
                                        $total_pairs_per_cycle = $logs->count();
                                    }
        
                                    /* PAIRINGS PER DAY FLUSHOUT CHECKING */
                                    $membership = Tbl_membership::where("membership_id",$slot_placement->slot_membership)->first();
                                    if($membership) {
                                        $limit_type = $binary_settings->binary_limit_type;
                                        
                                        if($has_paired_today == 1)
                                        {
                                            if($limit_type == 1 && $total_pairs_per_cycle >= $membership->membership_pairings_per_day && $membership->membership_pairings_per_day) {
                                                $proceed_flushout = 1;
                                                $log_flushout += $income_binary;

                                                if ($gc == 1) {
                                                    $gc_gained -= $income_binary;
                                                } else {
                                                    // $log_earnings -= $membership->auto_upgrade ? ($income_binary - $deduction) : $income_binary;
                                                    $log_earnings -= $income_binary;
                                                }
                                                $income_binary = 0;
                                            } else if($limit_type == 2) {
                                                $total = $total_earnings_per_cycle + $income_binary;
                                                if(round($total, 2) > round($membership->max_earnings_per_cycle, 2)) {
                                                    if(round($total_earnings_per_cycle, 2) >= round($membership->max_earnings_per_cycle, 2)) {
                                                        $log_flushout += $income_binary;
                                                        $log_earnings = 0;
                                                        $income_binary = 0;

                                                    } else {
                                                        // $income_binary = (round($total, 2) - round($membership->max_earnings_per_cycle, 2)) ;
                                                        // dd(round($total, 2), round($membership->max_earnings_per_cycle, 2), $income_binary);
                                                        $diff = (round($total, 2) - round($membership->max_earnings_per_cycle, 2));
                                                        $income_binary -= $diff;
                                                        $log_flushout += $diff;
                                                        $log_earnings -= $log_flushout;
                                                    }
                                                    $proceed_flushout = 1;
                                                }
                                            } else {
                                                $update_pairing_slot_mem["slot_pairs_per_day"] = $slot_placement->slot_pairs_per_day + 1;
                                                $update_pairing_slot_mem["meridiem"] = Carbon::parse($slot_date_pairing)->format("A");
                                                Tbl_slot::where("slot_id", $slot_placement->slot_id)->update($update_pairing_slot_mem);
                                            }
                                        }
                                        else
                                        {
                                            $update_pairing_slot_mem["slot_pairs_per_day_date"]  = $binary_settings->cycle_per_day == 3 ? Carbon::parse($slot_date_pairing)->endofweek()->format("m-d-Y") : Carbon::parse($slot_date_pairing)->format("m-d-Y");
                                            $update_pairing_slot_mem["meridiem"]                 = Carbon::parse($slot_date_pairing)->format("A");
                                            $update_pairing_slot_mem["slot_pairs_per_day"]       = 1;
                                            Tbl_slot::where("slot_id",$slot_placement->slot_id)->update($update_pairing_slot_mem);
                                        } 
                                        // else if($membership->max_earnings_per_level != 0) {
                                        //     $total_earning_per_level = Tbl_binary_points::where('binary_points_slot_id', $slot_placement->slot_id)
                                        //     ->where('binary_cause_level', $tree->placement_level)
                                        //     ->sum('binary_points_income');

                                        //     $total_earnings = $total_earning_per_level + $log_earnings;
                                            
                                        //     if ($total_earnings > $membership->max_earnings_per_level) {
                                        //         $diff = $total_earnings - $membership->max_earnings_per_level;
                                        //         $income_binary = max($income_binary - $diff, 0);
                                        //         $log_flushout += $diff;
                                        //         $log_earnings -= $diff;
                                        //         $proceed_flushout = 1;
                                        //     }
                                        // }
                                    }
        
        
                                    /* AMOUNT LIMIT PER DAY CHECKING */
                                    if($binary_settings->amount_binary_limit != 0)
                                    {
                                        if($has_paired_today == 1)
                                        {
                                            
                                            $balance    =   Tbl_binary_points::where("binary_points_slot_id",$slot_placement->slot_id);
        
                                            if($binary_settings->cycle_per_day == 1)
                                            {
                                                $balance->where("binary_points_date_received",">=",$slot_date_pairing->format("Y-m-d 00:00:00"));
                                            }
                                            else if($binary_settings->cycle_per_day == 2)
                                            {   
                                                $meridiem     = Carbon::parse($slot_date_pairing)->format("A"); 
                                                if($meridiem == "AM")
                                                {   
                                                    $date  =   Carbon::parse($slot_date_pairing)->format("Y-m-d 00:00:00");
                                                }
                                                else 
                                                {
                                                    $date  =   Carbon::parse($slot_date_pairing)->format("Y-m-d 12:00:00");
                                                }
                                                $balance->where("binary_points_date_received",">=",$date);
                                            }
                                            else
                                            {
                                                $date  =   Carbon::parse($slot_date_pairing)->startofweek();
                                                $balance->where("binary_points_date_received",">=",$date);
                                            }
        
                                            $balance =   $balance->sum("binary_points_income");
                                            $total = $balance + $income_binary;
                                            if($total > $binary_settings->amount_binary_limit)
                                            {
                                                $diff = $total - $binary_settings->amount_binary_limit;
                                                $income_binary = $income_binary - $diff; 
        
                                                $log_flushout  = $log_flushout + $diff;
        
                                                $log_earnings  = $income_binary;
                                            }
                                        }   
                                    }
        
                                    /* MENTORS BONUS */
                                    if($gc == 0 && $income_binary != 0)
                                    {
                                        /* MENTORS BONUS */
                                        Special_plan::mentors_bonus($slot_placement->slot_id,$income_binary,$slot_date_pairing);
                                        
                                        /* SPONSOR MATCHING BONUS */
                                        Special_plan::sponsor_matching($slot_placement->slot_id,$income_binary,$slot_date_pairing);
                                    }
                                    
                                    /*LOGS*/
                                    $_binary_settings = Tbl_binary_settings::first();
                                    $_proceed = false;
                                    $_gc_currency = Tbl_currency::where("currency_abbreviation","GC")->first();
                                    if ($_gc_currency->currency_id != $currency_id) 
                                    {
                                        if($_binary_settings->binary_points_enable == 1)
                                        {
                                            if($_binary_settings->binary_points_minimum_conversion > 0)
                                            {
                                                $_MP = Tbl_currency::where("currency_abbreviation","MP")->first();
                                                if($_MP)
                                                {
                                                    $details = "";
                                                    Log::insert_wallet($slot_placement->slot_id,$income_binary,"BINARY",$_MP->currency_id);
                                                    Log::insert_earnings($slot_placement->slot_id,$income_binary,"BINARY","SLOT PLACEMENT",$slot_info->slot_id,$details,$tree->placement_level,$_MP->currency_id);

                                                    $_MP_wallet = Tbl_slot::where("tbl_slot.slot_id",$slot_placement->slot_id)->Wallet($_MP->currency_id)->first();
                                                    if ($_MP_wallet->wallet_amount >= $_binary_settings->binary_points_minimum_conversion) 
                                                    {
                                                        Log::insert_wallet($slot_placement->slot_id,$_MP_wallet->wallet_amount * -1,"BINARY_CONVERSION",$_MP->currency_id);
                                                        Log::insert_earnings($slot_placement->slot_id,$_MP_wallet->wallet_amount * -1,"BINARY_CONVERSION","SLOT PLACEMENT",$slot_info->slot_id,$details,$tree->placement_level,$_MP->currency_id);

                                                        Log::insert_wallet($slot_placement->slot_id,$_MP_wallet->wallet_amount,"MATCHED_POINTS_CONVERSION",$currency_id);
                                                        Log::insert_earnings($slot_placement->slot_id,$_MP_wallet->wallet_amount,"MATCHED_POINTS_CONVERSION","SLOT PLACEMENT",$slot_info->slot_id,$details,$tree->placement_level,$currency_id);
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
                                    }
                                    else
                                    {
                                        $_proceed = true;
                                    }
                                    
                                    if($_proceed)
                                    {
                                        $details = "";

                                        $cd_package = Tbl_membership::where('hierarchy',1)->where('archive',0)->pluck('membership_id')->first();
                                        $cd_slot_info = Tbl_slot::where('slot_id',$slot_placement->slot_id)->first();
                                        if($cd_slot_info->slot_membership == $cd_package)
                                        {
                                            $cd_earnings    = $income_binary * 0.2;
                                            $income_binary1 = $income_binary - $cd_earnings;

                                            Log::insert_wallet($slot_placement->slot_id,$income_binary1,"BINARY");
                                            Log::insert_wallet($slot_placement->slot_id,$cd_earnings,"BINARY",18);
                                        }
                                        else
                                        {
                                            $status = 0;

                                            if($slot_placement->binary_realtime_commission == 1) {
                                                Log::insert_wallet($slot_placement->slot_id,$income_binary,"BINARY",$currency_id);
                                            }
                                        }
                                        if($income_binary && $slot_placement->binary_realtime_commission == 1) {
                                            Log::insert_earnings($slot_placement->slot_id,$income_binary,"BINARY","SLOT PLACEMENT",$slot_info->slot_id,$details,$tree->placement_level,$currency_id);
                					        Special_plan::infinity_bonus($slot_placement, "BINARY", $income_binary);
                                        } else if($income_binary && $slot_placement->binary_realtime_commission == 0) {
                                            $insert["slot_id"] = $slot_placement->slot_id;
                                            $insert["membership_id"] = $slot_placement->slot_membership;
                                            $insert["cause_slot_id"] = $slot_info->slot_id;
                                            $insert["cause_membership_id"] = $slot_info->slot_membership;
                                            $insert["cause_level"] = $tree->placement_level;
                                            $insert["wallet_amount"] = $income_binary;
                                            $insert["status"] = $status;
                                            $insert["date_status_change"] = $status ? Carbon::now() : null;
                                            $insert["date_created"] = Carbon::now();
                                            Tbl_binary_projected_income_log::insert($insert);
                                        }
                                        $check_plan_special_bonus     = Tbl_mlm_plan::where('mlm_plan_code','=','SPECIAL_BONUS')->first() ? Tbl_mlm_plan::where('mlm_plan_code','=','SPECIAL_BONUS')->first()->mlm_plan_enable : 0;
                                        if($check_plan_special_bonus == 1)
                                        {
                                            Special_plan::special_bonus($slot_placement, $income_binary);
                                        }
                                        /* GET THE LAST EARNINGS BEFORE FLUSHOUT */  
                                        if($log_earnings) {
                                            $temp_log_earnings = $log_earnings;
                                        } else {
                                            $log_earnings = $temp_log_earnings;
                                        }
                                    }
                                    /* REFRESH GET DATA ON POINTS */  
                                    $binary["left"]  = Tbl_slot::where("slot_id",$slot_placement->slot_id)->first()->slot_left_points;
                                    $binary["right"] = Tbl_slot::where("slot_id",$slot_placement->slot_id)->first()->slot_right_points;
                                    $slot_placement  = Tbl_slot::JoinMembership()->where("slot_id",$slot_placement->slot_id)->first();
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
                                            $flushout_points["left"] = $new["left"];
                                            $new["left"]  = $new["left"] - $new["left"];  
                                        }
                                        
                                        if($new["right"] != 0)
                                        {
                                            $plan_type = "BINARY_RIGHT_FLUSHOUT";
                                            Log::insert_points($slot_placement->slot_id,(-1 * $new["right"]),$plan_type,$slot_info->slot_id, 0);                                 
                                            $flushout_points["right"] = $new["right"];
                                            $new["right"] = $new["right"] - $new["right"];
                                        }
        
                                        $update_slot_flush["slot_left_points"]    = $new["left"];
                                        $update_slot_flush["slot_right_points"]   = $new["right"];
                                        Tbl_slot::where("slot_id",$slot_placement->slot_id)->update($update_slot_flush);
                                }
                            }
                            // For a single detail among multiple pairings
                            // $details = "";
                            // Log::insert_wallet($slot_placement->slot_id,$log_earnings,"BINARY");
                            // Log::insert_earnings($slot_placement->slot_id,$log_earnings,"BINARY","SLOT PLACEMENT",$slot_info->slot_id,$details,$tree->placement_level);
                            if($slot_placement->binary_realtime_commission == 1) {
                                Log::insert_binary_points($slot_placement->slot_id,$receive,$old,$new,$slot_info->slot_id,$log_earnings,$log_flushout,$tree->placement_level,"Slot Placement",$gc_gained,$flushout_points,$binary_repurchase_pts);
                            } else if ($slot_placement->binary_realtime_commission == 0) {
                                Log::insert_binary_points($slot_placement->slot_id,$receive,$old,$new,$slot_info->slot_id,0,$log_flushout,$tree->placement_level,"Slot Placement",$gc_gained,$flushout_points,$binary_repurchase_pts, null, $log_earnings);
                            }
                            
                            // Created By: Centy - 10-27-2023
                            $check_plan_achievers     = Tbl_mlm_plan::where('mlm_plan_code','=','ACHIEVERS_RANK')->first() ? Tbl_mlm_plan::where('mlm_plan_code','=','ACHIEVERS_RANK')->first()->mlm_plan_enable : 0;
                            if($check_plan_achievers == 1)
                            {
                                $is_slot_creation = true;
                                Achievers_Rank::update_rank($slot_placement->slot_id,null,null,$is_slot_creation);
                            }
                        }
                    }
                }
            }
		}
	}

	public static function direct($slot_info)
	{
		/* CHECK SPONSOR SLOT*/
        $slot_sponsor = Tbl_slot::where('slot_id', $slot_info->slot_sponsor)->first();
        if($slot_sponsor)
        {
        	/* CHECK INCOME SETTINGS */
        	$membership_income = Tbl_membership_income::where("membership_id",$slot_sponsor->slot_membership)->where("membership_entry_id",$slot_info->slot_membership)->first();
        	if($membership_income)
        	{
        		$direct_income = $membership_income->membership_direct_income;
        	}
        	else
        	{
        		$direct_income = 0;
            }
            
            /* CHECK INCOME IN GC SETTINGS */
            $membership_income2 = Tbl_membership_gc_income::where("membership_id",$slot_sponsor->slot_membership)->where("membership_entry_id",$slot_info->slot_membership)->first();
            // dd($membership_income2["membership_gc_income"]);
        	if($membership_income2)
        	{
        		$gc_income = $membership_income2["membership_gc_income"];
        	}
        	else
        	{
        		$gc_income = 0;
        	}

            /* VORTEX */
            $vortex_plan = Tbl_mlm_plan::where("mlm_plan_code","VORTEX_PLAN")->where("mlm_plan_enable",1)->first();
            if($vortex_plan)
            {
                $membership_income = Tbl_membership_vortex::where("membership_id",$slot_sponsor->slot_membership)->where("membership_entry_id",$slot_info->slot_membership)->first();
                $vortex_token      = $membership_income ? $membership_income->membership_vortex_token : 0;

                if($vortex_token != 0)
                {
                    Vortex::insert_token($slot_sponsor->slot_id,$slot_info->slot_id,"DIRECT",$vortex_token);
                }
            }



        	/* IF DIRECT INCOME IS NOT 0 */
        	if($direct_income != 0)
        	{
                /*LOGS*/
                $details = "";

                $cd_package = Tbl_membership::where('hierarchy',1)->where('archive',0)->pluck('membership_id')->first();
                $cd_slot_info = Tbl_slot::where('slot_id',$slot_sponsor->slot_id)->first();

                if($cd_slot_info->slot_membership == $cd_package)
                {

                    $cd_earnings    = $direct_income * 0.2;
                    $direct_income1 = $direct_income - $cd_earnings;

                    Log::insert_wallet($slot_sponsor->slot_id,$direct_income1,"DIRECT");
                    Log::insert_wallet($slot_sponsor->slot_id,$cd_earnings,"DIRECT",18);
                }
                else
                {
                    Log::insert_wallet($slot_sponsor->slot_id,$direct_income,"DIRECT");
                }

                Log::insert_earnings($slot_sponsor->slot_id,$direct_income,"DIRECT","SLOT CREATION",$slot_info->slot_id,$details,1);
                Special_plan::infinity_bonus($slot_sponsor, "DIRECT", $direct_income);
            }

            /* IF GC INCOME IS NOT 0 */
        	if($gc_income != 0)
        	{
                /*LOGS*/

                $currency_id = Tbl_currency::where("currency_abbreviation","GC")->where("archive",0)->first() ? Tbl_currency::where("currency_abbreviation","GC")->where("archive",0)->first()->currency_id : null;
                $details = "";
                if($currency_id != null)
                {
                    Log::insert_wallet($slot_sponsor->slot_id,$gc_income,"DIRECT GC",$currency_id);
                    Log::insert_earnings($slot_sponsor->slot_id,$gc_income,"DIRECT GC","SLOT CREATION",$slot_info->slot_id,$details,1,$currency_id);
                }
            }
            
            /* IF DIRECT BONUS*/
            $direct_bonus = Tbl_direct_bonus::where("archive",0)->get();
            foreach ($direct_bonus as $key => $bonus) 
            {
                $check = Tbl_slot::where("slot_id",$slot_sponsor->slot_id)->first();
               if($check["bonus_no"] < $bonus["hierarchy"])
               {
                   $total_direct_earning =  Tbl_earning_log::where("earning_log_plan_type","DIRECT")->where("earning_log_slot_id",$slot_sponsor->slot_id)->sum("earning_log_amount");
                   if($total_direct_earning >= $bonus["direct_bonus_checkpoint"])
                   {
                       if($bonus["direct_bonus_amount"] != 0)
                       {
                           $details = "";
                           Log::insert_wallet($slot_sponsor->slot_id,$bonus["direct_bonus_amount"],"DIRECT BONUS");
                           Log::insert_earnings($slot_sponsor->slot_id,$bonus["direct_bonus_amount"],"DIRECT BONUS","SLOT CREATION",$slot_info->slot_id,$details,1);
                           Tbl_slot::where("slot_id",$slot_sponsor->slot_id)->update(["bonus_no"=>$bonus["hierarchy"]]);
                       }
                   }
               }
            }
        }
	}

    public static function indirect($slot_info)
    {
        $slot_tree         = Tbl_tree_sponsor::where("sponsor_child_id",$slot_info->slot_id)->where("sponsor_parent_id", "!=", 1)->where("sponsor_level","!=",1)->orderby("sponsor_level", "asc")->get();
        /* RECORD ALL INTO A SINGLE VARIABLE */
        /* CHECK IF LEVEL EXISTS */
        
        $indirect_level = Tbl_membership::where('membership_id', $slot_info->slot_membership)->first()->membership_indirect_level;
        $gained_level = [];
        $all_levels = range(2, $indirect_level + 1);

        foreach($slot_tree as $key => $tree)
        {
            /* GET SPONSOR AND GET INDIRECT BONUS INCOME */
            $slot_sponsor   = Tbl_slot::where("slot_id",$tree->sponsor_parent_id)->first();
            $indirect_bonus = Tbl_membership_indirect_level::where("membership_id",$slot_sponsor->slot_membership)->where("membership_entry_id",$slot_info->slot_membership)->where("membership_level",$tree->sponsor_level)->first();
            if($indirect_bonus)
            {
                $indirect_bonus = $indirect_bonus->membership_indirect_income;
            }
            else
            {
                $indirect_bonus = 0;
            }
            if($indirect_bonus != 0)
            {
                $indirect_settings = Tbl_indirect_settings::first();
                $_proceed = false;
                if($indirect_settings->indirect_points_enable == 1)
                {
                    if($indirect_settings->indirect_points_minimum_conversion > 0)
                    {
                        $_IP = Tbl_currency::where("currency_abbreviation","IP")->first();
                        if($_IP)
                        {
                            $details = "";
                            Log::insert_wallet($slot_sponsor->slot_id,$indirect_bonus,"INDIRECT",$_IP->currency_id);
                            Log::insert_earnings($slot_sponsor->slot_id,$indirect_bonus,"INDIRECT","SLOT CREATION",$slot_info->slot_id,$details,$tree->sponsor_level,$_IP->currency_id);

                            $_IP_wallet = Tbl_slot::where("tbl_slot.slot_id",$slot_sponsor->slot_id)->Wallet($_IP->currency_id)->first();
                            if ($_IP_wallet->wallet_amount >= $indirect_settings->indirect_points_minimum_conversion) 
                            {
                                Log::insert_wallet($slot_sponsor->slot_id,$_IP_wallet->wallet_amount * -1,"INDIRECT_CONVERSION",$_IP->currency_id);
                                Log::insert_earnings($slot_sponsor->slot_id,$_IP_wallet->wallet_amount * -1,"INDIRECT_CONVERSION","SLOT CREATION",$slot_info->slot_id,$details,$tree->sponsor_level,$_IP->currency_id);

                                Log::insert_wallet($slot_sponsor->slot_id,$_IP_wallet->wallet_amount,"INDIRECT_POINTS_CONVERSION");
                                Log::insert_earnings($slot_sponsor->slot_id,$_IP_wallet->wallet_amount,"INDIRECT_POINTS_CONVERSION","SLOT CREATION",$slot_info->slot_id,$details,$tree->sponsor_level);
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
                    $details = "";

                    $cd_package = Tbl_membership::where('hierarchy',1)->where('archive',0)->pluck('membership_id')->first();
                    $cd_slot_info = Tbl_slot::where('slot_id',$slot_sponsor->slot_id)->first();
                    if($cd_slot_info->slot_membership == $cd_package)
                    {
                        $cd_earnings    = $indirect_bonus * 0.2;
                        $indirect_bonus1 = $indirect_bonus - $cd_earnings;

                        Log::insert_wallet($slot_sponsor->slot_id,$indirect_bonus1,"INDIRECT");
                        Log::insert_wallet($slot_sponsor->slot_id,$cd_earnings,"INDIRECT",18);
                    }
                    else
                    {
                        Log::insert_wallet($slot_sponsor->slot_id,$indirect_bonus,"INDIRECT");
                    }

                    Log::insert_earnings($slot_sponsor->slot_id,$indirect_bonus,"INDIRECT","SLOT CREATION",$slot_info->slot_id,$details,$tree->sponsor_level);
                    $gained_level[] = $tree->sponsor_level;
                    Special_plan::infinity_bonus($slot_sponsor, "INDIRECT", $indirect_bonus);
                }
            }
            // /* CHECK IF BONUS IS ZERO */
            // if($indirect_bonus != 0)
            // {
            //     /*LOGS*/
            //     $details = "";
            //     Log::insert_wallet($slot_sponsor->slot_id,$indirect_bonus,"INDIRECT");
            //     Log::insert_earnings($slot_sponsor->slot_id,$indirect_bonus,"INDIRECT","SLOT CREATION",$slot_info->slot_id,$details,$tree->sponsor_level);
            // }
        } 
        if (count($gained_level)) {
            Self::ungained_earnings_based_on_levels($all_levels, $gained_level, $slot_info, "indirect");
        }
    }

    public static function membership_upgrade($slot_info)
    {
        $check_kind_of_upgrade = Tbl_membership_upgrade_settings::first() ? Tbl_membership_upgrade_settings::first()->membership_upgrade_settings_method : "direct_downlines";
        if($check_kind_of_upgrade == "direct_downlines")
        {
            $slot_tree         = Tbl_tree_sponsor::where("sponsor_child_id",$slot_info->slot_id)->orderby("sponsor_level", "Desc")->get();
            
            foreach ($slot_tree as $key => $tree) 
            {
                $slot_sponsor2 = Tbl_slot::where('slot_id', $tree->sponsor_parent_id)->join('tbl_membership', 'tbl_slot.slot_membership', '=', 'tbl_membership.membership_id')->first();
                $get_next_hierarchy2 = Tbl_membership::where('hierarchy', '>', $slot_sponsor2->hierarchy)->where('archive', 0)->orderBy('hierarchy', 'asc')->first();
                $check_sponsored_slots2 = Tbl_slot::where('slot_sponsor', $slot_sponsor2->slot_id)->count();
                $check_downline_slots2  = Tbl_tree_sponsor::where('sponsor_parent_id', $slot_sponsor2->slot_id)->count();
                if($get_next_hierarchy2)
                {
                    $check_lockdown = Self::lockdown_checker($tree->sponsor_parent_id,1);
                    if($check_sponsored_slots2 >= $get_next_hierarchy2->required_directs && $check_downline_slots2 >= $get_next_hierarchy2->required_downlines && $check_lockdown == false)
                    {
                        $log2['slot_id'] = $slot_sponsor2->slot_id;
                        $log2['old_membership_id'] = $slot_sponsor2->slot_membership;
                        $log2['new_membership_id'] = $get_next_hierarchy2->membership_id;
                        $log2['upgraded_at']       = Carbon::now();
                        
                        DB::table('tbl_membership_upgrade_logs')->insert($log2);
                        
                        $update_membership2['slot_membership'] = $get_next_hierarchy2->membership_id;
                        Tbl_slot::where("slot_id",$slot_sponsor2->slot_id)->update($update_membership2);    
                        if($slot_sponsor2->flushout_enable == 1)
                        {
                            $points2['slot_right_points'] = 0;
                            $points2['slot_left_points'] = 0;
                            $update2['slot_right_points'] = $slot_sponsor2->slot_right_points;
                            $update2['slot_left_points'] = $slot_sponsor2->slot_left_points;
                            $receive2["left"] = (-1 * $update2['slot_left_points']);
                            $receive2["right"] = (-1 * $update2['slot_right_points']);
                            $old2["left"] = $slot_sponsor2->slot_left_points;
                            $old2["right"] = $slot_sponsor2->slot_right_points;
                            $new2["left"] = 0;
                            $new2["right"] = 0;
                            $flushout_points2["left"] = $slot_sponsor2->slot_left_points;
                            $flushout_points2["right"] = $slot_sponsor2->slot_right_points;
                            $plan_type_left2 = "BINARY_LEFT_FLUSHOUT";
                            Log::insert_points($slot_sponsor2->slot_id,(-1 * $update2['slot_left_points']),$plan_type_left2,$slot_info->slot_id, 0);                                 
                            $plan_type_right2 = "BINARY_RIGHT_FLUSHOUT";
                            Log::insert_points($slot_sponsor2->slot_id,(-1 * $update2['slot_right_points']),$plan_type_right2,$slot_info->slot_id, 0);   
                            Tbl_slot::where('slot_id', $slot_sponsor2->slot_id)->update($points2);
                            Log::insert_binary_points($slot_sponsor2->slot_id,$receive2,$old2,$new2,$slot_sponsor2->slot_id,0,0,0,"Membership Upgrade",0,$flushout_points2,0);
                        }
                    }
                }
            }
        }
        else 
        {
            $given_upgrade_points =Tbl_membership::where("membership_id",$slot_info->slot_membership)->first()->given_upgrade_points;
            $slot_sponsor = Tbl_slot::where('slot_id', $slot_info->slot_sponsor)->join('tbl_membership', 'tbl_slot.slot_membership', '=', 'tbl_membership.membership_id')->first();
            $get_next_hierarchy = Tbl_membership::where('hierarchy', '>', $slot_sponsor->hierarchy)->where('archive', 0)->orderBy('hierarchy', 'asc')->get();
            $total_points = $slot_sponsor->slot_upgrade_points +  $given_upgrade_points;
            Tbl_slot::where('slot_id',$slot_sponsor->slot_id)->update(["slot_upgrade_points" => $total_points]);
            if($get_next_hierarchy)
            {
                $check_lockdown = Self::lockdown_checker($slot_sponsor->slot_id,1);
                if($check_lockdown == false)
                {
                    foreach ($get_next_hierarchy as $key => $value) 
                    {
                        if($total_points>= $value->required_upgrade_points)
                        {
                            $slot_membership = Tbl_slot::where('slot_id', $slot_info->slot_sponsor)->join('tbl_membership', 'tbl_slot.slot_membership', '=', 'tbl_membership.membership_id')->first();
                            $log['slot_id'] = $slot_sponsor->slot_id;
                            $log['old_membership_id'] = $slot_sponsor->slot_membership;
                            $log['new_membership_id'] = $value->membership_id;
                            $log['upgraded_at']       = Carbon::now();
                            
                            DB::table('tbl_membership_upgrade_logs')->insert($log);
                            
                            $update_membership['slot_membership'] = $value->membership_id;
                            Tbl_slot::where("slot_id",$slot_sponsor->slot_id)->update($update_membership);    
    
                            if($slot_membership->flushout_enable == 1)
                            {
                                $points['slot_right_points'] = 0;
                                $points['slot_left_points'] = 0;
                                $update['slot_right_points'] = $slot_sponsor->slot_right_points;
                                $update['slot_left_points'] = $slot_sponsor->slot_left_points;
                                $receive["left"] = (-1 * $update['slot_left_points']);
                                $receive["right"] = (-1 * $update['slot_right_points']);
                                $old["left"] = $slot_sponsor->slot_left_points;
                                $old["right"] = $slot_sponsor->slot_right_points;
                                $new["left"] = 0;
                                $new["right"] = 0;
                                $flushout_points["left"] = $slot_sponsor->slot_left_points;
                                $flushout_points["right"] = $slot_sponsor->slot_right_points;
                                $plan_type_left = "BINARY_LEFT_FLUSHOUT";
                                Log::insert_points($slot_sponsor->slot_id,(-1 * $update['slot_left_points']),$plan_type_left,$slot_info->slot_id, 0);                                 
                                $plan_type_right = "BINARY_RIGHT_FLUSHOUT";
                                Log::insert_points($slot_sponsor->slot_id,(-1 * $update['slot_right_points']),$plan_type_right,$slot_info->slot_id, 0);   
                                Tbl_slot::where('slot_id', $slot_sponsor->slot_id)->update($points);
                                Log::insert_binary_points($slot_sponsor->slot_id,$receive,$old,$new,$slot_sponsor->slot_id,0,0,0,"Membership Upgrade",0,$flushout_points,0);
                            }
                        }
                    }
                }
            }
        }
        
    }

    public static function board($slot_info)
    {
        $check_plan_settings = Tbl_mlm_plan::where('mlm_plan_code', 'BOARD')->first();

        if($check_plan_settings->mlm_plan_trigger == 'Slot Creation')
        {
            //FIFO comes here
        
            if($slot_info != null)
            {
                $position = Slot::get_board_auto_position(1);
                $sponsor_info = Tbl_slot::where('slot_id', $position->slot_id)->first();
                $data['slot_placement']   = $sponsor_info->slot_no;
                $data['slot_position']   = $position->position;
                $data['slot_code']        = $slot_info->slot_no;
                Slot::place_slot($data,"board", 0);
                Self::check_graduate($slot_info, 1);
            }


        }
        else
        {
            //FOLLOW ME
            if($slot_info != null)
            {
                Self::check_graduate($slot_info, 1);
            }
            
        }
    }

    public static function check_graduate($slot_info, $board_level)
    {
        $board_settings = Self::check_board_settings('select');
        $graduation_count = pow(2, ($board_settings->board_depth));
        $check_upline = Tbl_mlm_board_placement::where('placement_child_id', $slot_info->slot_id)
                                                ->where('board_level', $board_level)
                                                ->where('placement_level', $board_settings->board_depth)->first();
        if($check_upline)
        {
            $check_if_graduated = Tbl_mlm_board_slot::where('slot_id', $check_upline->placement_parent_id)
            ->where('board_level', $board_level)
            ->where('graduated', 0)->first();
            if($check_if_graduated)
            {
                $check_for_graduation = Tbl_mlm_board_placement::where('placement_parent_id' ,$check_upline->placement_parent_id)
                            ->where('board_level', $board_level)
                            ->where('placement_level', $board_settings->board_depth)
                            ->count();
                if($check_for_graduation == $graduation_count)
                {
                    Tbl_mlm_board_slot::where('slot_id', $check_upline->placement_parent_id)->where('board_level', $board_level)->update(['graduated' => 1]);
                    $grad_bonus = Tbl_mlm_board_settings::where('board_level', $board_level)->first();
                    Log::insert_wallet($check_upline->placement_parent_id,$grad_bonus->graduation_bonus,'BOARD');
                    Log::insert_earnings($check_upline->placement_parent_id,$grad_bonus->graduation_bonus,'BOARD','SLOT PLACEMENT',$slot_info->slot_id,$slot_info->slot_membership,$board_level);
                    $board_level = $board_level + 1;
                    $check_next_level = Slot::get_board_auto_position($board_level);
                    if($check_next_level == null)
                    {
                        $insert_board_slot['slot_id'] = $check_upline->placement_parent_id;
                        $insert_board_slot['placement'] = 0;
                        $insert_board_slot['placement_position'] = "LEFT";
                        $insert_board_slot['board_level'] = $board_level;
                        DB::table('tbl_mlm_board_slot')->insert($insert_board_slot);
                    }
                    else
                    {
                        if(isset($check_next_level->position))
                        {
                            $insert_board_slot['slot_id'] = $check_upline->placement_parent_id;
                            $insert_board_slot['placement'] = $check_next_level->slot_id;
                            $insert_board_slot['placement_position'] = $check_next_level->position;
                            $insert_board_slot['board_level'] = $board_level;
                            $graduate_id = DB::table('tbl_mlm_board_slot')->insertGetId($insert_board_slot);
                            Tree::insert_board_placement($check_next_level->slot_id, $check_upline->placement_parent_id, 1, $board_level);

                            $graduate_slot = Tbl_mlm_board_slot::where('board_slot_id', $graduate_id)->first();
                            $graduate_placement = Tbl_mlm_board_placement::where('placement_child_id', $graduate_slot->slot_id)
                                ->where('board_level', $graduate_slot->board_level)
                                ->where('placement_level', $board_settings->board_depth)->first();
                            if($graduate_placement)
                            {
                                $check_if_parent_graduated = Tbl_mlm_board_placement::where('placement_parent_id', $graduate_placement->placement_parent_id)
                                ->where('board_level', $board_level)
                                ->where('placement_level', $board_settings->board_depth)->count();

                                if($check_if_parent_graduated == $graduation_count)
                                {
                                    Self::parent_graduation($graduate_slot, $board_level);
                                }
                            }
                        }
                       

                    }
                }
            }
        }
    }

    public static function parent_graduation($graduate, $board_level)
    {
        $board_settings = Self::check_board_settings('select');
        $graduation_count = pow(2, ($board_settings->board_depth));
        $parent = Tbl_mlm_board_slot::where('slot_id', $graduate->placement)->where('board_level', $board_level)->first();
        Tbl_mlm_board_slot::where('slot_id', $parent->slot_id)->where('board_level', $board_level)->update(['graduated' => 1]);
        $grad_bonus = Tbl_mlm_board_settings::where('board_level', $board_level)->first();
        Log::insert_wallet($parent->slot_id,$grad_bonus->graduation_bonus,'BOARD');
        Log::insert_earnings($parent->slot_id,$grad_bonus->graduation_bonus,'BOARD','SLOT PLACEMENT',$graduate->slot_id,$graduate->slot_membership,$board_level);
        
        $board_level = $board_level + 1;
        $check_next_level = Slot::get_board_auto_position($board_level);
        if($check_next_level == null)
        {
            $insert_board_slot['slot_id'] = $parent->slot_id;
            $insert_board_slot['placement'] = 0;
            $insert_board_slot['placement_position'] = "LEFT";
            $insert_board_slot['board_level'] = $board_level;
            DB::table('tbl_mlm_board_slot')->insert($insert_board_slot);
        }
        else
        {
            $insert_board_slot['slot_id'] = $parent->slot_id;
            $insert_board_slot['placement'] = $check_next_level->slot_id;
            $insert_board_slot['placement_position'] = $check_next_level->position;
            $insert_board_slot['board_level'] = $board_level;
            $graduate_id = DB::table('tbl_mlm_board_slot')->insertGetId($insert_board_slot);
            Tree::insert_board_placement($check_next_level->slot_id, $parent->slot_id, 1, $board_level);

            $graduate_slot = Tbl_mlm_board_slot::where('board_slot_id', $graduate_id)->first();
            $graduate_placement = Tbl_mlm_board_placement::where('placement_child_id', $graduate_slot->slot_id)
                ->where('board_level', $graduate_slot->board_level)
                ->where('placement_level', $board_settings->board_depth)->first();
            if($graduate_placement)
            {
                $check_if_parent_graduated = Tbl_mlm_board_placement::where('placement_parent_id', $graduate_placement->placement_parent_id)
                ->where('board_level', $board_level)
                ->where('placement_level', $board_settings->board_depth)->count();

                if($check_if_parent_graduated == $graduation_count)
                {
                    Self::parent_graduation($graduate_slot, $board_level);
                }
            }
                    
        }
        
    }

    // {
        // $board_settings = Self::check_board_settings('select');
        // $counts_to_grad = pow(2, ($board_settings->board_depth));
        // $count_children = Tbl_mlm_board_placement::select('placement_parent_id',DB::raw('count(*) as total'))
        //                 ->groupBy('placement_parent_id')
        //                 ->where('placement_level', $board_settings->board_depth)
        //                 ->where('board_level', 1)
        //                 ->where('graduated', 0)
        //                 ->get();
        // foreach($count_children as $key => $value)
        // {
        //     if($value->total == $counts_to_grad)
        //     {
        //         // $grad_bonus = Tbl_mlm_board_settings::where('board_level', $level)->first();
        //         $graduate = Tbl_mlm_board_placement::where('placement_child_id', $value->placement_parent_id)->where('placement_level', 1)->first();
                
        //         $grad_level = $graduate->board_level + 1;
        //         $check_next_board = Tbl_mlm_board_placement::where('board_level', $grad_level)->first();
        //         if($check_next_board)
        //         {

        //             $level = $level + 1;
        //             $position = Slot::get_board_auto_position($level);
        //             Tree::insert_board_placement($position, $graduate, 1, $level);
        //             Self::board(null, $level);
        //         }
        //         else
        //         {
        //             $insert_grad['placement_parent_id'] = $graduate->placement_parent_id;
        //             $insert_grad['placement_child_id'] = $graduate->placement_child_id;
        //             $insert_grad['placement_level'] = 1;
        //             $insert_grad['board_level'] = $graduate->board_level +1;
        //             $insert_grad['placement_position'] = "Head";

        //             Tbl_mlm_board_placement::insert($insert_grad);

        //         }                


        //         // Log::insert_wallet($value->placement_parent_id,$grad_bonus,'BOARD');
        //         // Log::insert_earnings($value->placement_parent_id,$grad_bonus,'BOARD','SLOT PLACEMENT',$slot_info->slot_id,$slot_info->slot_membership,$board_settings->board_depth);

        //     }
        // }

    // }

    // public static function check_if_graduated($level, $slot_info)
    // {
    //     $graduated = 0;
    //     $board_settings = Self::check_board_settings('select');
    //     $counts_to_grad = pow(2, ($board_settings->board_depth));
    //     $count_children = Tbl_mlm_board_placement::select('placement_parent_id',DB::raw('count(*) as total'))
    //                     ->groupBy('placement_parent_id')
    //                     ->where('placement_level', $board_settings->board_depth)
    //                     ->where('board_level', $level)
    //                     ->get();
    //     foreach($count_children as $key => $value)
    //     {
    //         if($value->placement_parent_id)
    //         {
    //             if($value->total == $counts_to_grad)
    //             {
    //                 $grad_bonus = Tbl_mlm_board_settings::where('board_level', $level)->first()->graduation_bonus;
    //                 $graduate = Tbl_mlm_board_placement::where('placement_child_id', $value->placement_parent_id)->where('placement_level', 1)->first();
    //                 $next_graduate = Tbl_mlm_board_placement::where('placement_parent_id', $value->placement_parent_id)->where('board_level', $graduate->board_level)->first();
    //                 if($next_graduate)
    //                 {
    //                     $level = $level + 1;
    //                     $asd = Slot::get_board_auto_position($level);
    //                     dd(456);
    //                 }
                
    //                 $grad_level = $graduate->board_level + 1;
    //                 $check_next_board = Tbl_mlm_board_placement::where('board_level', $grad_level)->first();
                    
    //             }
    //         }
    //     }

    // }


    
    public static function check_board_settings($type = "get")
    {
        if($type == "get")
        {
            $settings = Tbl_mlm_board_settings::get();

        }
        else
        {
            $settings = Tbl_mlm_board_settings::first();
        }

        return $settings;
    }

    // public static function cashback($slot_info)
    // {

    //     $slot_tree         = Tbl_tree_sponsor::where("sponsor_child_id",$slot_info->slot_id)->orderby("sponsor_level", "DESC")->get();
    //     foreach($slot_tree as $key => $tree)
    //     {
    //         $slot_sponsor   = Tbl_slot::where("slot_id",$tree->sponsor_parent_id)->first();
    //         $cashback_percentage = Tbl_membership_cashback_level::where("membership_id",$slot_sponsor->slot_membership)->where("membership_level",$tree->sponsor_level)->first();
    //         if($cashback_percentage)
    //         {
    //             $cashback_money = ($cashback_percentage->membership_cashback_income/100) * 1;
    //         }
    //         else
    //         {
    //             $cashback_money = 0;
    //         }
    //         if($cashback_money != 0)
    //         {
    //             $cashback_money = round($cashback_money,2);
    //             Log::insert_wallet($slot_sponsor->slot_id,$cashback_money,"PURCHASE CASHBACK");
    //         }
    //     } 
        
    // }
    //  $details = "";
    // Log::insert_earnings($graduate->placement_child_id,$check_grad_bonus->graduation_bonus,"Graduation Bonus","SLOT CREATION",$graduate->placement_parent_id,$details);

    public static function monoline($slot_info)
    {
        //dd($slot_info);

        /*amount of membeship fee*/


        $_item       = Tbl_item::where("membership_id",$slot_info->slot_membership)->where("archived",0)->first();
        // dd($_item["item_price"]);
        $_monoline_percent = Tbl_mlm_monoline_settings::where("membership_id",$slot_info->slot_membership)->first();
        $amount = (($_item["item_price"])/100)*$_monoline_percent["monoline_percent"];   
        $slot_tree         = Tbl_tree_sponsor::where("sponsor_child_id",$slot_info->slot_id)->orderby("sponsor_level", "DESC")->get();
        $divisor = count($slot_tree);

        /*check if sponsor_id_parents have record*/
        // dd($slot_tree,$slot_info->slot_id);
        foreach($slot_tree as $key => $tree)
        {
            $slot_sponsor   = Tbl_slot::where("slot_id",$tree->sponsor_parent_id)->first();
            $check          = Tbl_monoline_points::where("slot_id",$slot_sponsor->slot_id)->first();
            if(!$check)
            {
                $settings["slot_id"]                 = $slot_sponsor->slot_id;
                $settings["monoline_points"]         = 0;
                $settings["monoline_grad_stat"]      = 0;
                $settings["excess_monoline_points"]  = 0;
                Tbl_monoline_points::insert($settings);
            }
            $check_stat  = Tbl_slot::JoinMonolinePoints()->where("tbl_slot.slot_id",$slot_sponsor->slot_id)->first();
            if ($check_stat["monoline_grad_stat"]  != 0) 
            {

                $divisor--;  
            }
        }
        //dd($divisor);
        
        
        // && $amount != 0
        if ($divisor != 0 && $amount != 0)
        {
            foreach ($slot_tree as $key => $tree) 
            {
                $slot_sponsor   = Tbl_slot::JoinMonolinePoints()->where("tbl_slot.slot_id",$tree->sponsor_parent_id)->first();
                //dd($slot_sponsor);
                $slot_sponsor_membership =  Tbl_mlm_monoline_settings::where("membership_id",$slot_sponsor->slot_membership)->first();
                //dd($slot_sponsor["monoline_points"]);
                if ($slot_sponsor["monoline_grad_stat"] == 0)
                {
                    $earn        = $amount/$divisor;
                    $max_earning = $slot_sponsor["monoline_points"] +  round($earn,2);
                    // dd($slot_sponsor["monoline_points"]);
                    if ($max_earning >= $slot_sponsor_membership["max_price"])
                    {
                        $earn                               = $earn - ($max_earning - $slot_sponsor_membership["max_price"]);
                        $settings["slot_id"]                = $tree->sponsor_parent_id;
                        $settings["monoline_points"]        = round($max_earning,2);
                        $settings["excess_monoline_points"] = round($max_earning - $_monoline_percent["max_price"],2);
                        $settings["monoline_grad_stat"]     = 1;

                        $lock["slot_id"] 		= $tree->sponsor_parent_id;
                        $lock["amount"] 		= round($max_earning,2);
                        $lock["type"] 			= "MONOLINE_POINTS";
                        $is_lockdown = Log::lockdown_logs($lock);
                        if($is_lockdown == false)
		                {
                            Tbl_monoline_points::where("slot_id",$tree->sponsor_parent_id)->update($settings);
                        }
                    }
                    else
                    {
                        $settings2["monoline_points"] = round($max_earning,2);
                        $lock["slot_id"] 		= $tree->sponsor_parent_id;
                        $lock["amount"] 		= round($max_earning,2);
                        $lock["type"] 			= "MONOLINE_POINTS";
                        $is_lockdown = Log::lockdown_logs($lock);
                        if($is_lockdown == false)
		                {
                            Tbl_monoline_points::where("slot_id",$tree->sponsor_parent_id)->update($settings2);
                        }
                    }
                }
                else
                {
                    $earn = 0;
                }

                if($earn != 0)
                {
                    $earn = round($earn,2);
                    $details = "";
                    Log::insert_wallet($tree->sponsor_parent_id,$earn,"MONOLINE");
                    Log::insert_earnings($tree->sponsor_parent_id,$earn,"MONOLINE","SLOT CREATION",$slot_info->slot_id,$details,$tree->sponsor_level);
                }
            }   
        }    
    }
    public static function pass_up($slot_info)
    {
        $_pass_up_settings   = Tbl_mlm_pass_up_settings::where("membership_id",$slot_info->slot_membership)->first();
        $full_slot_info      = Tbl_slot::where("slot_id",$slot_info->slot_id)->leftjoin('tbl_membership','tbl_membership.membership_id','slot_membership')->first();
       


        /*checking the direction of pass up and direct in settings*/
        if($_pass_up_settings["direct_direction"] == 0 && $_pass_up_settings["pass_up_direction"] == 1)
        {
            $slot_sponsor   = Tbl_slot::where("slot_id",$slot_info->slot_id)->leftjoin('tbl_membership','tbl_membership.membership_id','slot_membership')->first();

            $slot_tree      = Tbl_tree_sponsor::where("sponsor_child_id",$slot_info->slot_id)->orderby("sponsor_level", "ASC")->get();
            $pass = 0; 
            $pos = 0;
            $self = 0;
            foreach ($slot_tree as $key => $tree) 
            {
                $full_sponsor_info       = Tbl_slot::where("slot_id",$tree->sponsor_parent_id)->leftjoin('tbl_membership','tbl_membership.membership_id','slot_membership')->first();
                $count_position          = Tbl_tree_sponsor::where("sponsor_parent_id",$slot_sponsor->slot_sponsor)->where("sponsor_level",1)->orderby("tree_sponsor_id", "ASC")->get();
                
                $pass_up_earnings        = Tbl_pass_up_combination_income::where('membership_id',$full_sponsor_info->slot_membership)->where('membership_entry_id',$full_slot_info->slot_membership)->pluck('pass_up_income')->first();
                $pass_up_direct_earnings = Tbl_pass_up_direct_combination_income::where('membership_id',$full_sponsor_info->slot_membership)->where('membership_entry_id',$full_slot_info->slot_membership)->pluck('pass_up_direct_income')->first();
                

                foreach ($count_position as $key => $count) 
                {
                    if ($count->sponsor_child_id != $slot_sponsor->slot_id)
                    {
                        $pos = $pos + 1;
                    }
                    else
                    {

                        $self = $pos + 1;
                    }                    
                }
                if($self >  $_pass_up_settings["pass_up"] && $self < $_pass_up_settings["direct"])
                {
                    $pos  = 0;
                    $self = 0;
                    goto end;
                }
                if ($self >= $_pass_up_settings["direct"]) 
                {
                    if($pass == 1)
                    {
                        if($pass_up_earnings != 0 )
                        {
                            $details = "";

                            // if($full_slot_info->hierarchy >= $full_sponsor_info->hierarchy)
                            // {
                            //     $_pass_up_settings   = Tbl_mlm_pass_up_settings::where("membership_id",$full_sponsor_info->slot_membership)->first();
                            // }

                            $cd_package = Tbl_membership::where('hierarchy',1)->where('archive',0)->pluck('membership_id')->first();
                            $cd_slot_info = Tbl_slot::where('slot_id',$slot_sponsor->slot_sponsor)->first();
                            if($cd_slot_info->slot_membership == $cd_package)
                            {
                                $cd_earnings    = $pass_up_earnings * 0.2;
                                $cd_pass_up     = $pass_up_earnings - $cd_earnings;

                                Log::insert_wallet($slot_sponsor->slot_sponsor,$cd_pass_up,"PASS_UP");
                                Log::insert_wallet($slot_sponsor->slot_sponsor,$cd_earnings,"PASS_UP",18);
                            }
                            else
                            {
                                Log::insert_wallet($slot_sponsor->slot_sponsor,$pass_up_earnings,"PASS_UP");
                            }
                            Log::insert_earnings($slot_sponsor->slot_sponsor,$pass_up_earnings,"PASS_UP","SLOT CREATION",$slot_info->slot_id,$details,$tree->sponsor_level);
                        }
                       
                        goto end;
                    }
                    if($pass == 0)
                    {
                        if($pass_up_direct_earnings != 0)
                        {
                            $details = "";

                            // if($full_slot_info->hierarchy >= $full_sponsor_info->hierarchy)
                            // {
                            //     $_pass_up_settings   = Tbl_mlm_pass_up_settings::where("membership_id",$full_sponsor_info->slot_membership)->first();
                            // }

                            $cd_package = Tbl_membership::where('hierarchy',1)->where('archive',0)->pluck('membership_id')->first();
                            $cd_slot_info = Tbl_slot::where('slot_id',$slot_sponsor->slot_sponsor)->first();
                            if($cd_slot_info->slot_membership == $cd_package)
                            {
                                $cd_earnings    = $pass_up_direct_earnings * 0.2;
                                $cd_pass_up_direct = $pass_up_direct_earnings - $cd_earnings;

                                Log::insert_wallet($slot_sponsor->slot_sponsor,$cd_pass_up_direct,"DIRECT");
                                Log::insert_wallet($slot_sponsor->slot_sponsor,$cd_earnings,"DIRECT",18);
                            }
                            else
                            {
                                Log::insert_wallet($slot_sponsor->slot_sponsor,$pass_up_direct_earnings,"DIRECT");
                            }
                            
                            Log::insert_earnings($slot_sponsor->slot_sponsor,$pass_up_direct_earnings,"DIRECT","SLOT CREATION",$slot_info->slot_id,$details,$tree->sponsor_level);

                        }
                        goto end;
                    }
                    
                }
                if ($self <= $_pass_up_settings["pass_up"]) 
                {
                    $pass = 1;
                    $slot_sponsor   = Tbl_slot::where("slot_id",$slot_sponsor->slot_sponsor)->first();
                    $pos  = 0;
                    $self = 0;
                    goto pass_up_down;
                }
                
                pass_up_down:       
            }
        }
        elseif($_pass_up_settings["direct_direction"] == 1 && $_pass_up_settings["pass_up_direction"] == 0)
        {
            $slot_tree      = Tbl_tree_sponsor::where("sponsor_child_id",$slot_info->slot_id)->orderby("sponsor_level", "ASC")->get();
            $slot_sponsor   = Tbl_slot::where("slot_id",$slot_info->slot_id)->first();
            $pass = 0; 
            foreach ($slot_tree as $key => $tree) 
            {
                $full_sponsor_info       = Tbl_slot::where("slot_id",$tree->sponsor_parent_id)->leftjoin('tbl_membership','tbl_membership.membership_id','slot_membership')->first();
                $count_position          = Tbl_tree_sponsor::where("sponsor_parent_id",$slot_sponsor->slot_sponsor)->where("sponsor_level",1)->orderby("tree_sponsor_id", "ASC")->get();
               
                $pass_up_earnings        = Tbl_pass_up_combination_income::where('membership_id',$full_sponsor_info->slot_membership)->where('membership_entry_id',$full_slot_info->slot_membership)->pluck('pass_up_income')->first();
                $pass_up_direct_earnings = Tbl_pass_up_direct_combination_income::where('membership_id',$full_sponsor_info->slot_membership)->where('membership_entry_id',$full_slot_info->slot_membership)->pluck('pass_up_direct_income')->first();


                $pos = 0;
                foreach ($count_position as $key => $count) 
                {
                    if ($count->sponsor_child_id != $slot_sponsor->slot_id)
                    {
                        $pos++;
                    }
                    else
                    {

                        $self = $pos + 1;
                    }                    
                }
                if($self >  $_pass_up_settings["pass_up"] && $self < $_pass_up_settings["direct"])
                {
                    $pos  = 0;
                    $self = 0;
                    goto end;
                }
                if ($self <= $_pass_up_settings["direct"]) 
                {
                    if($pass == 1)
                    {
                        if($pass_up_earnings != 0 )
                        {
                            $details = "";
                            // if($full_slot_info->hierarchy >= $full_sponsor_info->hierarchy)
                            // {
                            //     $_pass_up_settings   = Tbl_mlm_pass_up_settings::where("membership_id",$full_sponsor_info->slot_membership)->first();
                            // }

                            $cd_package = Tbl_membership::where('hierarchy',1)->where('archive',0)->pluck('membership_id')->first();
                            $cd_slot_info = Tbl_slot::where('slot_id',$slot_sponsor->slot_sponsor)->first();
                            if($cd_slot_info->slot_membership == $cd_package)
                            {
                                $cd_earnings    = $pass_up_earnings * 0.2;
                                $cd_pass_up     = $pass_up_earnings - $cd_earnings;

                                Log::insert_wallet($slot_sponsor->slot_sponsor,$cd_pass_up,"PASS_UP");
                                Log::insert_wallet($slot_sponsor->slot_sponsor,$cd_earnings,"PASS_UP",18);
                            }
                            else
                            {
                                Log::insert_wallet($slot_sponsor->slot_sponsor,$pass_up_earnings,"PASS_UP");
                            }
                            
                            Log::insert_earnings($slot_sponsor->slot_sponsor,$pass_up_earnings,"PASS_UP","SLOT CREATION",$slot_info->slot_id,$details,$tree->sponsor_level);
                        }
                        goto end;
                    }
                    if($pass == 0)
                    {
                        if($pass_up_direct_earnings != 0)
                        {
                            $details = "";

                            if($full_slot_info->hierarchy >= $full_sponsor_info->hierarchy)
                            {
                                $_pass_up_settings   = Tbl_mlm_pass_up_settings::where("membership_id",$full_sponsor_info->slot_membership)->first();
                            }

                            $cd_package = Tbl_membership::where('hierarchy',1)->where('archive',0)->pluck('membership_id')->first();
                            $cd_slot_info = Tbl_slot::where('slot_id',$slot_sponsor->slot_sponsor)->first();
                            if($cd_slot_info->slot_membership == $cd_package)
                            {
                                $cd_earnings    = $pass_up_direct_earnings * 0.2;
                                $cd_pass_up_direct = $pass_up_direct_earnings - $cd_earnings;

                                Log::insert_wallet($slot_sponsor->slot_sponsor,$cd_pass_up_direct,"DIRECT");
                                Log::insert_wallet($slot_sponsor->slot_sponsor,$cd_earnings,"DIRECT",18);
                            }
                            else
                            {
                                Log::insert_wallet($slot_sponsor->slot_sponsor,$pass_up_direct_earnings,"DIRECT");
                            }
                            Log::insert_earnings($slot_sponsor->slot_sponsor,$pass_up_direct_earnings,"DIRECT","SLOT CREATION",$slot_info->slot_id,$details,$tree->sponsor_level);

                        }
                        goto end;
                    }
                    
                }
                if ($self >= $_pass_up_settings["pass_up"]) 
                {
                    $pass = 1;
                    $slot_sponsor   = Tbl_slot::where("slot_id",$slot_sponsor->slot_sponsor)->first();
                    $pos  = 0;
                    $self = 0;
                    goto pass_up_up;
                }
                pass_up_up:       
            }
        }
        end:
    }
    public static function leveling_bonus($slot_info)
    {
        $tree_placement      = Tbl_tree_placement::where("placement_child_id",$slot_info->slot_id)->orderBy("placement_level","ASC")->get();
        
        foreach($tree_placement as $tree)
        {
            $slot_placement  = Tbl_slot::where("slot_id",$tree->placement_parent_id)->first();
            
            // $points_settings = Tbl_leveling_bonus_points::where("membership_id",$slot_placement->slot_membership)->where("slot_id",$slot_placement->slot_id)->get();
            $points_settings = Tbl_leveling_bonus_points::where("slot_id",$slot_placement->slot_id)->first();
            // dd($points_settings);
            if(!$points_settings)
            {
                $_level      = Tbl_membership_leveling_bonus_level::where("membership_id",$slot_placement->slot_membership)->orderby("membership_level","ASC")->get();
     
                foreach ($_level as $key => $level) 
                {
                    $insert["slot_id"]           =    $slot_placement->slot_id;
                    $insert["membership_id"]     =    $level->membership_id;
                    $insert["membership_level"]  =    $level->membership_level;
                    $insert["left_point"]        =    0;
                    $insert["right_point"]       =    0;
                    $insert["claim"]             =    0;

                    Tbl_leveling_bonus_points::insert($insert);
                }
            }
            else 
            {
                Tbl_leveling_bonus_points::where("slot_id",$slot_placement->slot_id)->update(["membership_id"=>$slot_placement->slot_membership]);
            }
            
            $_level_validation   = Tbl_membership_leveling_bonus_level::where("membership_id",$slot_placement->slot_membership)->orderby("membership_level","ASC")->get();
            if($tree->placement_level <= count($_level_validation))
            {
                /*checking for update leveling bonus settings*/
                $check           = Tbl_leveling_bonus_points::where("membership_id",$slot_placement->slot_membership)->where("slot_id",$slot_placement->slot_id)->where("membership_level",$tree->placement_level)->first();
                if(!$check)
                {
                    $insert["slot_id"]           =    $slot_placement->slot_id;
                    $insert["membership_id"]     =    $slot_placement->slot_membership;
                    $insert["membership_level"]  =    $tree->placement_level;
                    $insert["left_point"]        =    0;
                    $insert["right_point"]       =    0;
                    $insert["claim"]             =    0;
                    Tbl_leveling_bonus_points::insert($insert);
                }

                /*adding points for your left and right per level*/

                $add_points      = Tbl_leveling_bonus_points::where("membership_id",$slot_placement->slot_membership)->where("slot_id",$slot_placement->slot_id)->where("membership_level",$tree->placement_level)->first();
                //dd($add_points);
                if($tree->placement_position == "LEFT")
                {
                    $update_left["left_point"]    = $add_points->left_point + 1;
                    Tbl_leveling_bonus_points::where("membership_id",$slot_placement->slot_membership)->where("slot_id",$slot_placement->slot_id)->where("membership_level",$tree->placement_level)->update($update_left);
                }
                if($tree->placement_position == "RIGHT")
                {
                    $update_right["right_point"]    = $add_points->right_point + 1;
                    Tbl_leveling_bonus_points::where("membership_id",$slot_placement->slot_membership)->where("slot_id",$slot_placement->slot_id)->where("membership_level",$tree->placement_level)->update($update_right);
                }

                $pair_points      = Tbl_leveling_bonus_points::where("membership_id",$slot_placement->slot_membership)->where("slot_id",$slot_placement->slot_id)->where("membership_level",$tree->placement_level)->first();

                if($pair_points->left_point >= 1 && $pair_points->right_point >= 1 && $pair_points->claim == 0)
                {
                    $update_claim["claim"]    = 1;
                    Tbl_leveling_bonus_points::where("membership_id",$slot_placement->slot_membership)->where("slot_id",$slot_placement->slot_id)->where("membership_level",$tree->placement_level)->update($update_claim);

                    $_amount      = Tbl_membership_leveling_bonus_level::where("membership_id",$slot_placement->slot_membership)->where("membership_level",$tree->placement_level)->first();
                    $details = "";
                    Log::insert_wallet($slot_placement->slot_id,$_amount->membership_leveling_bonus_income,"LEVELING_BONUS");
                    Log::insert_earnings($slot_placement->slot_id,$_amount->membership_leveling_bonus_income,"LEVELING_BONUS","SLOT PLACEMENT",$tree->placement_child_id,$details,$tree->placement_level);
                }
            }
        }
        
    }
     public static function universal_pool_bonus($slot_info)
    {
        //dd($slot_info);
        $_item       = Tbl_item::where("membership_id",$slot_info->slot_membership)->where("archived",0)->first();
        $_universal_pool_bonus_settings = Tbl_mlm_universal_pool_bonus_settings::where("membership_id",$slot_info->slot_membership)->first();
        $amount = (($_item["item_price"])/100)*$_universal_pool_bonus_settings["percent"];   
        // $slot_id         = Tbl_slot::get();

        // /*check if slot_id have record in universal_pool_bonus_points*/
    
        // foreach($slot_id as $key => $v__slot_id)
        // {
        //     $check          = Tbl_mlm_universal_pool_bonus_points::where("slot_id",$v__slot_id->slot_id)->first();
        //     if(!$check)
        //     {
        //         $settings["slot_id"]                             = $v__slot_id->slot_id;
        //         $settings["universal_pool_bonus_points"]         = 0;
        //         $settings["universal_pool_bonus_grad_stat"]      = 0;
        //         $settings["excess_universal_pool_bonus_points"]  = 0;
        //         Tbl_mlm_universal_pool_bonus_points::insert($settings);
        //     }
        // }
        if ($amount != 0)
        {
            // $date_from = Carbon::now()->startofday();
            // $date_to   = Carbon::now()->endofday();
            // // $slot_id2          = Tbl_mlm_universal_pool_bonus_points::where("universal_pool_bonus_grad_stat",0)->where('tbl_mlm_universal_pool_bonus_points.slot_id','!=',$slot_info->slot_id)
            // //                                                         ->leftJoin("tbl_slot","tbl_slot.slot_id","=","tbl_mlm_universal_pool_bonus_points.slot_id")
            // //                                                         ->whereDate("slot_date_created",">=",$date_from)->whereDate("slot_date_created","<=",$date_to)
            // //                                                         ->select('tbl_slot.slot_id',DB::raw('count(slot_sponsor) as total_sponsor'))
            // //                                                         ->get();
            // // dd($slot_id2);
            // ->select('tbl_top_recruiter.slot_id',DB::raw('sum(total_recruits) as total_recruits'),DB::raw('sum(total_leads) as total_leads'));
            $slot_id2          = Tbl_mlm_universal_pool_bonus_points::where("universal_pool_bonus_grad_stat",0)->where('slot_id','!=',$slot_info->slot_id)->get();
            $maintain          = Tbl_mlm_universal_pool_bonus_maintain_settings::first();
            
            if($maintain->maintain_date != "disable")
            {
                if($maintain->maintain_date == "daily")
                {
                    if($maintain->required_direct != 0)
                    {
                        // dd($slot_id2); 
                        foreach ($slot_id2 as $key => $value) 
                        {
                            $date_from = Carbon::now()->startofday();
                            $date_to   = Carbon::now()->endofday();
                            $check = Tbl_slot::where("slot_sponsor",$value->slot_id)->whereDate("slot_date_created",">=",$date_from)->whereDate("slot_date_created","<=",$date_to)->count();
                            if($check < $maintain->required_direct)
                            {
                                unset($slot_id2[$key]);
                            }
                        }
                    }
                }
                else if($maintain->maintain_date == "weekly")
                {
                    // dd("weekly");

                    if($maintain->required_direct != 0)
                    {
                        foreach ($slot_id2 as $key => $value) 
                        {
                            $date_from = Carbon::now()->startofweek();
                            $date_to   = Carbon::now()->endofweek();
                            $check = Tbl_slot::where("slot_sponsor",$value->slot_id)->whereDate("slot_date_created",">=",$date_from)->whereDate("slot_date_created","<=",$date_to)->count();
                            if($check < $maintain->required_direct)
                            {
                                unset($slot_id2[$key]);
                            }
                        }
                    }
                }
                else if($maintain->maintain_date == "monthly")
                {
                    // dd("monthly");

                    if($maintain->required_direct != 0)
                    {
                        foreach ($slot_id2 as $key => $value) 
                        {
                            $date_from = Carbon::now()->startofmonth();
                            $date_to   = Carbon::now()->endofmonth();
                            $check = Tbl_slot::where("slot_sponsor",$value->slot_id)->whereDate("slot_date_created",">=",$date_from)->whereDate("slot_date_created","<=",$date_to)->count();
                            if($check < $maintain->required_direct)
                            {
                                unset($slot_id2[$key]);
                            }
                        }
                    }
                }
                else if($maintain->maintain_date == "life_time")
                {
                    if($maintain->required_direct != 0)
                    {
                        foreach ($slot_id2 as $key => $value) 
                        {
                            $check = Tbl_slot::where("slot_sponsor",$value->slot_id)->count();
                            if($check < $maintain->required_direct)
                            {
                                unset($slot_id2[$key]);
                            }
                        }
                    }
                }
            }
            if($maintain->binary_maintenace != 0)
            {
                // dd("binary");
                foreach ($slot_id2 as $key => $value2) 
                {
                    $check = Tbl_tree_placement::where("placement_parent_id",$value2->slot_id)->where("placement_level",1)->get();
                    $ctr = 0;
                    foreach ($check as $key => $legs) 
                    {
                        $sponsor = Tbl_slot::where("slot_id",$legs->placement_child_id)->first()->slot_sponsor;
                        if($sponsor == $legs->placement_parent_id)
                        {
                            $ctr = $ctr + 1;
                        } 
                    }
                    if($ctr != 2)
                    {
                        unset($slot_id2[$key]);
                    }
                }
            }
            
            $slot_count       = count($slot_id2);
            // dd($slot_id2); 
            foreach ($slot_id2 as $key => $v__slot_id) 
            {
                    // $v2__slot_id    = Tbl_mlm_universal_pool_bonus_points::where("slot_id",$v__slot_id->slot_id)->first();

                    $slot_id_membership = Tbl_slot::where('slot_id',$v__slot_id->slot_id)->first();
                    $slot_sponsor_membership =  Tbl_mlm_universal_pool_bonus_settings::where("membership_id",$slot_id_membership->slot_membership)->first();
                    $earn = $amount/$slot_count;
                    $max_earning = $v__slot_id["universal_pool_bonus_points"] +  round($earn,2);

                if ($max_earning >= $slot_sponsor_membership["max_price"])
                {
                    $earn                                           = $earn - ($max_earning - $slot_sponsor_membership["max_price"]);
                    $settings["slot_id"]                            = $v__slot_id->slot_id;
                    $settings["universal_pool_bonus_points"]        = round($max_earning,2);
                    $settings["excess_universal_pool_bonus_points"] = round($max_earning - $slot_sponsor_membership["max_price"],2);
                    $settings["universal_pool_bonus_grad_stat"]     = 1;
                    $lock["slot_id"] 		= $v__slot_id->slot_id;
                    $lock["amount"] 		= round($max_earning,2);
                    $lock["type"] 			= "UNIVERSAL_POINTS";
                    $is_lockdown = Log::lockdown_logs($lock);
                    if($is_lockdown == false)
                    {
                        Tbl_mlm_universal_pool_bonus_points::where("slot_id",$v__slot_id->slot_id)->update($settings);
                    }
                }
                else
                {
                    $settings2["slot_id"]                            = $v__slot_id->slot_id;
                    $settings2["universal_pool_bonus_points"]        = round($max_earning,2);
                    $lock["slot_id"] 		= $v__slot_id->slot_id;
                    $lock["amount"] 		= round($max_earning,2);
                    $lock["type"] 			= "UNIVERSAL_POINTS";
                    $is_lockdown = Log::lockdown_logs($lock);
                    if($is_lockdown == false)
                    {
                        Tbl_mlm_universal_pool_bonus_points::where("slot_id",$v__slot_id->slot_id)->update($settings2);
                    }
                }

                if($earn != 0)
                {
                    $earn    = round($earn,2);
                    $details = "";
                    $level   = 0 ;
                    Log::insert_wallet($v__slot_id->slot_id,$earn,"UNIVERSAL_POOL_BONUS");
                    Log::insert_earnings($v__slot_id->slot_id,$earn,"UNIVERSAL_POOL_BONUS","SLOT CREATION",$slot_info->slot_id,$details,$level);
                }
            }   
        }    
    }

    public static function sign_up_bonus($slot_info)
    {
        $get_sponsor_info                       = Tbl_slot::where('slot_id',$slot_info->slot_sponsor)->first();
        
        if($get_sponsor_info)
        {
            $sign_up_bonus                          = Tbl_membership::where("membership_id",$get_sponsor_info->slot_membership)->first()->sign_up_bonus ?? 0;
            if($sign_up_bonus != 0)
            {
                $details = '';
                Log::insert_wallet($slot_info->slot_id,$sign_up_bonus,"SIGN_UP_BONUS",13);
                Log::insert_earnings($slot_info->slot_id,$sign_up_bonus,"SIGN_UP_BONUS","SPECIAL PLAN",$get_sponsor_info->slot_id,$details,0,13);
    
                $insert['slot_id']                  = $slot_info->slot_id;
                $insert['sponsor_id']               = $get_sponsor_info->slot_id;
                $insert['membership_id']            = $get_sponsor_info->slot_membership;
                $insert['date']                     = Carbon::now();
    
                Tbl_signup_bonus_logs::insert($insert);
            }
        }
    }
    public static function passive_unilevel_premium($slot_info)
    {
        $slot_tree         = Tbl_tree_sponsor::where("sponsor_child_id",$slot_info->slot_id)->where("sponsor_level","!=",1)->orderby("sponsor_level", "asc")->get();
        /* RECORD ALL INTO A SINGLE VARIABLE */
        /* CHECK IF LEVEL EXISTS */
        foreach($slot_tree as $key => $tree) {
            /* GET SPONSOR AND GET INDIRECT BONUS INCOME */
            $slot_sponsor   = Tbl_slot::where("slot_id",$tree->sponsor_parent_id)->first();
            $indirect_bonus = Tbl_membership_indirect_level::where("membership_id",$slot_sponsor->slot_membership)->where("membership_entry_id",$slot_info->slot_membership)->where("membership_level",$tree->sponsor_level)->first();
            if($indirect_bonus)  {
                $indirect_bonus = $indirect_bonus->membership_indirect_income;
            }
            else {
                $indirect_bonus = 0;
            }
            /* CHECK IF BONUS IS ZERO */
            if($indirect_bonus != 0) {
                $upline = Tbl_membership::leftJoin("tbl_passive_unilevel_premium","tbl_passive_unilevel_premium.premium_membership_id","tbl_membership.membership_id")->where("tbl_membership.archive",0)->orderby("tbl_passive_unilevel_premium.premium_upline", "desc")->first();
                $downline = Tbl_membership::leftJoin("tbl_passive_unilevel_premium","tbl_passive_unilevel_premium.premium_membership_id","tbl_membership.membership_id")->where("tbl_membership.archive",0)->orderby("tbl_passive_unilevel_premium.premium_downline", "desc")->first();
                if($upline->premium_upline != '' || $upline->premium_upline != null || $upline->premium_upline != 0) {
                    $upline_tree         = Tbl_tree_sponsor::where("sponsor_parent_id",$slot_sponsor->slot_id)->where("sponsor_level",'<=',$upline->premium_upline)->orderby("sponsor_level", "asc")->get();
                    
                    $count = count($upline_tree);
                    if($count != 0) {
                        foreach ($upline_tree as $key => $upline_slot) {
                           
                            $upline_receiver   = Tbl_slot::where("slot_id",$upline_slot->sponsor_child_id)->PassiveUnilevel()->first();
                            if($upline_receiver) {
                                
                                if($upline_receiver->premium_is_enable == 1) {
    
                                    if($upline_receiver->premium_upline >= $upline_slot->sponsor_level) { 
            
                                        if($upline_receiver->premium_earning_cycle == 0) {
            
                                            $date_start = Carbon::now()->startofday();
                                            $date_end = Carbon::now()->endofday();
                                            $check_earning_limit = Tbl_earning_log::where("earning_log_slot_id",$upline_slot->sponsor_child_id)
                                                                                    ->where("earning_log_plan_type","PASSIVE UNILEVEL PREMIUM")
                                                                                    ->whereDate("earning_log_date_created",">=",$date_start)
                                                                                    ->whereDate("earning_log_date_created","<=",$date_end)
                                                                                    ->sum("earning_log_amount");
                                        }
                                        elseif ($upline_receiver->premium_earning_cycle == 1) {
                                            
                                            $date_start = Carbon::now()->startofweek();
                                            $date_end = Carbon::now()->endofweek();
                                            $check_earning_limit = Tbl_earning_log::where("earning_log_slot_id",$upline_slot->sponsor_child_id)
                                                                                    ->where("earning_log_plan_type","PASSIVE UNILEVEL PREMIUM")
                                                                                    ->whereDate("earning_log_date_created",">=",$date_start)
                                                                                    ->whereDate("earning_log_date_created","<=",$date_end)
                                                                                    ->sum("earning_log_amount");
                                        }
                                        elseif ($upline_receiver->premium_earning_cycle == 2) {
                                            
                                            $date_start = Carbon::now()->startofmonth();
                                            $date_end = Carbon::now()->endofmonth();
                                            $check_earning_limit = Tbl_earning_log::where("earning_log_slot_id",$upline_slot->sponsor_child_id)
                                                                                    ->where("earning_log_plan_type","PASSIVE UNILEVEL PREMIUM")
                                                                                    ->whereDate("earning_log_date_created",">=",$date_start)
                                                                                    ->whereDate("earning_log_date_created","<=",$date_end)
                                                                                    ->sum("earning_log_amount");
                                        }
                                        else {
                                            $check_earning_limit = Tbl_earning_log::where("earning_log_slot_id",$upline_slot->sponsor_child_id)
                                                                                    ->where("earning_log_plan_type","PASSIVE UNILEVEL PREMIUM")
                                                                                    ->sum("earning_log_amount");
                                        }
            
                                        if($check_earning_limit < $upline_receiver->premium_earning_limit) {
            
                                            $amount   = ($indirect_bonus * $upline_receiver->premium_percentage) / 100;
                                            $new_earning = $check_earning_limit + $amount;
    
                                            if ($new_earning > $upline_receiver->premium_earning_limit) {
    
                                                $diff = $new_earning - $upline_receiver->premium_earning_limit;
                                                $amount = $amount - $diff;
                                                if($amount != 0) {
                                                     /*LOGS*/
                                                    $details = "";
                                                    Log::insert_wallet($upline_receiver->slot_id,$amount,"PASSIVE_UNILEVEL_PREMIUM");
                                                    Log::insert_earnings($upline_receiver->slot_id,$amount,"PASSIVE_UNILEVEL_PREMIUM","SLOT CREATION",$slot_sponsor->slot_id,$details,$upline_slot->sponsor_level);
                                                }
                                            }
                                            else {
                                                
                                                if($amount != 0) {
                                                    /*LOGS*/
                                                   $details = "";
                                                   Log::insert_wallet($upline_receiver->slot_id,$amount,"PASSIVE_UNILEVEL_PREMIUM");
                                                   Log::insert_earnings($upline_receiver->slot_id,$amount,"PASSIVE_UNILEVEL_PREMIUM","SLOT CREATION",$slot_sponsor->slot_id,$details,$upline_slot->sponsor_level);
                                               }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                if($downline->premium_downline != '' || $downline->premium_downline != null || $downline->premium_downline != 0) {
                    $downline_tree         = Tbl_tree_sponsor::where("sponsor_child_id",$slot_sponsor->slot_id)->where("sponsor_level",'<=',$downline->premium_downline)->orderby("sponsor_level", "asc")->get();
                    // dd($downline_tree);
                    $count = count($downline_tree);
                    if($count != 0) {
                    
                        foreach ($downline_tree as $key => $downline_slot) {
                           
                            $downline_receiver   = Tbl_slot::where("slot_id",$downline_slot->sponsor_parent_id)->PassiveUnilevel()->first();
                            if($downline_receiver) {
                                
                                if($downline_receiver->premium_is_enable == 1) {
                                    if($downline_receiver->premium_downline >= $downline_slot->sponsor_level) { 
                                        
                                        if($downline_receiver->premium_earning_cycle == 0) {
            
                                            $date_start = Carbon::now()->startofday();
                                            $date_end = Carbon::now()->endofday();
                                            $check_earning_limit = Tbl_earning_log::where("earning_log_slot_id",$downline_slot->sponsor_parent_id)
                                                                                    ->where("earning_log_plan_type","PASSIVE UNILEVEL PREMIUM")
                                                                                    ->whereDate("earning_log_date_created",">=",$date_start)
                                                                                    ->whereDate("earning_log_date_created","<=",$date_end)
                                                                                    ->sum("earning_log_amount");
                                        }
                                        elseif ($downline_receiver->premium_earning_cycle == 1) {
                                            
                                            $date_start = Carbon::now()->startofweek();
                                            $date_end = Carbon::now()->endofweek();
                                            $check_earning_limit = Tbl_earning_log::where("earning_log_slot_id",$downline_slot->sponsor_parent_id)
                                                                                    ->where("earning_log_plan_type","PASSIVE UNILEVEL PREMIUM")
                                                                                    ->whereDate("earning_log_date_created",">=",$date_start)
                                                                                    ->whereDate("earning_log_date_created","<=",$date_end)
                                                                                    ->sum("earning_log_amount");
                                        }
                                        elseif ($downline_receiver->premium_earning_cycle == 2) {
                                            
                                            $date_start = Carbon::now()->startofmonth();
                                            $date_end = Carbon::now()->endofmonth();
                                            $check_earning_limit = Tbl_earning_log::where("earning_log_slot_id",$downline_slot->sponsor_parent_id)
                                                                                    ->where("earning_log_plan_type","PASSIVE UNILEVEL PREMIUM")
                                                                                    ->whereDate("earning_log_date_created",">=",$date_start)
                                                                                    ->whereDate("earning_log_date_created","<=",$date_end)
                                                                                    ->sum("earning_log_amount");
                                        }
                                        else {
                                            $check_earning_limit = Tbl_earning_log::where("earning_log_slot_id",$downline_slot->sponsor_parent_id)
                                                                                    ->where("earning_log_plan_type","PASSIVE UNILEVEL PREMIUM")
                                                                                    ->sum("earning_log_amount");
                                        }
            
                                        if($check_earning_limit < $downline_receiver->premium_earning_limit) {
            
                                            $amount   = ($indirect_bonus * $downline_receiver->premium_percentage) / 100;
                                            $new_earning = $check_earning_limit + $amount;
    
                                            if ($new_earning > $downline_receiver->premium_earning_limit) {
    
                                                $diff = $new_earning - $downline_receiver->premium_earning_limit;
                                                $amount = $amount - $diff;
                                                if($amount != 0) {
                                                     /*LOGS*/
                                                    $details = "";
                                                    Log::insert_wallet($downline_receiver->slot_id,$amount,"PASSIVE_UNILEVEL_PREMIUM");
                                                    Log::insert_earnings($downline_receiver->slot_id,$amount,"PASSIVE_UNILEVEL_PREMIUM","SLOT CREATION",$slot_sponsor->slot_id,$details,$downline_slot->sponsor_level);
                                                }
                                            }
                                            else {
                                                
                                                if($amount != 0) {
                                                    /*LOGS*/
                                                   $details = "";
                                                   Log::insert_wallet($downline_receiver->slot_id,$amount,"PASSIVE_UNILEVEL_PREMIUM");
                                                   Log::insert_earnings($downline_receiver->slot_id,$amount,"PASSIVE_UNILEVEL_PREMIUM","SLOT CREATION",$slot_sponsor->slot_id,$details,$downline_slot->sponsor_level);
                                               }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
               
            }
        } 
    }
    public static function lockdown_checker($slot_id,$type = 1)
    {
        if(isset($slot_id) && isset($type))
        {
            $enabled 	 = Tbl_other_settings::where("key","lockdown_enable")->first() ? Tbl_other_settings::where("key","lockdown_enable")->first()->value : 0; 
            if($type == 1)
            {
                if($enabled == 1)
                {	
                    $slot_id 					    = Tbl_slot::where("slot_id",$slot_id)->first();
                    $slot_id->maintained_until_date = $slot_id->maintained_until_date == null ? Carbon::now() : $slot_id->maintained_until_date;
                    $date_today                     = Carbon::now();
                    if($slot_id->maintained_until_date >= $date_today)
                    {
                        $return = false;
                    }
                    else 
                    {
                        $return = true;    
                    }
                }
                else 
                {
                    $return = false;    
                }
            }
        }
        else 
        {
            $return = false;
        }
        return $return;
    }
    public static function share_link_v2()
    {
       return;
    }
    public static function reverse_pass_up($slot_info)
    {
        $_pass_up_settings   = Tbl_reverse_pass_up_settings::where("membership_id",$slot_info->slot_membership)->first();
        $full_slot_info      = Tbl_slot::where("slot_id",$slot_info->slot_id)->leftjoin('tbl_membership','tbl_membership.membership_id','slot_membership')->first();


        /*checking the direction of pass up and direct in settings*/
        if($_pass_up_settings["direct_direction"] == 0 && $_pass_up_settings["pass_up_direction"] == 1)
        {
            $slot_sponsor   = Tbl_slot::where("slot_id",$slot_info->slot_id)->first();
            $slot_tree      = Tbl_tree_sponsor::where("sponsor_child_id",$slot_info->slot_id)->orderby("sponsor_level", "ASC")->get();

            $pass = 0; 
            $pos = 0;
            $self = 0;
            foreach ($slot_tree as $key => $tree) 
            {
                $full_sponsor_info       = Tbl_slot::where("slot_id",$tree->sponsor_parent_id)->leftjoin('tbl_membership','tbl_membership.membership_id','slot_membership')->first();
                $count_position          = Tbl_tree_sponsor::where("sponsor_parent_id",$slot_sponsor->slot_sponsor)->where("sponsor_level",1)->orderby("tree_sponsor_id", "ASC")->get();
                
                $pass_up_earnings        = Tbl_reverse_pass_up_combination_income::where('membership_id',$full_sponsor_info->slot_membership)->where('membership_entry_id',$full_slot_info->slot_membership)->pluck('pass_up_income')->first();
                $pass_up_direct_earnings = Tbl_reverse_pass_up_direct_combination_income::where('membership_id',$full_sponsor_info->slot_membership)->where('membership_entry_id',$full_slot_info->slot_membership)->pluck('pass_up_direct_income')->first();
                
                foreach ($count_position as $key => $count) 
                {
                    if ($count->sponsor_child_id != $slot_sponsor->slot_id)
                    {
                        $pos = $pos + 1;
                    }
                    else
                    {
                        $self = $pos + 1;
                    }                    
                }
                if($self >  $_pass_up_settings["pass_up"] && $self < $_pass_up_settings["direct"])
                {
                    $pos  = 0;
                    $self = 0;
                    goto end;
                }
                if ($self >= $_pass_up_settings["direct"]) 
                {
                    if($pass == 1)
                    {
                        if($pass_up_earnings != 0 )
                        {
                            $details = "";

                            // if($full_slot_info->hierarchy >= $full_sponsor_info->hierarchy)
                            // {
                            //     $_pass_up_settings   = Tbl_reverse_pass_up_settings::where("membership_id",$full_sponsor_info->slot_membership)->first();
                            // }

                            $cd_package = Tbl_membership::where('hierarchy',1)->where('archive',0)->pluck('membership_id')->first();
                            $cd_slot_info = Tbl_slot::where('slot_id',$slot_sponsor->slot_sponsor)->first();
                            if($cd_slot_info->slot_membership == $cd_package)
                            {
                                $cd_earnings    = $pass_up_earnings * 0.2;
                                $cd_pass_up     = $pass_up_earnings - $cd_earnings;

                                Log::insert_wallet($slot_sponsor->slot_sponsor,$cd_pass_up,"REVERSE_PASS_UP");
                                Log::insert_wallet($slot_sponsor->slot_sponsor,$cd_earnings,"REVERSE_PASS_UP",18);
                            }
                            else
                            {
                                Log::insert_wallet($slot_sponsor->slot_sponsor,$pass_up_earnings,"REVERSE_PASS_UP");
                            }
                            Log::insert_earnings($slot_sponsor->slot_sponsor,$pass_up_earnings,"REVERSE_PASS_UP","SLOT CREATION",$slot_info->slot_id,$details,$tree->sponsor_level);
                        }
                       
                        goto end;
                    }
                    if($pass == 0)
                    {
                        if($pass_up_direct_earnings != 0)
                        {
                            $details = "";

                            // if($full_slot_info->hierarchy >= $full_sponsor_info->hierarchy)
                            // {
                            //     $_pass_up_settings   = Tbl_reverse_pass_up_settings::where("membership_id",$full_sponsor_info->slot_membership)->first();
                            // }

                            $cd_package = Tbl_membership::where('hierarchy',1)->where('archive',0)->pluck('membership_id')->first();
                            $cd_slot_info = Tbl_slot::where('slot_id',$slot_sponsor->slot_sponsor)->first();
                            if($cd_slot_info->slot_membership == $cd_package)
                            {
                                $cd_earnings    = $pass_up_direct_earnings * 0.2;
                                $cd_pass_up_direct = $pass_up_direct_earnings - $cd_earnings;

                                Log::insert_wallet($slot_sponsor->slot_sponsor,$cd_pass_up_direct,"DIRECT");
                                Log::insert_wallet($slot_sponsor->slot_sponsor,$cd_earnings,"DIRECT",18);
                            }
                            else
                            {
                                Log::insert_wallet($slot_sponsor->slot_sponsor,$pass_up_direct_earnings,"DIRECT");
                            }
                            Log::insert_earnings($slot_sponsor->slot_sponsor,$pass_up_direct_earnings,"DIRECT","SLOT CREATION",$slot_info->slot_id,$details,$tree->sponsor_level);

                        }
                        goto end;
                    }
                    
                }
                if ($self <= $_pass_up_settings["pass_up"]) 
                {
                    $pass = 1;
                    $slot_sponsor   = Tbl_slot::where("slot_id",$slot_sponsor->slot_sponsor)->first();
                    $pos  = 0;
                    $self = 0;
                    goto pass_up_down;
                }
                
                pass_up_down:       
            }
        }
        elseif($_pass_up_settings["direct_direction"] == 1 && $_pass_up_settings["pass_up_direction"] == 0)
        {
            $slot_tree      = Tbl_tree_sponsor::where("sponsor_child_id",$slot_info->slot_id)->orderby("sponsor_level", "ASC")->get();
            $slot_sponsor   = Tbl_slot::where("slot_id",$slot_info->slot_id)->first();
            $pass = 0; 
            foreach ($slot_tree as $key => $tree) 
            {
                $full_sponsor_info       = Tbl_slot::where("slot_id",$tree->sponsor_parent_id)->leftjoin('tbl_membership','tbl_membership.membership_id','slot_membership')->first();
                $count_position          = Tbl_tree_sponsor::where("sponsor_parent_id",$slot_sponsor->slot_sponsor)->where("sponsor_level",1)->orderby("tree_sponsor_id", "ASC")->get();
                
                $pass_up_earnings        = Tbl_reverse_pass_up_combination_income::where('membership_id',$full_sponsor_info->slot_membership)->where('membership_entry_id',$full_slot_info->slot_membership)->pluck('pass_up_income')->first();
                $pass_up_direct_earnings = Tbl_reverse_pass_up_direct_combination_income::where('membership_id',$full_sponsor_info->slot_membership)->where('membership_entry_id',$full_slot_info->slot_membership)->pluck('pass_up_direct_income')->first();

                $pos = 0;
                foreach ($count_position as $key => $count) 
                {
                    if ($count->sponsor_child_id != $slot_sponsor->slot_id)
                    {
                        $pos++;
                    }
                    else
                    {

                        $self = $pos + 1;
                    }                    
                }
                if($self >  $_pass_up_settings["pass_up"] && $self < $_pass_up_settings["direct"])
                {
                    $pos  = 0;
                    $self = 0;
                    goto end;
                }
                if ($self <= $_pass_up_settings["direct"]) 
                {
                    if($pass == 1)
                    {
                        if($pass_up_earnings != 0 )
                        {
                            $details = "";

                            // if($full_slot_info->hierarchy >= $full_sponsor_info->hierarchy)
                            // {
                            //     $_pass_up_settings   = Tbl_reverse_pass_up_settings::where("membership_id",$full_sponsor_info->slot_membership)->first();
                            // }

                            $cd_package = Tbl_membership::where('hierarchy',1)->where('archive',0)->pluck('membership_id')->first();
                            $cd_slot_info = Tbl_slot::where('slot_id',$slot_sponsor->slot_sponsor)->first();
                            if($cd_slot_info->slot_membership == $cd_package)
                            {
                                $cd_earnings    = $pass_up_earnings * 0.2;
                                $cd_pass_up     = $pass_up_earnings - $cd_earnings;

                                Log::insert_wallet($slot_sponsor->slot_sponsor,$cd_pass_up,"REVERSE_PASS_UP");
                                Log::insert_wallet($slot_sponsor->slot_sponsor,$cd_earnings,"REVERSE_PASS_UP",18);
                            }
                            else
                            {
                                Log::insert_wallet($slot_sponsor->slot_sponsor,$pass_up_earnings,"REVERSE_PASS_UP");
                            }
                            Log::insert_earnings($slot_sponsor->slot_sponsor,$pass_up_earnings,"REVERSE_PASS_UP","SLOT CREATION",$slot_info->slot_id,$details,$tree->sponsor_level);
                        }
                        goto end;
                    }
                    if($pass == 0)
                    {
                        if($pass_up_direct_earnings != 0)
                        {
                            $details = "";

                            // if($full_slot_info->hierarchy >= $full_sponsor_info->hierarchy)
                            // {
                            //     $_pass_up_settings   = Tbl_reverse_pass_up_settings::where("membership_id",$full_sponsor_info->slot_membership)->first();
                            // }

                            $cd_package = Tbl_membership::where('hierarchy',1)->where('archive',0)->pluck('membership_id')->first();
                            $cd_slot_info = Tbl_slot::where('slot_id',$slot_sponsor->slot_sponsor)->first();
                            if($cd_slot_info->slot_membership == $cd_package)
                            {
                                $cd_earnings    = $pass_up_direct_earnings * 0.2;
                                $cd_pass_up_direct = $pass_up_direct_earnings - $cd_earnings;

                                Log::insert_wallet($slot_sponsor->slot_sponsor,$cd_pass_up_direct,"DIRECT");
                                Log::insert_wallet($slot_sponsor->slot_sponsor,$cd_earnings,"DIRECT",18);
                            }
                            else
                            {
                                Log::insert_wallet($slot_sponsor->slot_sponsor,$pass_up_direct_earnings,"DIRECT");
                            }
                            Log::insert_earnings($slot_sponsor->slot_sponsor,$pass_up_direct_earnings,"DIRECT","SLOT CREATION",$slot_info->slot_id,$details,$tree->sponsor_level);

                        }
                        goto end;
                    }
                    
                }
                if ($self >= $_pass_up_settings["pass_up"]) 
                {
                    $pass = 1;
                    $slot_sponsor   = Tbl_slot::where("slot_id",$slot_sponsor->slot_sponsor)->first();
                    $pos  = 0;
                    $self = 0;
                    goto pass_up_up;
                }
                pass_up_up:       
            }
        }
        end:
    }

    public static function welcome_bonus($slot_info) {
        
        if($slot_info)
        {
        	$welcome_bonus = Tbl_welcome_bonus_commissions::where("membership_id", $slot_info->slot_membership)->first();
        	if($welcome_bonus) {
        		$income = $welcome_bonus->commission;
        	} else {
        		$income = 0;
            }
        	if($income) {
                $details = "";
                Log::insert_wallet($slot_info->slot_id, $income, "WELCOME BONUS");
                Log::insert_earnings($slot_info->slot_id, $income, "WELCOME BONUS", "SLOT CREATION", $slot_info->slot_id, $details, 1);
            }
        }
    }

    public static function unilevel_matrix_bonus($slot_info) {
        $check_plan_settings = Tbl_mlm_plan::where('mlm_plan_code', 'UNILEVEL_MATRIX_BONUS')->first();
        if($check_plan_settings->mlm_plan_enable) {
            //FIFO comes here
		    $matrix_settings = Tbl_unilevel_matrix_bonus_settings::first();
            $slot_to_place = $slot_info;
            $default = 1;
            $test = [];
            $placement_level = 1;
            $check_right = false;
            $previous_downline = [];
            $current_position = 'LEFT';
		    $matrix_placement = Tbl_slot::where("slot_id", $slot_info->slot_id)->JoinMembership()->value('matrix_placement');
          
            if($slot_info != null && $slot_info->slot_count_id >= $matrix_settings->matrix_placement_start_at && $matrix_placement) {
                 
                if($matrix_settings->matrix_placement_start_at != 1) {
                    if($slot_info->slot_count_id > $matrix_settings->matrix_placement_start_at) {
                        $slot_owner_info = Tbl_slot::where('slot_owner', $slot_info->slot_owner)->where('slot_count_id',$matrix_settings->matrix_placement_start_at)->first();
                        
                        // dd($upline_sponsor_info);
                        if($slot_info->slot_count_id > $matrix_settings->matrix_placement_start_at) {
                            $placement_matrix = Tbl_matrix_placement::where('level',1)->where('parent_id', $slot_owner_info->slot_id)->orderBy(DB::raw("CASE WHEN position = 'LEFT' THEN 1 ELSE 2 END"))->get();
                        } else {
                            $placement_matrix = Tbl_matrix_placement::where('level',1)->where('parent_id', $slot_owner_info->slot_sponsor)->orderBy(DB::raw("CASE WHEN position = 'LEFT' THEN 1 ELSE 2 END"))->get();
                        }
                    } else {
                        
                        if($slot_info->slot_sponsor == 1) {
                            $sponsor_info = Tbl_slot::where('slot_id', $slot_info->slot_id)->first();
                        } else {
                            $sponsor_info = Tbl_slot::where('slot_id', $slot_info->slot_sponsor)->first();
                        }
                        $upline_sponsor_info = Tbl_slot::where('slot_owner', $sponsor_info->slot_owner)->where('slot_count_id',$matrix_settings->matrix_placement_start_at)->first();
					    if($upline_sponsor_info) {

                            if($upline_sponsor_info->slot_count_id >= $matrix_settings->matrix_placement_start_at) {
                                $placement_matrix = Tbl_matrix_placement::where('level',1)->where('parent_id', $upline_sponsor_info->slot_id)->orderBy(DB::raw("CASE WHEN position = 'LEFT' THEN 1 ELSE 2 END"))->get();
                            } else {
                                $placement_matrix = Tbl_matrix_placement::where('level',1)->where('parent_id', $upline_sponsor_info->slot_sponsor)->orderBy(DB::raw("CASE WHEN position = 'LEFT' THEN 1 ELSE 2 END"))->get();
                            }
                        } else {
                            $tree_sponsor = Tbl_tree_sponsor::where('sponsor_child_id', $slot_info->slot_id)->orderBy('tree_sponsor_id', 'asc')->get();
                            
                            foreach($tree_sponsor as $t_sponsor) {
                                
                                $sponsor_info = Tbl_slot::where('slot_id',$t_sponsor->sponsor_parent_id)->first();
                                $upline_sponsor_info = Tbl_slot::where('slot_owner', $sponsor_info->slot_owner)->where('slot_count_id',$matrix_settings->matrix_placement_start_at)->first();
                                if($upline_sponsor_info) {
                                    
                                    $placement_matrix = Tbl_matrix_placement::where('level',1)->where('parent_id', $upline_sponsor_info->slot_id)->orderBy(DB::raw("CASE WHEN position = 'LEFT' THEN 1 ELSE 2 END"))->get();
                                    break;
                                } else if ($t_sponsor->sponsor_parent_id == 1) {
                                    $placement_matrix = Tbl_matrix_placement::where('level',1)->where('parent_id', $upline_sponsor_info->slot_sponsor)->orderBy(DB::raw("CASE WHEN position = 'LEFT' THEN 1 ELSE 2 END"))->get();
                                }
                            }
                            // dito natapos
                        }
                        
                        // $placement_matrix = Tbl_matrix_placement::where('level',1)->where('parent_id', $slot_info->slot_sponsor)->orderBy(DB::raw("CASE WHEN position = 'LEFT' THEN 1 ELSE 2 END"))->get();
                    }
                } else {
                    // $sponsor_info = Tbl_slot::where('slot_id', $slot_info->slot_sponsor)->first();
                    // if($sponsor_info->matrix_sponsor && $sponsor_info->matrix_position) {
                    
                    $placement_matrix = Tbl_matrix_placement::where('level',1)->where('parent_id', $slot_info->slot_sponsor)->orderBy(DB::raw("CASE WHEN position = 'LEFT' THEN 1 ELSE 2 END"))->get();
                    // } else {
                    //     $tree_sponsor = Tbl_tree_sponsor::where('sponsor_child_id', $slot_info->slot_id)->orderBy('tree_sponsor_id', 'asc')->get();

                    //     foreach($tree_sponsor as $t_sponsor) {
                    //         $upline_sponsor_info = Tbl_slot::where('slot_id', $t_sponsor->sponsor_parent_id)
                    //         ->whereNotNull('matrix_position')
                    //         ->where('matrix_sponsor', '!=', 0)
                    //         ->first();
                            
                    //         if($upline_sponsor_info) {
                    //             $placement_matrix = Tbl_matrix_placement::where('level',1)->where('parent_id', $upline_sponsor_info->slot_id)->orderBy(DB::raw("CASE WHEN position = 'LEFT' THEN 1 ELSE 2 END"))->get();
                    //             break;
                    //         }
                    //     }
                    // }
                }
                
                $next_level = false;
                $placement_matrix_old = $placement_matrix;
                if(count($placement_matrix) > 1) { 
                    while(!$next_level) {
                        foreach ($placement_matrix as $value) {
                            $slot = Tbl_slot::where('slot_id', $value->child_id)->first();
                            $test[$slot->slot_no] = $placement_level . ' ' . $check_right;
                            $previous_downline[] = $value;
                            
                            $exist_left = Tbl_matrix_placement::where('level',1)->where('parent_id',$value['child_id'])->where('position', 'LEFT')->first();
                            $exist_right = Tbl_matrix_placement::where('level',1)->where('parent_id',$value['child_id'])->where('position', 'RIGHT')->first();
                            
                            if(!$exist_left || !$exist_right) {
                                $slot_info = Tbl_slot::where('slot_id',$value['child_id'])->first();
                                $default = 0;
                                $next_level = true;
                                break;
                            }
                        } 
                        
                        $previous_downline = [];
                        // $test = [];
                        foreach($placement_matrix_old as $matrix) {
                            $previous_downline[] = Tbl_matrix_placement::where(['level' => 1, 'parent_id' => $matrix->child_id, 'position' => 'LEFT'])->first();
                            $previous_downline[] = Tbl_matrix_placement::where(['level' => 1, 'parent_id' => $matrix->child_id, 'position' => 'RIGHT'])->first();
                            // $test[$matrix->child_id] = $check_right;
                        }
                        
                        $previous_downline = array_filter($previous_downline);
                        $check_right = false;
                        $placement_level++;
                        $placement_matrix = $previous_downline;
                        $placement_matrix_old = $previous_downline;
                    }
                } 
               
                // dd($slot_info->slot_no, $test, $placement_level, $check_right);
                $position = Slot::get_matrix_auto_position($slot_info, $default);
               
                if(!$position)
                {
                    // FOR DEV CHECKING
                    dd($position, 1, $slot_info, $default, $placement_level, $slot_to_place->slot_no);
                }
                $sponsor_info               = Tbl_slot::where('slot_id', $position->slot_id)->first();
                $data['slot_placement']     = $sponsor_info->slot_no;
                $data['slot_position']      = $position->position;
                $data['slot_code']          = $slot_to_place->slot_no;
                // dd($data);
                Slot::place_slot_matrix($data);
            }
        }
    }

    public static function unilevel_matrix_bonus_commission($slot) {
        $matrix_plan = Tbl_mlm_plan::where("mlm_plan_code", "UNILEVEL_MATRIX_BONUS")->first();
        $matrix_settings = Tbl_unilevel_matrix_bonus_settings::first();

        if($matrix_plan->mlm_plan_enable) {
            $slot_tree = DB::table('tbl_matrix_placement')->where("child_id",$slot->slot_id)->where("parent_id", "!=", 1)->orderBy("level", "asc")->get();
            
            $matrix_level = Tbl_unilevel_matrix_bonus_settings::first()->matrix_level;
            $gained_level = [];
            $all_levels = range(1, $matrix_level);

            foreach($slot_tree as $key => $tree) {
                /* GET SPONSOR AND GET UNILEVEL BONUS INCOME PERCENTAGE  */
                $slot_sponsor = Tbl_slot::where("slot_id",$tree->parent_id)->first();
                $commission_settings = Tbl_unilevel_matrix_bonus_levels::where("membership_id", $slot_sponsor->slot_membership)
                    ->where("membership_entry_id", $slot->slot_membership)
                    ->where("level", $tree->level)
                    ->first();

                if(isset($commission_settings) && $commission_settings->matrix_commission) {
                    $commission_amount = $commission_settings->matrix_commission;
                }
                else {
                    $commission_amount = 0;
                }
                
                /* CHECK IF BONUS IS ZERO */
                if($commission_amount != 0) {
                    $gained_level[] = $tree->level;
                    Log::insert_wallet($slot_sponsor->slot_id, $commission_amount,"UNILEVEL_MATRIX_BONUS");
                    Log::insert_earnings($slot_sponsor->slot_id, $commission_amount,"UNILEVEL_MATRIX_BONUS","SLOT CREATION",$slot->slot_id, "", $tree->level);
                }
            }
            Self::ungained_earnings_based_on_levels($all_levels, $gained_level, $slot, "matrix");
        }
    }

    public static function ungained_earnings_based_on_levels($all_levels, $gained_level, $slot, $plan, $item_id = 0, $points = 0) {

        $ungained_levels = array_diff($all_levels, $gained_level);
        $commission_ungained = 0;
        $company_account = Users::where("company_account", 1)->JoinSlot()->first();
        if($company_account) {
            foreach($ungained_levels as $level) {
                if($plan == "matrix") {
                    $commission_settings = Tbl_unilevel_matrix_bonus_levels::where("membership_id", $company_account->slot_membership)
                    ->where("membership_entry_id", $slot->slot_membership)
                    ->where("level", $level)
                    ->first();
                    $plan_label = "UNILEVEL_MATRIX_BONUS";
                    $plan_trigger = "SLOT CREATION";
                    
                    if(isset($commission_settings) && $commission_settings->matrix_commission) {
                        $commission_amount = $commission_settings->matrix_commission;
                        $commission_ungained += $commission_amount;
                    }
                    else {
                        $commission_amount = 0;
                    }
                } else if ($plan == "indirect") {
                    $commission_settings = Tbl_membership_indirect_level::where("membership_id", $company_account->slot_membership)
                        ->where("membership_entry_id", $slot->slot_membership)
                        ->where("membership_level", $level)
                        ->first();
                    $plan_label = "INDIRECT";
                    $plan_trigger = "SLOT CREATION";
                    if(isset($commission_settings) && $commission_settings->membership_indirect_income) {
                        $commission_amount = $commission_settings->membership_indirect_income;
                        $commission_ungained += $commission_amount;
                    } else {
                        $commission_amount = 0;
                    }
                } else if ($plan == "unilevel") {
                    $commission_settings = Tbl_membership_unilevel_level::where("membership_id", $company_account->slot_membership)
                        ->where("membership_entry_id", $slot->slot_membership)
                        ->where("membership_level", $level)
                        ->first();
                    if(isset($commission_settings) && $commission_settings->membership_percentage) {
                        $commission_amount = ($commission_settings->membership_percentage/100) * $points;
                        $commission_ungained += $commission_amount;
                    } else {
                        $commission_amount = 0;
                    }
                }

            }

            if($plan != 'unilevel' && $commission_ungained != 0) {
                Log::insert_wallet($company_account->slot_id, $commission_ungained, $plan_label);
                Log::insert_earnings($company_account->slot_id, $commission_ungained, $plan_label, $plan_trigger, $slot->slot_id, "", 0);
            } else if ($plan == 'unilevel') {
                Log::insert_points($company_account->slot_id, $commission_ungained,"UNILEVEL_GPV", $slot->slot_id, 0);
                Log::insert_unilevel_points($company_account->slot_id, $commission_ungained, "UNILEVEL_GPV", $slot->slot_id, 0, $item_id);
            }
        }
    }

    public static function prime_refund($slot_info)
    {
        $slot_sponsor = Tbl_slot::JoinMembership()
            ->where("slot_id", $slot_info->slot_sponsor)
            ->first();
    
        if (!$slot_sponsor) {
            return;
        }
    
        // Check if the user has already claimed the refund
        $already_claimed = Tbl_prime_refund_points_log::where([
            ["slot_id", $slot_sponsor->slot_id],
            ["status", 1] // Status 1 means already claimed
        ])->exists();
    
        if ($already_claimed) {
            return; // Exit function if refund is already claimed
        }
    
        $prime_refund = Tbl_prime_refund_setup::where([
            ["membership_id", $slot_sponsor->slot_membership],
            ["membership_entry_id", $slot_info->slot_membership]
        ])->first();
    
        if (!$prime_refund || !$prime_refund->prime_refund_points) {
            return;
        }
    
        $prime_refund_points = Tbl_prime_refund_points_log::where([
            ["slot_id", $slot_sponsor->slot_id],
            ["status", 0]
        ])->sum("points");
    
        $flushout = 0;
        if (
            $prime_refund_points && 
            $slot_sponsor->prime_refund_enable == 1 && 
            $slot_sponsor->prime_refund_accumulated_points
        ) {
            $total_points = $prime_refund_points + $prime_refund->prime_refund_points;
            if ($total_points >= $slot_sponsor->prime_refund_accumulated_points) {
                $flushout = max(0, $total_points - $slot_sponsor->prime_refund_accumulated_points);
                $prime_refund->prime_refund_points -= $flushout;
            }
        }
    
        Tbl_prime_refund_points_log::insert([
            "slot_id" => $slot_sponsor->slot_id,
            "membership_id" => $slot_sponsor->slot_membership,
            "cause_slot_id" => $slot_info->slot_id,
            "cause_membership_id" => $slot_info->slot_membership,
            "points" => $prime_refund->prime_refund_points,
            "flushout_points" => $flushout,
            "commission" => $flushout ? $slot_sponsor->prime_refund_accumulated_points : 0,
            "date_created" => Carbon::now(),
            "status" => 0
        ]);
    
        if ($flushout) {
            Tbl_prime_refund_points_log::where("slot_id", $slot_sponsor->slot_id)
                ->where("status", 0)
                ->update(["status" => 1]);
    
            Log::insert_wallet($slot_sponsor->slot_id, $slot_sponsor->prime_refund_accumulated_points, "PRIME_REFUND");
            Log::insert_earnings($slot_sponsor->slot_id, $slot_sponsor->prime_refund_accumulated_points, "PRIME_REFUND", "SLOT CREATION", $slot_info->slot_id, "", 1);
        }
    }

    public static function milestone_bonus($slot_info , $binary_repurchase_pts = 0)
	{
        $milestone_settings = Tbl_milestone_bonus_settings::first();
        $trigger = $binary_repurchase_pts ? 'Slot Repurchase' : 'Slot Placement';
        
        $tree_placement = Tbl_tree_placement::where("placement_child_id", $slot_info->slot_id)
            ->orderBy("placement_level","ASC")
            ->get();
		foreach($tree_placement as $tree) {
            $slot_placement = Tbl_slot::JoinMembership()->where("slot_id", $tree->placement_parent_id)->first();
            $points_settings = Tbl_milestone_points_setup::where("membership_id",$slot_placement->slot_membership)->where("membership_entry_id",$slot_info->slot_membership)->first();
            // $maximum_limit = Tbl_membership::where('membership_id', $slot_placement->slot_membership)->first()->milestone_maximum_limit;
            // $tree_details = Tbl_tree_placement::where('placement_parent_id', $slot_placement->slot_id)->where('placement_child_id', $slot_info->slot_id)->first();

            if($binary_repurchase_pts != 0) {
                $points = $binary_repurchase_pts;
            } else if($points_settings) {
                $points = $points_settings->milestone_points;
            } else {
                $points = 0;
            }
                    
            $receive["left"] = 0;
            $receive["right"] = 0;
            $old["left"] = $slot_placement->slot_milestone_left_points;
            $old["right"] = $slot_placement->slot_milestone_right_points;
            $new["left"] = $slot_placement->slot_milestone_left_points;
            $new["right"] = $slot_placement->slot_milestone_right_points;
            $flushout_points["right"] = 0;
            $flushout_points["left"] = 0;
            $log_earnings = 0;
            $log_flushout = 0;
            $proceed_flushout = 0;
            
            if($points != 0) {
                $position = strtolower($tree->placement_position);
                if($position == "left" || $position == "right") {
                    $receive[$position] = $points;
                    $new[$position] = $new[$position] + $points;
                    $temp_log_earnings = 0;
                    $update = null;
                    $update_string = "slot_milestone_".$position."_points";
                    $update[$update_string] = Tbl_slot::where("slot_id",$slot_placement->slot_id)->first()->$update_string + $points;

                    Tbl_slot::where("slot_id",$slot_placement->slot_id)->update($update);
                
                    $plan_type = "MILESTONE_" . strtoupper($tree->placement_position) . "_POINTS";
                    Log::insert_points($slot_placement->slot_id, $points, $plan_type, $slot_info->slot_id, $tree->placement_level);
                
                    $milestone_points["left"]  = Tbl_slot::where("slot_id",$slot_placement->slot_id)->first()->slot_milestone_left_points;
                    $milestone_points["right"] = Tbl_slot::where("slot_id",$slot_placement->slot_id)->first()->slot_milestone_right_points;
      
                    $pairing_settings = Tbl_milestone_pairing_points_setup::where("archive",0)
                        ->orderBy("milestone_pairing_right","DESC")
                        ->orderBy("milestone_pairing_left","DESC")
                        ->where("milestone_pairing_bonus","!=",0)
                        ->where("milestone_pairing_left","!=",0)
                        ->where("milestone_pairing_right","!=",0)
                        ->where(function ($query) use ($slot_placement)
                        {
                            $query->where('membership_id', $slot_placement->slot_membership)
                                ->orWhereNull('membership_id', '=', null);
                        })
                        ->get();
                    foreach($pairing_settings as $pairing)
                    {
                        // dd($milestone_points["left"] >= $pairing->milestone_pairing_left && $milestone_points["right"] >= $pairing->milestone_pairing_right);
                        while($milestone_points["left"] >= $pairing->milestone_pairing_left && $milestone_points["right"] >= $pairing->milestone_pairing_right)
                        {
                            /* PAIR THE POINTS */
                            $milestone_points["left"]  = $milestone_points["left"] - $pairing->milestone_pairing_left;
                            $milestone_points["right"] = $milestone_points["right"] - $pairing->milestone_pairing_right;

                            /* FOR LOGS BINARY PTS RECORD */
                            $new["left"]     = $new["left"] - $pairing->milestone_pairing_left; 
                            $new["right"]    = $new["right"] - $pairing->milestone_pairing_right;
                            $log_earnings    = $log_earnings + $pairing->milestone_pairing_bonus;
                            $milestone_income   = $pairing->milestone_pairing_bonus;

                            /* ANOTHER RECORD FOR POINTS LOG */
                            $plan_type = "MILESTONE_LEFT_POINTS";
                            Log::insert_points($slot_placement->slot_id,(-1 * $pairing->milestone_pairing_left),$plan_type,$slot_info->slot_id, $tree->placement_level);
                        
                            $plan_type = "MILESTONE_RIGHT_POINTS";
                            Log::insert_points($slot_placement->slot_id,(-1 * $pairing->milestone_pairing_right),$plan_type,$slot_info->slot_id, $tree->placement_level);
                        

                            /* UPDATE POINTS AND WALLET*/
                            $update_slot["slot_milestone_left_points"]	= $milestone_points["left"];
                            $update_slot["slot_milestone_right_points"]	= $milestone_points["right"];
                            Tbl_slot::where("slot_id",$slot_placement->slot_id)->update($update_slot);

                            $currency_id = 0;


                            /* CONDITIONAL PAIRING BINARY VALIDATION */
                            if($binary_repurchase_pts != 0) {
                                $slot_date_pairing = Carbon::now();
                            } else {
                                $slot_date_pairing = Carbon::parse($slot_info->slot_date_placed);
                            }

                            $logs = Tbl_earning_log::where('earning_log_slot_id', $slot_placement->slot_id)
                                ->where('earning_log_plan_type', '=','MILESTONE BONUS');

                            if($milestone_settings->milestone_limit) {
                                $now = Carbon::now();
                                $compareDate = Carbon::parse($slot_date_pairing);
                                $cycle = strtolower($milestone_settings->milestone_cycle_limit);
                                $has_paired_today = 0;
                                $total_earnings_per_cycle = 0;
                                $total_pairs_per_cycle = 0;

                                switch ($cycle) {
                                    case 'daily':
                                        $compare = $compareDate->toDateString();
                                        $has_paired_today = $slot_placement->slot_milestone_pairs_date === $compare ? 1 : 0;

                                        $logs = $logs->whereDate('earning_log_date_created', $now->toDateString());
                                        break;

                                    case 'halfday':
                                        $compare = $compareDate->toDateString();
                                        $compareMeridiem = $compareDate->format('A');
                                        $has_paired_today = ($slot_placement->slot_milestone_pairs_date === $compare && $slot_placement->meridiem === $compareMeridiem) ? 1 : 0;

                                        $isAM = $now->format('A') === 'AM';
                                        $start = $isAM ? $now->copy()->startOfDay() : $now->copy()->setTime(12, 0, 0);
                                        $end = $isAM ? $now->copy()->setTime(11, 59, 59) : $now->copy()->endOfDay();

                                        $logs = $logs->whereBetween('earning_log_date_created', [$start, $end]);
                                        break;

                                    case 'weekly':
                                        // Check if slot pairing date is within this week
                                        $startOfWeek = $now->copy()->startOfWeek();
                                        $endOfWeek = $now->copy()->endOfWeek();

                                        $has_paired_today = $slot_placement->slot_milestone_pairs_date &&
                                            Carbon::parse($slot_placement->slot_milestone_pairs_date)->between($startOfWeek, $endOfWeek) ? 1 : 0;

                                        $logs = $logs->whereBetween('earning_log_date_created', [$startOfWeek, $endOfWeek]);
                                        break;

                                    default:
                                        $compare = $compareDate->toDateString();
                                        $has_paired_today = $slot_placement->slot_milestone_pairs_date === $compare ? 1 : 0;
                                        // No date filter for unlimited cycle
                                        break;
                                }

                                $total_earnings_per_cycle = $logs->sum('earning_log_amount');
                                $total_pairs_per_cycle = $logs->count();

                                /* PAIRINGS PER DAY FLUSHOUT CHECKING */
                                $membership = Tbl_membership::where("membership_id",$slot_placement->slot_membership)->first();
                                if($membership) {
                                    $enableLimit = $milestone_settings->milestone_limit;
                                    $limit_type = $milestone_settings->milestone_type_limit;
                                    $maximum_limit = $membership->milestone_maximum_limit;
                                    // dd($limit_type,  $maximum_limit,  $total_pairs_per_cycle, $has_paired_today, $milestone_income, $enableLimit,$slot_placement->slot_milestone_pairs_date, $compare_date );
                                    if($has_paired_today == 1 && $maximum_limit && $enableLimit) {
                                       if ($limit_type == "pairs" && $total_pairs_per_cycle >= floor($maximum_limit)) {
                                            $proceed_flushout = 1;
                                            $log_flushout += $milestone_income;
                                            // $log_earnings -= $membership->auto_upgrade ? ($milestone_income - $deduction) : $milestone_income;
                                            $log_earnings -= $milestone_income;
                                            $milestone_income = 0;
                                        } else if($limit_type == "earnings") {
                                            $total = $total_earnings_per_cycle + $milestone_income;
                                            if(round($total, 2) > round( $maximum_limit, 2)) {
                                                if(round($total_earnings_per_cycle, 2) >= round( $maximum_limit, 2)) {
                                                    $log_flushout += $milestone_income;
                                                    $log_earnings = 0;
                                                    $milestone_income = 0;
                                                } else {
                                                    // $milestone_income = (round($total, 2) - round( $maximum_limit, 2)) ;
                                                    // dd(round($total, 2), round( $maximum_limit, 2), $milestone_income);
                                                    $diff = (round($total, 2) - round( $maximum_limit, 2));
                                                    $milestone_income -= $diff;
                                                    $log_flushout += $diff;
                                                    $log_earnings -= $log_flushout;
                                                }
                                                $proceed_flushout = 1;
                                            }
                                        } else {
                                            $update_slot["slot_milestone_pairs"] = $slot_placement->slot_milestone_pairs + 1;
                                            $update_slot["milestone_meridiem"] = Carbon::parse($slot_date_pairing)->format("A");
                                            Tbl_slot::where("slot_id", $slot_placement->slot_id)->update($update_slot);
                                        }
                                    }  else {
                                        $update_slot["slot_milestone_pairs_date"] = $slot_date_pairing;
                                        $update_slot["milestone_meridiem"] = Carbon::parse($slot_date_pairing)->format("A");
                                        $update_slot["slot_milestone_pairs"] = 1;
                                        Tbl_slot::where("slot_id",$slot_placement->slot_id)->update($update_slot);
                                    } 
                                }
                            }
                            
                            $details = "";

                            Log::insert_wallet($slot_placement->slot_id,$milestone_income,"MILESTONE_BONUS",$currency_id);
                            Log::insert_earnings($slot_placement->slot_id,$milestone_income,"MILESTONE_BONUS",strtoupper($trigger),$slot_info->slot_id,$details,$tree->placement_level,$currency_id);
                            Special_plan::infinity_bonus($slot_placement, "MILESTONE_BONUS", $milestone_income);
                            
                            /* GET THE LAST EARNINGS BEFORE FLUSHOUT */  
                            if($log_earnings) {
                                $temp_log_earnings = $log_earnings;
                            } else {
                                $log_earnings = $temp_log_earnings;
                            }
                            
                            /* REFRESH GET DATA ON POINTS */  
                            $milestone_points["left"]  = Tbl_slot::where("slot_id",$slot_placement->slot_id)->first()->slot_milestone_left_points;
                            $milestone_points["right"] = Tbl_slot::where("slot_id",$slot_placement->slot_id)->first()->slot_milestone_right_points;
                            $slot_placement  = Tbl_slot::JoinMembership()->where("slot_id",$slot_placement->slot_id)->first();
                        }
                    }
                    if($milestone_settings->milestone_strong_leg_retention == 0) {                   
                        if($proceed_flushout == 1) {
                            if($new["left"] != 0) {
                                $plan_type = "MILESTONE_LEFT_FLUSHOUT";
                                Log::insert_points($slot_placement->slot_id,(-1 * $new["left"]),$plan_type,$slot_info->slot_id, 0);                                 
                                $flushout_points["left"] = $new["left"];
                                $new["left"] = $new["left"] - $new["left"];  
                            }
                            
                            if($new["right"] != 0) {
                                $plan_type = "MILESTONE_RIGHT_FLUSHOUT";
                                Log::insert_points($slot_placement->slot_id,(-1 * $new["right"]),$plan_type,$slot_info->slot_id, 0);                                 
                                $flushout_points["right"] = $new["right"];
                                $new["right"] = $new["right"] - $new["right"];
                            }

                            $update_slot_flush["slot_milestone_left_points"] = $new["left"];
                            $update_slot_flush["slot_milestone_right_points"] = $new["right"];
                            Tbl_slot::where("slot_id",$slot_placement->slot_id)->update($update_slot_flush);
                        }
                    }

                    // For a single detail among multiple pairings
                    // $details = "";
                    // Log::insert_wallet($slot_placement->slot_id,$log_earnings,"BINARY");
                    // Log::insert_earnings($slot_placement->slot_id,$log_earnings,"BINARY","SLOT PLACEMENT",$slot_info->slot_id,$details,$tree->placement_level);
           
                    Log::insert_milestone_points(
                        $slot_placement->slot_id,
                        $receive,
                        $old,
                        $new,
                        $slot_info->slot_id,
                        $log_earnings,
                        $log_flushout,
                        $tree->placement_level,
                        $trigger,
                        $flushout_points,
                        $binary_repurchase_pts);
                }
            }
		}
	}

	public static function marketing_support($slot_info) {
        $slot_sponsor = Tbl_slot::JoinMembership()->where('slot_id', $slot_info->slot_sponsor)->first();
        $settings = Tbl_marketing_support_settings::first();
        if($slot_sponsor->marketing_support_enable) {
            if($slot_sponsor->marketing_support_activate) {
                $setup = Tbl_marketing_support_setup::where("membership_id",$slot_sponsor->slot_membership)
                    ->where("membership_entry_id",$slot_info->slot_membership)
                    ->first();
                if($setup && $setup->income) {
                    $hasPendingLogs = Tbl_marketing_support_log::where([
                            ['log_slot_id', '=', $slot_sponsor->slot_id],
                            ['log_claimed', '=', 0],
                            ['log_status', '=', 0],
                        ])->exists();
                    
                    if($settings->number_of_days_to_earn && !$hasPendingLogs) {
                        $date = Carbon::now();
                        $income_count = $slot_sponsor->marketing_support_count_income + 1;
                        for ($i = 0; $i < $settings->number_of_days_to_earn; $i++) {
                            Tbl_marketing_support_log::insert([
                                'log_slot_id' => $slot_sponsor->slot_id,
                                'log_membership_id' => $slot_sponsor->slot_membership,
                                'log_cause_slot_id' => $slot_info->slot_id,
                                'log_cause_membership_id' => $slot_info->slot_membership,
                                'log_income_count' => $income_count,
                                'log_income' => $setup->income,
                                'log_date_created' => $date
                            ]);
                            $date = $date->copy()->addDay();
                        }
                        Member::update_daily_marketing_support_income($slot_sponsor->slot_id);
                    }
                }
            } else {
                $slot_count = Member::get_count_direct($slot_sponsor);
                if($slot_sponsor->marketing_support_date_end) {
                    if($slot_count['recurring_direct'] >= $slot_sponsor->marketing_support_required_directs_for_recurring && $slot_sponsor->marketing_support_count_income < $settings->number_of_income) {
                        Tbl_slot::where('slot_id', $slot_sponsor->slot_id)
                            ->update([
                                'marketing_support_activate' => 1,
                                'marketing_support_date_start' => Carbon::now(),
                            ]);
                        Mlm_complan_manager::marketing_support($slot_info);
                    } 
                } else {
                    if($slot_count['left_direct'] >= $slot_sponsor->marketing_support_left_required_directs_to_activate && $slot_count['right_direct'] >= $slot_sponsor->marketing_support_right_required_directs_to_activate) {
                        Tbl_slot::where('slot_id', $slot_sponsor->slot_id)
                            ->update([
                                'marketing_support_activate' => 1,
                                'marketing_support_date_start' => Carbon::now(),
                            ]);
                        Mlm_complan_manager::marketing_support($slot_info);
                    } 
                }
            }
        }
    }

    public static function leaders_support($slot_info) {
        $slot_sponsor = Tbl_slot::JoinMembership()->where('slot_id', $slot_info->slot_sponsor)->first();
        $settings = Tbl_leaders_support_settings::first();
        if($slot_sponsor->leaders_support_enable) {
            $setup = Tbl_leaders_support_setup::where("membership_id",$slot_sponsor->slot_membership)
                ->where("membership_entry_id",$slot_info->slot_membership)
                ->first();
            if ($setup && $setup->income) {
                if ($settings->number_of_days_to_earn && $settings->number_of_income) {
                    
                    $dateStart = Carbon::now();
                    $dateEnd = $dateStart->copy()->addDays($settings->number_of_days_to_earn);

                    if (!$slot_sponsor->leaders_support_date_end) {
                        $leadersSupportDateEnd = $dateStart->copy()->addDays(
                            $settings->number_of_days_to_earn * $settings->number_of_income
                        );

                        Tbl_slot::where('slot_id', $slot_sponsor->slot_id)
                            ->update(['leaders_support_date_end' => $leadersSupportDateEnd]);
                    }
                    $slotEndDate = Tbl_slot::where('slot_id', $slot_sponsor->slot_id)->first()->leaders_support_date_end;

                    // Proceed only if support period is still valid
                    if ($slotEndDate >= $dateStart) {

                        Tbl_leaders_support_log::insert([
                            'log_slot_id' => $slot_sponsor->slot_id,
                            'log_membership_id' => $slot_sponsor->slot_membership,
                            'log_cause_slot_id' => $slot_info->slot_id,
                            'log_cause_membership_id' => $slot_info->slot_membership,
                            'log_income' => $setup->income,
                            'log_date_start' => $dateStart,
                            'log_date_end' => $dateEnd
                        ]);
                    }
                }
            }
        }
    }
}
