<?php
namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use App\Globals\Slot;
use App\Globals\Log;
use App\Globals\Audit_trail;

use App\Models\Tbl_membership;
use App\Models\Tbl_unilevel_or_points;
use App\Models\Tbl_slot;
use App\Models\Tbl_slot_tree;
use App\Models\Tbl_tree_sponsor;
use App\Models\Tbl_membership_unilevel_or_level;
use App\Models\Tbl_unilevel_or_distribute_full;
use App\Models\Tbl_mlm_plan;
use App\Models\Tbl_currency;


use Illuminate\Support\Facades\Request;

class AdminDistributePersonalController extends AdminController
{

	public function get()
	{
		$response["slot_list_total"] = Tbl_slot::JoinMembership()->where('membership_inactive',0)->count();
		$response["date_from"] 		 = Carbon::now()->startOfMonth()->format('Y-m-d');
		$response["date_to"] 		 = Carbon::now()->endOfMonth()->format('Y-m-d');
		return response()->json($response);
	}


	public function filtered()
	{
		$from = Request::input("date_from");
		$to   = Request::input("date_to");
		

		$query = Tbl_slot::JoinMembership()
							->where('membership_inactive',0)
							->select('slot_id','slot_no','slot_cashback_points')
							->paginate(15);
		$slot_list	 = $query;
		return response()->json($slot_list);
	}

	public function get_distribute()
	{
		$from = Request::input("date_from");
		$to   = Request::input("date_to");
		$query = Tbl_slot::JoinMembership()
							->where('membership_inactive',0)
							->select('slot_id','slot_no','slot_cashback_points')
							->paginate(15);
		$slot_list	 = $query;
		return response()->json($slot_list);
	}

	public function distribute_points()
	{
        $slot_id = Request::input("slot_id");
		$slot_cashback_points = Request::input("slot_cashback_points");
		if($slot_cashback_points >= 0)
		{
			if($slot_cashback_points != 0)
			{
				Log::insert_wallet($slot_id,$slot_cashback_points,"PERSONAL_CASHBACK");
				$details = "";
				Log::insert_earnings($slot_id,$slot_cashback_points,"PERSONAL_CASHBACK","PRODUCT REPURCHASE",$slot_id,$details,0);
				Tbl_slot::where("slot_id",$slot_id)->update(['slot_cashback_points'=> 0 ]);
			}
			$response['status'] = 'success';
		}
		else 
		{
			$response['status'] = 'error';
			$response['status_message'] = "Error in getting Cashback Points";
		}
		return response()->json($response);
	}
}
