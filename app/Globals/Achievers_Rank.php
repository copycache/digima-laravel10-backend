<?php
namespace App\Globals;

use App\Models\Tbl_achievers_rank;
use App\Models\Tbl_achievers_rank_list;
use App\Models\Tbl_binary_points;
use App\Models\Tbl_slot;
use Carbon\Carbon;

class Achievers_Rank
{
    // Created By: Centy - 10-27-2023
	public static function update_rank($slot_id)
	{
        $slot_info = Tbl_slot::where("slot_id",$slot_id)->first();
        $current_rank_level = Tbl_achievers_rank::where("achievers_rank_id",$slot_info->slot_achievers_rank)->first() ? Tbl_achievers_rank::where("achievers_rank_id",$slot_info->slot_achievers_rank)->first()->achievers_rank_level : 0;
        $get_rank = Tbl_achievers_rank::where("archive",0)->where("achievers_rank_level",">",$current_rank_level)->orderBy("achievers_rank_level","ASC")->get();
        
        $left = Tbl_slot::where('slot_placement', $slot_id)
            ->where('slot_position', 'LEFT')
            ->pluck('tbl_slot.slot_id')
            ->first();

        $right = Tbl_slot::where('slot_placement', $slot_id)
            ->where('slot_position', 'RIGHT')
            ->pluck('tbl_slot.slot_id')
            ->first();

        foreach($get_rank as $srank)
        {
            $achievers_rank_id = $srank->achievers_rank_id;
            $achievers_rank_left_points = $srank->achievers_rank_binary_points_left;
            $achievers_rank_right_points = $srank->achievers_rank_binary_points_right;

            $total_left_binary_points = Tbl_binary_points::where('binary_points_slot_id',$slot_info->slot_id)->sum('binary_receive_left');
            $total_right_binary_points = Tbl_binary_points::where('binary_points_slot_id',$slot_info->slot_id)->sum('binary_receive_right');
            
            if($total_left_binary_points >=  $achievers_rank_left_points &&  $total_right_binary_points >= $achievers_rank_right_points)
            {
                $update_rank["slot_achievers_rank"] = $achievers_rank_id;
                Tbl_slot::where("slot_id",$slot_id)->update($update_rank);

                $check = Tbl_achievers_rank_list::where("slot_id",$slot_id)->where("rank_id", $achievers_rank_id)->count();
                if($check == 0) {
                    $insert = [
                        "slot_id" => $slot_id,
                        "rank_id" => $achievers_rank_id,
                        "left_downline" => $left,
                        "right_downline" => $right,
                        "qualified_date" => Carbon::now(),
                    ];
                    Tbl_achievers_rank_list::insert($insert);
                }
            }
        }
    }
}
