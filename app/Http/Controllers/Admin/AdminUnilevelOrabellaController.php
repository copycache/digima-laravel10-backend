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

use Illuminate\Support\Facades\Request;

class AdminUnilevelOrabellaController extends AdminController
{

	public function get()
	{
		$response["slot_list_total"] = Tbl_slot::JoinMembership()->where('membership_inactive',0)->count();
		$response["date_from"] 		 = Carbon::now()->startOfMonth()->format('Y-m-d');
		$response["date_to"] 		 = Carbon::now()->endOfMonth()->format('Y-m-d');
		return response()->json($response);
	}


	public function get_filtered()
	{
		$from = Request::input("date_from");
		$to   = Request::input("date_to");
		

		$query = Tbl_slot::JoinMembership()
							->where('membership_inactive',0)
							->select('slot_id','slot_no','membership_unilevel_or_level','membership_required_pv_or','membership_id')
							->paginate(15);
		$slot_list	 = $query;


		if($from != null || $from != '' || $to != null ||  $to != '')
		{
			foreach ($slot_list as $key => $slot_list_with_total) 
			{
				$slot_list[$key]->accumulated_pv = Tbl_unilevel_or_points::where('slot_id',$slot_list_with_total->slot_id)->whereDate('created_at','>=',$from)->whereDate('created_at','<=',$to)->where('processed',0)->sum('pv_points') ? Tbl_unilevel_or_points::where('slot_id',$slot_list_with_total->slot_id)->whereDate('created_at','>=',$from)->whereDate('created_at','<=',$to)->where('processed',0)->sum('pv_points') :0;
				if($slot_list[$key]->accumulated_pv < $slot_list_with_total->membership_required_pv_or)
				{
					$slot_list[$key]->remark = "Failed";
				}
				else
				{
					$slot_list[$key]->remark = "Passed";
				}
			}
		}
		return response()->json($slot_list);
	}

	public function unilevel_get_distribute()
	{
		$from = Request::input("date_from");
		$to   = Request::input("date_to");
		

		$query = Tbl_slot::JoinMembership()
							->where('membership_inactive',0)
							->select('slot_id','slot_no','membership_unilevel_or_level','membership_required_pv_or','membership_id')
							->paginate(15);
		$slot_list	 = $query;


		if($from != null || $from != '' || $to != null ||  $to != '')
		{
			foreach ($slot_list as $key => $slot_list_with_total) 
			{
				$slot_list[$key]->accumulated_pv = Tbl_unilevel_or_points::where('slot_id',$slot_list_with_total->slot_id)->whereDate('created_at','>=',$from)->whereDate('created_at','<=',$to)->where('processed',0)->sum('pv_points') ? Tbl_unilevel_or_points::where('slot_id',$slot_list_with_total->slot_id)->whereDate('created_at','>=',$from)->whereDate('created_at','<=',$to)->where('processed',0)->sum('pv_points') :0;
				if($slot_list[$key]->accumulated_pv < $slot_list_with_total->membership_required_pv_or)
				{
					$slot_list[$key]->remark = "Failed";
				}
				else
				{
					$slot_list[$key]->remark = "Passed";
				}
			}
		}
		return response()->json($slot_list);
	}

