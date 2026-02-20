<?php
namespace App\Globals;

use DB;
use App\Models\Tbl_slot;
use App\Models\Tbl_tree_sponsor;
use App\Models\Tbl_stairstep_points;
use App\Models\Tbl_stairstep_rank;
use App\Models\Tbl_stairstep_settings;
use Carbon\Carbon;

use App\Globals\Wallet;

class Stairstep
{
	public static function update_rank($slot_id,$start_date = null ,$end_date = null ,$is_slot_creation = false)
	{
        $slot_info          = Tbl_slot::where("slot_id",$slot_id)->first();
        $current_rank_level = Tbl_stairstep_rank::where("stairstep_rank_id",$slot_info->slot_stairstep_rank)->first() ? Tbl_stairstep_rank::where("stairstep_rank_id",$slot_info->slot_stairstep_rank)->first()->stairstep_rank_level : 0;
        $get_rank           = Tbl_stairstep_rank::where("archive",0)->where("stairstep_rank_level",">",$current_rank_level)->orderBy("stairstep_rank_level","ASC")->get();
        
        foreach($get_rank as $srank)
        {
            $rank_personal           = $srank->stairstep_rank_personal;
            $rank_group              = $srank->stairstep_rank_group;
            $rank_personal_all       = $srank->stairstep_rank_personal_all;
            $rank_group_all          = $srank->stairstep_rank_group_all;
            $rank_upgrade_count      = $srank->stairstep_rank_upgrade;
            $rank__name_id           = $srank->stairstep_rank_name_id;
            $rank_direct_referral    = $srank->stairstep_direct_referral;

            $all_personal      = Tbl_stairstep_points::where("stairstep_points_slot_id",$slot_info->slot_id)->where("stairstep_points_type","STAIRSTEP_PPV")->sum("stairstep_points_amount");
            $all_group         = Tbl_stairstep_points::where("stairstep_points_slot_id",$slot_info->slot_id)->where("stairstep_points_type","STAIRSTEP_GPV")->sum("stairstep_points_amount");

            $start_date        = Carbon::now()->startOfMonth();
            $end_date          = Carbon::now()->endOfMonth();

            $monthly_personal        = Tbl_stairstep_points::where("stairstep_points_slot_id",$slot_info->slot_id)->where("stairstep_points_date_created",">=",$start_date)->where("stairstep_points_date_created","<=",$end_date)->where("stairstep_points_type","STAIRSTEP_PPV")->sum("stairstep_points_amount");
            $monthly_group           = Tbl_stairstep_points::where("stairstep_points_slot_id",$slot_info->slot_id)->where("stairstep_points_date_created",">=",$start_date)->where("stairstep_points_date_created","<=",$end_date)->where("stairstep_points_type","STAIRSTEP_GPV")->sum("stairstep_points_amount");

            $direct                  = Tbl_tree_sponsor::where("sponsor_parent_id",$slot_info->slot_id)->where("sponsor_level",1)->get();
            $referral                = Tbl_slot::where("slot_sponsor",$slot_info->slot_id)->where("membership_inactive",0)->first() ? Tbl_slot::where("slot_sponsor",$slot_info->slot_id)->where("membership_inactive",0)->count() : 0;
            
            $rank_ctr                = 0;
            
            foreach ($direct as $key => $leg) 
            {
                // dd($rank__name_id);
                $ctr = 0;
                $slot_direct   = Tbl_slot::where("slot_id",$leg->sponsor_child_id)->first();
                if($slot_direct->slot_stairstep_rank == $rank__name_id)
                {
                    
                    $ctr++;
                    $rank_ctr = $rank_ctr + $ctr;
                }
                if($ctr == 0)
                {
                    $downline   = Tbl_tree_sponsor::where("sponsor_parent_id",$leg->sponsor_child_id)->Child()->where("slot_stairstep_rank",$rank__name_id)->count();
                    if($downline != 0 || $downline != null)
                    {
                        $rank_ctr = $rank_ctr + 1;
                    }
        
                }
                if($rank_ctr == $rank_upgrade_count)
                {
                    break;
                }
            }
            // dd($slot_info,($monthly_personal >= $rank_personal ),($monthly_group >= $rank_group ),($all_personal >= $rank_personal_all ),($all_group >= $rank_group_all ),($rank_ctr == $rank_upgrade_count));
            if($monthly_personal >= $rank_personal && $monthly_group >= $rank_group && $all_personal >= $rank_personal_all && $all_group >= $rank_group_all && $rank_ctr == $rank_upgrade_count && $referral >= $rank_direct_referral)
            {
                
                if($is_slot_creation == true)
                {
                    $update_rank["slot_stairstep_rank"] = $srank->stairstep_rank_id;
                    Tbl_slot::where("slot_id",$slot_id)->update($update_rank);
                    self::upline($slot_id);
                }
                else 
                {
                    $update_rank["slot_stairstep_rank"] = $srank->stairstep_rank_id;
                    Tbl_slot::where("slot_id",$slot_id)->update($update_rank);
                }
                
            }
        }
    }
    public static function upline($slot_id)
    {
        $upline             = Tbl_tree_sponsor::where("sponsor_child_id",$slot_id)->orderby("sponsor_level", "ASC")->get();

        foreach ($upline as $key => $tree) 
        {
            $slot_sponsor           = Tbl_slot::where("slot_id",$tree->sponsor_parent_id)->first();
            $current_rank_level2    = Tbl_stairstep_rank::where("stairstep_rank_id",$slot_sponsor->slot_stairstep_rank)->first() ? Tbl_stairstep_rank::where("stairstep_rank_id",$slot_sponsor->slot_stairstep_rank)->first()->stairstep_rank_level : 0;
            $get_rank2              = Tbl_stairstep_rank::where("archive",0)->where("stairstep_rank_level",">",$current_rank_level2)->orderBy("stairstep_rank_level","ASC")->get();
            $direct                 = Tbl_tree_sponsor::where("sponsor_parent_id",$slot_sponsor->slot_id)->where("sponsor_level",1)->get();
            $referral                = Tbl_slot::where("slot_sponsor",$slot_sponsor->slot_id)->where("membership_inactive",0)->first() ? Tbl_slot::where("slot_sponsor",$slot_sponsor->slot_id)->where("membership_inactive",0)->count() : 0;

            foreach($get_rank2 as $srank)
            {
                $rank_ctr                = 0;
                $rank_personal           = $srank->stairstep_rank_personal;
                $rank_group              = $srank->stairstep_rank_group;
                $rank_personal_all       = $srank->stairstep_rank_personal_all;
                $rank_group_all          = $srank->stairstep_rank_group_all;
                $rank_upgrade_count      = $srank->stairstep_rank_upgrade;
                $rank__name_id           = $srank->stairstep_rank_name_id;
                $rank_direct_referral    = $srank->stairstep_direct_referral;
                
                $all_personal      = Tbl_stairstep_points::where("stairstep_points_slot_id",$slot_sponsor->slot_id)->where("stairstep_points_type","STAIRSTEP_PPV")->sum("stairstep_points_amount");
                $all_group         = Tbl_stairstep_points::where("stairstep_points_slot_id",$slot_sponsor->slot_id)->where("stairstep_points_type","STAIRSTEP_GPV")->sum("stairstep_points_amount");
    
                $start_date        = Carbon::now()->startOfMonth();
                $end_date          = Carbon::now()->endOfMonth();
    
                $monthly_personal        = Tbl_stairstep_points::where("stairstep_points_slot_id",$slot_sponsor->slot_id)->where("stairstep_points_date_created",">=",$start_date)->where("stairstep_points_date_created","<=",$end_date)->where("stairstep_points_type","STAIRSTEP_PPV")->sum("stairstep_points_amount");
                $monthly_group           = Tbl_stairstep_points::where("stairstep_points_slot_id",$slot_sponsor->slot_id)->where("stairstep_points_date_created",">=",$start_date)->where("stairstep_points_date_created","<=",$end_date)->where("stairstep_points_type","STAIRSTEP_GPV")->sum("stairstep_points_amount");

                foreach ($direct as $key => $leg) 
                {
                    // dd($direct,$rank__name_id);
                    $ctr = 0;
                    $slot_direct   = Tbl_slot::where("slot_id",$leg->sponsor_child_id)->first();
                    if($slot_direct->slot_stairstep_rank == $rank__name_id)
                    {
                        
                        $ctr++;
                        $rank_ctr = $rank_ctr + $ctr;
                    }
                    if($ctr == 0)
                    {
                        $downline   = Tbl_tree_sponsor::where("sponsor_parent_id",$leg->sponsor_child_id)->Child()->where("slot_stairstep_rank",$rank__name_id)->count();
                        if($downline != 0 || $downline != null)
                        {
                            $rank_ctr = $rank_ctr + 1;
                        }
                    }
                    if($rank_ctr == $rank_upgrade_count)
                    {
                        break;
                    }
                }
                if($monthly_personal >= $rank_personal && $monthly_group >= $rank_group && $all_personal >= $rank_personal_all && $all_group >= $rank_group_all && $rank_ctr == $rank_upgrade_count && $referral >= $rank_direct_referral)
                {
                    $update_rank["slot_stairstep_rank"] = $srank->stairstep_rank_id;
                    Tbl_slot::where("slot_id",$slot_id)->update($update_rank);
                }
            }
        }
    }
}
