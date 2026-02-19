<?php
namespace App\Http\Controllers\Member;
use App\Http\Controllers\Controller;
use App\Models\Tbl_achievers_rank;
use App\Models\Tbl_achievers_rank_claimed_history;
use App\Models\Tbl_achievers_rank_list;
use Illuminate\Support\Facades\Request;
use Carbon\Carbon;
use App\Models\Tbl_slot;

class MemberAchieversRankController extends Controller
{
    // Created By: Centy - 10-27-2023
    public function achievers_rank()
    {
        $slot_id = Request::input('slot_id');

        $achievers_rank = Tbl_achievers_rank::where("archive", 0)
            ->select("achievers_rank_id", "achievers_rank_level", "achievers_rank_name", "achievers_rank_binary_points_left", "achievers_rank_binary_points_right", "achievers_rank_reward", "achievers_rank_date_created")
            ->get();
        $achievers_rank_user = Tbl_achievers_rank_list::where("slot_id", $slot_id)
            ->select("status")
            ->get(); 

        $combinedArray = [];
        $status1Array = [];

        $you = Tbl_slot::where('slot_id', $slot_id)
            ->pluck('tbl_slot.slot_no')
            ->first();
        $left = Tbl_slot::where('slot_placement', $slot_id)
            ->where('slot_position', 'LEFT')
            ->pluck('tbl_slot.slot_no')
            ->first();
        $right = Tbl_slot::where('slot_placement', $slot_id)
            ->where('slot_position', 'RIGHT')
            ->pluck('tbl_slot.slot_no')
            ->first();

        foreach ($achievers_rank as $index => $srank) {
            $srank["status"] = isset($achievers_rank_user[$index]) ? $achievers_rank_user[$index]->status : null;
            $srank["slot_username"] = $you;
            $srank["left_downline"] = isset($left) ? $left : null;
            $srank["right_downline"] = isset($right) ? $right : null;
            
            if ($srank["status"] == 1) {
                $status1Array[] = $srank;
            } else {
                $combinedArray[] = $srank;
            }
        }

        $combinedArray = array_merge($combinedArray, $status1Array);        
        
        $data["achievers_ranks"] = $combinedArray;

        return $data;
    }

    public static function achievers_claimed(){
        
        $slot_id = Request::input('slot_id');
        $rank_id = Request::input('rank_id');
        $slot_info = Tbl_slot::where('slot_id', $slot_id)->first();
        
        if (!$slot_info) {
            return 'Slot not found';
        }
    
        $update = ['status' => 2];

        $user = Tbl_achievers_rank_list::where('slot_id', $slot_id)
                ->where('rank_id', $rank_id)
                ->first();
        if ($user && $user->status == 0 || $user->status == 3) {
            $update_status = Tbl_achievers_rank_list::where('slot_id', $slot_id)
                ->where('rank_id', $rank_id)
                ->update($update);
            $check = Tbl_achievers_rank_claimed_history::where('slot_id', $slot_id)
                ->where('rank_id', $rank_id)->first();

            if($check) {
                if ($check->rejected_date) {
                    $insert = [
                        "slot_id" => $user->slot_id,
                        "rank_id" => $user->rank_id,
                        "claimed_date" => Carbon::now(),
                    ];
                    Tbl_achievers_rank_claimed_history::insert($insert);
                }
                else {
                    $update_date = ["claimed_date" => Carbon::now()];
                    Tbl_achievers_rank_claimed_history::where('slot_id', $slot_id)
                    ->where('rank_id', $rank_id)
                    ->update($update_date);
                }
            }
            else {
                $insert = [
                    "slot_id" => $user->slot_id,
                    "rank_id" => $user->rank_id,
                    "claimed_date" => Carbon::now(),
                ];
                Tbl_achievers_rank_claimed_history::insert($insert);
            }
        }
    
        $response['status'] = 'Success';
        return $response;
    }
}