	public function unilevel_distribute_points()
	{
		$check	= Tbl_mlm_plan::where('mlm_plan_code','=','UNILEVEL_OR')->first()->mlm_plan_enable;
		if($check == 0)
		{
			$response['response'] = 'error';
			$response['status_message'] = 'Unilevel Orabella Complan Disable';
		}
		else
		{
			$slot_info			 = Request::all();
			//dd($slot_info);
			$from 				 = $slot_info['date_from'];
			$to  			     = $slot_info['date_to'];
			$unilevel_or_level   = $slot_info['slot']['membership_unilevel_or_level'];
			$trigger_slot        = [];
			$trigger_points      = [];
			$direct_points       = 0;
        	if($slot_info['slot']['remark'] == "Passed")
        	{
        		$slot_tree         = Tbl_tree_sponsor::where("sponsor_parent_id",$slot_info['slot']['slot_id'])->where('sponsor_level',1)->get();
        		//dd($slot_tree);
        		$ctr               = 0;
        		$level_income      = 0;
        		foreach ($slot_tree as $key => $l1) 
        		{

   				    $slot_distinct = Tbl_unilevel_or_points::whereDate('created_at','>=',$from)->whereDate('created_at','<=',$to)->where('processed',0)->distinct()->get(["slot_id"]);
   					$slot_info1    = Tbl_slot::JoinMembership()->where('slot_id',$l1->sponsor_child_id)->first();
					$check         =  0;   
				    $percent       = Tbl_membership_unilevel_or_level::where('membership_id',$slot_info['slot']['membership_id'])->where('membership_level',1)->first() ? Tbl_membership_unilevel_or_level::where('membership_id',$slot_info['slot']['membership_id'])->where('membership_level',1)->first()->membership_percentage :  0 ;
					foreach ($slot_distinct as $key2 => $x) 
					{
						if($l1->sponsor_child_id == $x->slot_id)
						{
							$check = 1;
						}
					}

					if($check == 1)
					{
						$slot_tree[$key]->accumulated_pv = Tbl_unilevel_or_points::where('slot_id',$l1->sponsor_child_id)->whereDate('created_at','>=',$from)->whereDate('created_at','<=',$to)->where('processed',0)->sum('pv_points') ? Tbl_unilevel_or_points::where('slot_id',$l1->sponsor_child_id)->whereDate('created_at','>=',$from)->whereDate('created_at','<=',$to)->where('processed',0)->sum('pv_points')  : 0;
					}
					else
					{
						$slot_tree[$key]->accumulated_pv = 0;
					}

					if($slot_tree[$key]->accumulated_pv < $slot_info1->membership_required_pv_or)
					{
						$slot_tree[$key]->remark = "Failed";

					}
					else
					{
						$slot_tree[$key]->remark = "Passed";
						$ctr++;
						if($ctr > $unilevel_or_level)
						{
							$ctr = $unilevel_or_level;
						}

						$direct_points = $direct_points + (($percent/100)*$slot_tree[$key]->accumulated_pv);

						array_push($trigger_slot,$l1->sponsor_child_id);
						array_push($trigger_points,(($percent/100)*$slot_tree[$key]->accumulated_pv));

						Log::insert_unilevel_or_points_logs($slot_info['slot']['slot_id'],(($percent/100)*$slot_tree[$key]->accumulated_pv),"UNILEVEL_ORABELLA_PV",$l1->sponsor_child_id, $l1->sponsor_level);

					}

        		}
        		//dd($direct_points);
        		if($ctr != 0 && $ctr != 1)
	    	    {

   					$slot_tree2 =Tbl_tree_sponsor::where('sponsor_parent_id',$slot_info['slot']['slot_id'])->where('sponsor_level','!=','1')->where('sponsor_level','<=',$ctr)->get();
   					//dd('$slot_tree2');
   					foreach ($slot_tree2 as $key => $l2) 
   					{
   						$percent       = Tbl_membership_unilevel_or_level::where('membership_id',$slot_info['slot']['membership_id'])->where('membership_level',$l2->sponsor_level)->first() ? Tbl_membership_unilevel_or_level::where('membership_id',$slot_info['slot']['membership_id'])->where('membership_level',$l2->sponsor_level)->first()->membership_percentage :  0 ;
						$pts 		   = Tbl_unilevel_or_points::where('slot_id',$l2->sponsor_child_id)->whereDate('created_at','>=',$from)->whereDate('created_at','<=',$to)->where('processed',0)->sum('pv_points') ? Tbl_unilevel_or_points::where('slot_id',$l2->sponsor_child_id)->whereDate('created_at','>=',$from)->whereDate('created_at','<=',$to)->where('processed',0)->sum('pv_points')  : 0;
						$slot_info2    = Tbl_slot::JoinMembership()->where('slot_id',$l2->sponsor_child_id)->first();
					
						if($pts >= $slot_info2->membership_required_pv_or)
						{
						    $direct_points = $direct_points + (($percent/100)*$pts);
						    Log::insert_unilevel_or_points_logs($slot_info['slot']['slot_id'],(($percent/100)*$pts),"UNILEVEL_ORABELLA_PV",$l2->sponsor_child_id, $l2->sponsor_level);

						    array_push($trigger_slot,$l2->sponsor_child_id);
							array_push($trigger_points,(($percent/100)*$pts));
						}


   					}
				   
        		}
        		// dd($direct_points);
        		if($direct_points !=0)
        		{
        			$direct_points = round($direct_points,2);
				
        			Log::insert_wallet($slot_info['slot']['slot_id'],$direct_points,"UNILEVEL_OR");

        			foreach ($trigger_slot as $key => $trigger_slot_id) 
        			{
        				$details = "";
        				$level   = Tbl_tree_sponsor::where('sponsor_parent_id',$slot_info['slot']['slot_id'])->where('sponsor_child_id',$trigger_slot_id)->first(); 
        				Log::insert_earnings($slot_info['slot']['slot_id'],$trigger_points[$key],"UNILEVEL_OR","PRODUCT REPURCHASE",$trigger_slot_id,$details,$level->sponsor_level);
        			}
				
        		}

				$response['response'] = 'success';
        		$response['status'] = "Maintained";
        	}
        	else
        	{
				$response['response'] = 'success';
        		$response['status'] = "Not Maintained";
        	}

		}
		


		return response()->json($response);
	}
	public function unilevel_or_points_reset()
	{
		$user       = Request::user()->id;
		$action     = "Unilevel Orabella Distribute";
		Audit_trail::audit(null,null,$user,$action); 
		$from = Request::input("date_from");
		$to   = Request::input("date_to");
		$slot = Tbl_unilevel_or_points::whereDate('created_at','>=',$from)->whereDate('created_at','<=',$to)->where('processed',0)->get();
		foreach ($slot as $key => $v) 
		{
			$update['processed'] = 1;
			Tbl_unilevel_or_points::where('unilevel_or_points_id',$v->unilevel_or_points_id)->update($update);
		}
		$insert['start_date']	     = $from;
		$insert['end_date'] 		 = $to; 
		$insert['distribution_date'] = Carbon::now();
		Tbl_unilevel_or_distribute_full::insert($insert);

		$return["status"]         = "success"; 
		$return["status_code"]    = 400; 
		$return["status_message"] = "POINTS DISTRIBUTED";

		return response()->json($return);

	}

