<?php
namespace App\Http\Controllers\Admin;

use App\Globals\Slot;
use App\Globals\Log;
use App\Globals\Wallet;
use App\Globals\Audit_trail;
use App\Globals\Special_plan;

use App\Models\Tbl_mlm_unilevel_settings;
use App\Models\Tbl_unilevel_points;
use App\Models\Tbl_slot;
use App\Models\Tbl_membership;
use App\Models\Tbl_unilevel_distribute;
use App\Models\Tbl_unilevel_distribute_full;
use App\Models\Tbl_stairstep_distribute;
use App\Models\Tbl_stairstep_distribute_full;

use Illuminate\Support\Facades\Request;
use Carbon\Carbon;

class AdminUnilevelController extends AdminController
{
	public static $gpv = 0;
	public static $child_level   = null;
	public static $child_counter = null;
	
	public function distribute_points($slot_id = null ,$full_id = null, $start_date = null, $end_date = null)
	{	
		$settings = Tbl_mlm_unilevel_settings::first();
		if($settings)
		{
			$slot                     = Tbl_slot::where("slot_id",$slot_id)->first();
			$personal_as_group 		  = $settings->personal_as_group;
			$gpv_to_wallet_conversion = $settings->gpv_to_wallet_conversion;
			$membership               = Tbl_membership::where("membership_id",$slot->slot_membership)->first();

			if($membership)
			{			
                $start_date = carbon::parse($start_date);
                $end_date = carbon::parse($end_date);
				$required_pv          = $membership->membership_required_pv;
				$total_pv             = Tbl_unilevel_points::where("unilevel_points_slot_id",$slot->slot_id)->where("unilevel_points_type","UNILEVEL_PPV")->where("unilevel_points_date_created",">=",$start_date)->where("unilevel_points_date_created","<=",$end_date)->sum("unilevel_points_amount");
				$total_gpv            = Tbl_unilevel_points::where("unilevel_points_slot_id",$slot->slot_id)->where("unilevel_points_type","UNILEVEL_GPV")->where("unilevel_points_date_created",">=",$start_date)->where("unilevel_points_date_created","<=",$end_date)->where("unilevel_points_distribute",0)->sum("unilevel_points_amount");
				$convert_wallet       = 0;
				$status               = 0;

				if($personal_as_group == 1)
				{
					$total_gpv = $total_gpv + Tbl_unilevel_points::where("unilevel_points_slot_id",$slot->slot_id)->where("unilevel_points_type","UNILEVEL_PPV")->where("unilevel_points_date_created",">=",$start_date)->where("unilevel_points_date_created","<=",$end_date)->where("unilevel_points_distribute",0)->sum("unilevel_points_amount");
				}

				$update_log["unilevel_points_distribute"] = 1;
				Tbl_unilevel_points::where("unilevel_points_slot_id",$slot->slot_id)->where("unilevel_points_type","UNILEVEL_PPV")->where("unilevel_points_date_created",">=",$start_date)->where("unilevel_points_date_created","<=",$end_date)->update($update_log);
				Tbl_unilevel_points::where("unilevel_points_slot_id",$slot->slot_id)->where("unilevel_points_type","UNILEVEL_GPV")->where("unilevel_points_date_created",">=",$start_date)->where("unilevel_points_date_created","<=",$end_date)->update($update_log);
				

				if($total_pv >= $required_pv)
				{
					$status         = 1;	
					$convert_wallet = $total_gpv * $settings->gpv_to_wallet_conversion;

					if($convert_wallet != 0)
					{
						Log::insert_wallet($slot_id,$convert_wallet,"UNILEVEL");
						Log::insert_earnings($slot_id,$convert_wallet,"UNILEVEL","UNILEVEL DISTRIBUTION",$slot_id,"", 0);
                		Special_plan::infinity_bonus($slot, "UNILEVEL", $convert_wallet);
					}
				}

				$insert_distribute["unilevel_distribute_date_start"] = $start_date;
				$insert_distribute["unilevel_distribute_end_start"]	 = $end_date;
				$insert_distribute["unilevel_personal_pv"]			 = $total_pv;
				$insert_distribute["unilevel_required_personal_pv"]	 = $required_pv;
				$insert_distribute["unilevel_group_pv"]				 = round($total_gpv, 2);
				$insert_distribute["status"]						 = $status;
				$insert_distribute["unilevel_amount"]				 = $convert_wallet;
				$insert_distribute["unilevel_multiplier"]			 = $settings->gpv_to_wallet_conversion;
				$insert_distribute["unilevel_date_distributed"]		 = Carbon::now();
				$insert_distribute["slot_id"]		 				 = $slot_id;
				$insert_distribute["distribute_full_id"]		     = $full_id;


				Tbl_unilevel_distribute::insert($insert_distribute);
			}
		}
	}


	public function distribute_slot()
	{
		$slot_id    					 = Request::input("slot_id");
		$start_date 					 = Request::input("start_date");  
		$end_date   				     = Request::input("end_date")." 23:59:59"; 	
		$full_id    					 = Request::input("full_id"); 	
		$stairstep_full_id               = Request::input("stairstep_full_id"); 	

		Self::$child_level[$slot_id]   = 0;
		Self::$child_counter[$slot_id] = 0;

		// $parent_id			= 1;
		// $slot_id			= 1;
		// $start_date			= "10/01/2018";
		// $end_date			= "10/31/2018";
		$response  = $this->distribute_points($slot_id,$full_id,$start_date,$end_date,$stairstep_full_id);

		return response()->json($response, 200);
	}


	public function distribute_start()
	{
		$user       = Request::user()->id;
		$action     = "Unilevel Distribute";
		Audit_trail::audit(null,null,$user,$action); 
		$start_date = Request::input("start_date");  
		$end_date   = Request::input("end_date")." 23:59:59"; 	
		$insert["start_date"]		  = $start_date;
		$insert["end_date"]			  = $end_date;
		$insert["distribution_date"]  = Carbon::now();

		$return["status"]             = "success";
		$return["status_code"]        = 201;
		$return["status_message"]     = "Slot Distribution Start";
		$return["distribute_full_id"] = Tbl_unilevel_distribute_full::insertGetId($insert);
		$return["stairstep_distribute_full_id"] = Tbl_stairstep_distribute_full::insertGetId($insert);

		return response()->json($return, 200);
	}
}
