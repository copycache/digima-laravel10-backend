<?php
namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Globals\Get_plan;
use App\Globals\Log;
use App\Globals\Plan;
use App\Globals\Audit_trail;
use App\Models\Tbl_global_pool_bonus_settings;
use App\Models\Tbl_mlm_plan;
use App\Models\Tbl_membership;
use App\Models\Tbl_label;
use App\Models\Tbl_slot;
use App\Models\Tbl_earning_log;



class AdminGlobalPoolController extends AdminController
{
	public function get()
	{
		$_membership = DB::table('tbl_membership')->where("archive",0)->where("global_pool_enabled",1)->pluck('membership_id');
        $date_today  = Carbon::now();		
		$response["slot_list_total"] =  Tbl_slot::where("archive",0)->where("slot_status","active")
																	->where("global_pool_entitiled",1)
																	->whereIn("slot_membership",$_membership)
																	->count();
		$response["slot_list_total_EN"] =  Tbl_slot::where("archive",0)->where("slot_status","active")
																	->whereIn("slot_membership",$_membership)
																	->count();
		return response()->json($response);
	}


	public function filtered()
	{
		$_membership = DB::table('tbl_membership')->where("archive",0)->where("global_pool_enabled",1)->pluck('membership_id');
        $date_today  = Carbon::now();		
		$slot_list	 = Tbl_slot::where("tbl_slot.archive",0)
								->where("slot_status","active")
								->where("global_pool_entitiled",1)
								->whereIn("slot_membership",$_membership)
								->JoinMembership()
								->select("slot_id","slot_no","membership_name")
								// ->get();
								// dd($slot_list);
							    ->paginate(15);
		return response()->json($slot_list);
	}

	public function get_distribute()
	{
		$_membership = DB::table('tbl_membership')->where("archive",0)->where("global_pool_enabled",1)->pluck('membership_id');
        $date_today  = Carbon::now();		
		$slot_list	 = Tbl_slot::where("tbl_slot.archive",0)
								->where("slot_status","active")
								->where("global_pool_entitiled",1)
								->whereIn("slot_membership",$_membership)
								->JoinMembership()
								->select("slot_id","slot_no","membership_name")
								->paginate(15);
								
		return response()->json($slot_list);
	}

	public function distribute_points()
	{
		$slot_id = Request::input("slot_id");
        $count = Request::input("count");		
		$check_pool_money = Tbl_global_pool_bonus_settings::first() ? Tbl_global_pool_bonus_settings::first()->global_pool_amount : 0;
		// $check_get_bonus_pool = null;
		$check_get_bonus_pool = Tbl_earning_log::where("earning_log_slot_id",$slot_id["slot_id"])->where("earning_log_date_created",">=",Carbon::now()->subMinutes(15))->where("earning_log_date_created","<=",Carbon::now()->addMinutes(15))->where("earning_log_plan_type","GLOBAL POOL BONUS")->first();
		if(!$check_get_bonus_pool)
		{
			if($check_pool_money != 0 && $count != 0)
			{
				$earn    = $check_pool_money/$count;
				
				Tbl_slot::where("slot_id",$slot_id["slot_id"])->update(["slot_personal_spv"=>0]);
				Log::insert_wallet($slot_id["slot_id"],round($earn,2),"GLOBAL_POOL_BONUS");
				Log::insert_earnings($slot_id["slot_id"],round($earn,2),"GLOBAL_POOL_BONUS","SLOT DISTRIBUTION",1,"");
				$return["status"]         = "success";
				$return["status_code"]    = 201;
				$return["status_message2"] = "Distributed"; 
			}
			else 
			{
				$return["status"]         = "warning";
				$return["status_code"]    = 205;
				$return["status_message"] = "Either Pool Money Is Zero Or No Slot/s Maintained";
			}
		}
		else 
		{
			$return["status"]         = "warning already distributed";
			$return["status_code"]    = 205;
			$return["status_message2"] = "This already distributed try again after 30 mins";
		}
		return response()->json($return);
	}
	public function done_distribute(Type $var = null)
	{
		DB::table("tbl_slot")->update(["global_pool_entitiled"=>0]);
	}

	
	public function get_intitled()
	{
		$_membership = DB::table('tbl_membership')->where("archive",0)->where("global_pool_enabled",1)->pluck('membership_id');
        $date_today  = Carbon::now();		
		$slot_list	 = Tbl_slot::where("tbl_slot.archive",0)
								->where("slot_status","active")
								->whereIn("slot_membership",$_membership)
								->JoinMembership()
								->select("slot_id","slot_no","membership_name")
								->paginate(15);
								
		return response()->json($slot_list);
	}
	