	/*commment dumpster*/

	// public function unilevel_distribute_points()
	// {
	// 	$slot_info			 = Request::all();
	// 	//dd($slot_info);
	// 	$from 				 = $slot_info['date_start'];
	// 	$to  			     = $slot_info['date_end'];
	// 	$unilevel_or_level   = $slot_info['slot']['membership_unilevel_or_level'];
	// 	//$direct_slot         = [];
	// 	$direct_points       = 0;
 //        if($slot_info['slot']['remark'] == "Passed")
 //        {
 //        	$slot_tree         = Tbl_tree_sponsor::where("sponsor_parent_id",$slot_info['slot']['slot_id'])->where('sponsor_level',1)->get();
 //        	//dd($slot_tree);
 //        	$ctr               = 0;
 //        	$level_income      = 0;
 //        	foreach ($slot_tree as $key => $l1) 
 //        	{

 //   			    $slot_distinct = Tbl_unilevel_or_points::whereDate('created_at','>=',$from)->whereDate('created_at','<=',$to)->where('processed',0)->distinct()->get(["slot_id"]);
 //   				$slot_info1    = Tbl_slot::JoinMembership()->where('slot_id',$l1->sponsor_child_id)->first();
	// 			$check         =  0;   
	// 		    $percent       = Tbl_membership_unilevel_or_level::where('membership_id',$slot_info['slot']['membership_id'])->where('membership_level',1)->first() ? Tbl_membership_unilevel_or_level::where('membership_id',$slot_info['slot']['membership_id'])->where('membership_level',1)->first()->membership_percentage :  0 ;
	// 			foreach ($slot_distinct as $key2 => $x) 
	// 			{
	// 				if($l1->sponsor_child_id == $x->slot_id)
	// 				{
	// 					$check = 1;
	// 				}
	// 			}

	// 			if($check == 1)
	// 			{
	// 				$slot_tree[$key]->accumulated_pv = Tbl_unilevel_or_points::where('slot_id',$l1->sponsor_child_id)->sum('pv_points');
	// 			}
	// 			else
	// 			{
	// 				$slot_tree[$key]->accumulated_pv = 0;
	// 			}

	// 			if($slot_tree[$key]->accumulated_pv < $slot_info1->membership_required_pv_or)
	// 			{
	// 				$slot_tree[$key]->remark = "Failed";

	// 			}
	// 			else
	// 			{
	// 				$slot_tree[$key]->remark = "Passed";
	// 				$ctr++;
	// 				if($ctr > $unilevel_or_level)
	// 				{
	// 					$ctr = $unilevel_or_level;
	// 				}

	// 				$direct_points = $direct_points + (($percent/100)*$slot_tree[$key]->accumulated_pv);
	// 				//array_push($direct_slot,$l1->sponsor_child_id);
	// 				Log::insert_unilevel_or_points_logs($slot_info['slot']['slot_id'],$slot_tree[$key]->accumulated_pv,"UNILEVEL_ORABELLA_PV",$l1->sponsor_child_id, $l1->sponsor_level);

