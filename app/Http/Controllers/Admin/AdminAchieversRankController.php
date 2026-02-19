<?php

namespace App\Http\Controllers\Admin;

use App\Globals\Log;
use App\Models\Tbl_achievers_rank_claimed_history;
use App\Models\Tbl_achievers_rank_list;
use App\Models\Tbl_achievers_rank_wallet_earnings;
use App\Models\Tbl_slot;
use App\Models\Tbl_tree_placement;
use App\Models\Tbl_wallet_log;
use Illuminate\Http\Request;
use Carbon\Carbon;



class AdminAchieversRankController extends AdminController
{
    // Created By: Centy - 10-27-2023
    public function list_of_claimed_achievers_rewards(Request $request)
    {
        $number_in_list = $request->number_in_list;
        $username = $request->username;
        $rank = $request->rank;
        $reward = $request->reward;
        $search = $request->search;
        $date_to = $request->date_to;
        $date_from = $request->date_from;
        $status = $request->status;

        if ($search == 'undefined') {
			$search = null;
		}
		

        $query = Tbl_achievers_rank_claimed_history::AchieversRankList()
                    ->Slot()
                    ->Owner()
                    ->AchieversRankAttribute();

		if (!empty($search)) {
			$query->where('slot_no', 'like', '%' . $search . '%');
		}

		if (!empty($date_from)) {
			$query->whereDate('approved_date', '>=', $date_from);
		}

		if (!empty($date_to)) {
			$query->whereDate('approved_date', '<=', $date_to);
		}

        if (!empty($status)) {
            if($status === "1") {
                $query->whereNotNull('tbl_achievers_rank_claimed_history.approved_date');
            }
            else if($status === "2") {
                $query->whereNull('tbl_achievers_rank_claimed_history.approved_date')->whereNull('tbl_achievers_rank_claimed_history.rejected_date');
            }
            if($status === "3") {
                $query->whereNotNull('tbl_achievers_rank_claimed_history.rejected_date');
            }
        }

        if ($number_in_list == 1) {
            $query->orderBy('tbl_achievers_rank_list.list_id', 'asc');
        }
        else if ($number_in_list == 2) {
            $query->orderBy('tbl_achievers_rank_list.list_id', 'desc');
        }
    
        if ($username == 1) {
            $query->orderBy('tbl_achievers_rank_list.slot_id', 'asc');
        }
        else if ($username == 2) {
            $query->orderBy('tbl_achievers_rank_list.slot_id', 'desc');
        }
    
        if ($rank == 1) {
            $query->orderBy('tbl_achievers_rank_list.rank_id', 'asc');
        }
        else if ($rank == 2) {
            $query->orderBy('tbl_achievers_rank_list.rank_id', 'desc');
        }
    
        if ($reward  == 1) {
            $query->orderBy('tbl_achievers_rank.achievers_rank_reward', 'asc');
        }
        else if ($reward == 2) {
            $query->orderBy('tbl_achievers_rank.achievers_rank_reward', 'desc');
        }

        $records = $query->orderByRaw("ISNULL(approved_date) AND ISNULL(rejected_date) DESC")
        ->orderBy('approved_date', 'DESC')
        ->get();

        return response()->json($records);
    }

    public static function achievers_approval(Request $request){
        
        $slot_id = $request->slot_id;
        $rank_id = $request->rank_id;
        $status = $request->status;
        
        $update = [];
        $update_date = [];

        if ($status == 1) {
            $update = ['status' => 1];
            $update_date = ['approved_date' => Carbon::now()];
            $message = 'Approved';
            self::achievers_earning($slot_id, $rank_id);
        }
        else if ($status == 3) {
            $update = ['status' => 3];
            $update_date = ['rejected_date' => Carbon::now()];
            $message = 'Rejected';
        }

        Tbl_achievers_rank_list::where('slot_id', $slot_id)
            ->where('rank_id', $rank_id)
            ->update($update);
        
        Tbl_achievers_rank_claimed_history::where('slot_id', $slot_id)
            ->where('rank_id', $rank_id)
            ->whereNull('rejected_date')
            ->update($update_date);
        
        $response['message'] =   "Successfully {$message}!";

        return $response; 
    }
    public static function achievers_earning($slot_id, $rank_id) {
        
        $slot = Tbl_achievers_rank_list::AchieversRankAttribute()
            ->where('slot_id', $slot_id)
            ->where('rank_id', $rank_id)
            ->first();
            
        $slot_username = Tbl_slot::where('slot_id', $slot->slot_id)->first()->slot_no;
        $sponsor_id    = $slot->slot_id;
        
        if($slot) {
            if ($slot->achievers_rank_reward) {
                $slot_reward = 0.5;
                $left_downline_reward = 0.25;
                $right_downline_reward = 0.25;

                self::processSlotReward($slot, $slot_username, $slot_reward, $sponsor_id);
                self::processSlotReward($slot, $slot->left_downline, $left_downline_reward, $sponsor_id);
                self::processSlotReward($slot, $slot->right_downline, $right_downline_reward, $sponsor_id);
                
            } else {
                dd('No Reward');
            }
        } else {
            dd('Not Slot Qualified');
        }
        
    }

    public static function processSlotReward($slot, $username, $rewardMultiplier, $sponsor_id) {
        $reward = $slot->achievers_rank_reward * $rewardMultiplier;
        $slot_id = Tbl_slot::where('slot_no', $username)->value('slot_id');
        Log::insert_wallet($slot_id, $reward, "ACHIEVERS RANK");
        Log::insert_earnings($slot_id, $reward, "ACHIEVERS RANK", "SPECIAL PLAN", $sponsor_id, "", 1);
    }
    
}