	public function check_intitled()
	{
		$slot_id = Request::input("slot_id");
		$_slot   = Tbl_slot::where('slot_id',$slot_id["slot_id"])->JoinMembership()->first();
		$_is_intitled = 0;
		if($_slot->slot_personal_spv >= $_slot->global_pool_pv)
		{
			$_is_intitled = 1;
		}
		Tbl_slot::where('slot_id',$slot_id["slot_id"])->update(["global_pool_entitiled"=>$_is_intitled]);
		// dd($slot_id);
		$return["status"]         = "ok";
		return response()->json($return);
	}

    // public function global_pool()
    // {
    //     $_membership = DB::table('tbl_membership')->where("archive",0)->where("global_pool_enabled",1)->pluck('membership_id');
    //     $date_today  = Carbon::now();
    //     $query       = Tbl_slot::where("archive",0)->where("slot_status","active")->where("maintained_until_date",">=",$date_today)
    //                             ->whereIn("slot_membership",$_membership)
    //                             ->select("slot_id");
    //     $count       = $query->count();
    //     $data        = $query->get();
    //     $check_pool_money = Tbl_global_pool_bonus_settings::first() ? Tbl_global_pool_bonus_settings::first()->global_pool_amount : 0;
    //     if($check_pool_money != 0 && $count != 0)
    //     {
    //         $earn    = $check_pool_money/$count;
    //         foreach ($data as $key => $value) 
    //         {
    //             Log::insert_wallet($value->slot_id,round($earn,2),"GLOBAL_POOL_BONUS");
    //             Log::insert_earnings($value->slot_id,round($earn,2),"GLOBAL_POOL_BONUS","SLOT DISTRIBUTION",1,"");
	// 		}
	// 		$return["status"]         = "success";
	// 		$return["status_code"]    = 201;
	// 		$return["status_message"] = "Distribution Complete"; 
	// 	}
	// 	else 
	// 	{
	// 		$return["status"]         = "warning";
	// 		$return["status_code"]    = 205;
	// 		$return["status_message"] = "Either Pool Money Is Zero Or No Slot/s Maintained";
	// 	}
        

	// 	return $return;
	// }
	
    public function global_pool_bonus()
	{
        $data = Request::input("data");
        $plan = Request::input("plan");
        $label = Request::input("label");
		$data = json_decode($data,true);
		$user      = Request::user()->id;
		$action    = "Update Global Pool Bonus";
		$old_value = Get_plan::GLOBAL_POOL_BONUS();

		foreach ($data['membership_settings'] as $key => $value) 
		{
			if(isset($data['membership'][$key]))
			{	
			}
			else 
			{
				$data['membership'][$key] = 0 ;
			}
			Tbl_membership::where("membership_id",$value['membership_id'])->update(['global_pool_enabled'=>$data['membership'][$key]]);
		}
		if(($data["amount"] ?? 0) != 0 || ($data["amount"] ?? "") != "")
		{
			$first = Tbl_global_pool_bonus_settings::first() ? Tbl_global_pool_bonus_settings::first()->global_pool_bonus_id : null;
			if($first)
			{
				Tbl_global_pool_bonus_settings::where("global_pool_bonus_id",$first)->update(["global_pool_amount"=>$data["amount"]]);
			}
		}
		Plan::update_label($plan,$label);
		// $update_plan["mlm_plan_enable"] = 1;
		// Tbl_mlm_plan::where("mlm_plan_code",$plan)->update($update_plan);

		$new_value = Get_plan::GLOBAL_POOL_BONUS();
		Audit_trail::audit(serialize($old_value),serialize($new_value),$user,$action);
		$return["status"]         = "success";
		$return["status_code"]    = 201;
		$return["status_message"] = "Settings updated...";

		return $return;

	}
}