	// 			}

 //        	}
 //        	//dd($direct_points);
 //        	if($ctr != 0 && $ctr != 1)
	//         {

 //        		//dd($direct_slot);
 //        		// $pts = Tbl_tree_sponsor::where('sponsor_parent_id',$slot_info['slot']['slot_id'])->join('tbl_unilevel_or_points','slot_id','=','sponsor_child_id')->whereDate('created_at','>=',$from)->whereDate('created_at','<=',$to)->where('processed',0)->where('sponsor_level','!=','1')->where('sponsor_level','<=',$ctr)->sum('pv_points');
 //        		// if($pts)
 //        		// {
 //        		// 	$direct_points = $direct_points + (($percent/100)*$pts);
 //        		// }

 //     //    		$slot_tree2 =Tbl_tree_sponsor::whereIn('sponsor_parent_id',$direct_slot)->where('sponsor_level','<=',$ctr - 1)->get();

 //   		// 		foreach ($slot_tree2 as $key => $l2) 
 //   		// 		{
	// 				// $pts = Tbl_unilevel_or_points::where('slot_id',$l2->sponsor_child_id)->whereDate('created_at','>=',$from)->whereDate('created_at','<=',$to)->where('processed',0)->sum('pv_points') ? Tbl_unilevel_or_points::where('slot_id',$l2->sponsor_child_id)->whereDate('created_at','>=',$from)->whereDate('created_at','<=',$to)->where('processed',0)->sum('pv_points')  : 0;
	// 				// $slot_info2    = Tbl_slot::JoinMembership()->where('slot_id',$l2->sponsor_child_id)->first();
        		
	// 				// if($pts  >= $slot_info2->membership_required_pv_or)
	// 				// {
	// 				// 	$slot_tree[$key]->remark = "Passed";
	// 				//     $direct_points = $direct_points + (($percent/100)*$pts);
					
	// 				// }

 //   		// 		}

 //   				$slot_tree2 =Tbl_tree_sponsor::where('sponsor_parent_id',$slot_info['slot']['slot_id'])->where('sponsor_level','!=','1')->where('sponsor_level','<=',$ctr)->get();
 //   				//dd('$slot_tree2');
 //   				foreach ($slot_tree2 as $key => $l2) 
 //   				{
 //   					$percent       = Tbl_membership_unilevel_or_level::where('membership_id',$slot_info['slot']['membership_id'])->where('membership_level',$l2->sponsor_level)->first() ? Tbl_membership_unilevel_or_level::where('membership_id',$slot_info['slot']['membership_id'])->where('membership_level',$l2->sponsor_level)->first()->membership_percentage :  0 ;
	// 				$pts 		   = Tbl_unilevel_or_points::where('slot_id',$l2->sponsor_child_id)->whereDate('created_at','>=',$from)->whereDate('created_at','<=',$to)->where('processed',0)->sum('pv_points') ? Tbl_unilevel_or_points::where('slot_id',$l2->sponsor_child_id)->whereDate('created_at','>=',$from)->whereDate('created_at','<=',$to)->where('processed',0)->sum('pv_points')  : 0;
	// 				$slot_info2    = Tbl_slot::JoinMembership()->where('slot_id',$l2->sponsor_child_id)->first();
        		
	// 				if($pts >= $slot_info2->membership_required_pv_or)
	// 				{
	// 				    $direct_points = $direct_points + (($percent/100)*$pts);
	// 				    Log::insert_unilevel_or_points_logs($slot_info['slot']['slot_id'],$pts,"UNILEVEL_ORABELLA_PV",$l2->sponsor_child_id, $l2->sponsor_level);
	// 				}


 //   				}
   				
 //        	}
 //        	// dd($direct_points);
 //        	if($direct_points !=0)
 //        	{
 //        		$direct_points = round($direct_points,2);
 //        		$details = "";
 //        		Log::insert_wallet($slot_info['slot']['slot_id'],$direct_points,"UNILEVEL");
 //                //Log::insert_earnings($slot_info['slot']['slot_id'],$direct_points,"UNILEVEL","PRODUCT REPURCHASE",$tree->placement_child_id,$details,$tree->placement_level);
 //        	}
        	

 //        	$response['status'] = "Maintained";
 //        }
 //        else
 //        {
 //        	$response['status'] = "Not Maintained";
 //        }


	// 	return response()->json($response);
	// }



}
