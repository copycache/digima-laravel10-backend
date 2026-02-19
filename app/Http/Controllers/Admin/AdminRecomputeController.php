<?php
namespace App\Http\Controllers\Admin;
use App\Globals\Plan;
use App\Globals\Slot;
use App\Globals\Currency;
use App\Globals\Wallet;
use App\Globals\Log;
use App\Globals\Audit_trail;
use App\Globals\Mlm_complan_manager;
use App\Models\Tbl_slot;
use App\Models\Tbl_wallet_log;
use App\Models\Tbl_earning_log;
use App\Models\Tbl_mlm_plan;
use App\Models\Tbl_binary_points;
use App\Models\Tbl_membership;
use App\Models\Tbl_tree_sponsor;

use App\Models\Tbl_tree_placement;
use App\Models\Tbl_leveling_bonus_points;
use App\Models\Tbl_membership_leveling_bonus_level;
use App\Models\Tbl_membership_upgrade_settings;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Request;
class AdminRecomputeController extends AdminController
{
    public function get_slots_pass_up() 
	{
		$data = Slot::get_slots_pass_up();
	    return response()->json($data, 200);
	}

	public function get_slots_pass_up_all() 
	{
		$data = Slot::get_slots_pass_up_all();
	    return response()->json($data, 200);
	}

	public function get_slots_binary() 
	{
		$data = Slot::get_slots_binary();
	    return response()->json($data, 200);
	}

	public function get_slots_binary_all() 
	{
		$data = Slot::get_slots_binary_all();
	    return response()->json($data, 200);
	}

	public function recompute_slot()
	{
		$check_password = Request::input("password");

		if($check_password == "waterqwerty")
		{
			$slot_id        = Request::input("slot_id");

			$wallet_log = Tbl_wallet_log::where("wallet_log_slot_id",$slot_id)->where("wallet_log_details","PASS UP")->get();

			if(count($wallet_log) != 0)
			{
				foreach($wallet_log as $log)
				{
					Wallet::update_wallet($log->wallet_log_slot_id, ($log->wallet_log_amount * -1) ,$log->currency_id);
				}
				
				Tbl_wallet_log::where("wallet_log_slot_id",$slot_id)->where("wallet_log_details","PASS UP")->delete();
				Tbl_earning_log::where("earning_log_slot_id",$slot_id)->where("earning_log_plan_type","PASS UP")->delete();
			}



	        $pass_up = Tbl_mlm_plan::where('mlm_plan_enable', 1)->where('mlm_plan_code', 'PASS_UP')->first();
	        if($pass_up)
	        {
	        	$slot_info = Tbl_slot::where('slot_id', $slot_id)->where("slot_sponsor","!=","0")->first();
	        	if($slot_info)
	        	{
	        		Mlm_complan_manager::pass_up($slot_info);
	        	}
	        }

			$response["status"] = "Success";
			return response()->json($response, 200);
		}
	}

	public function check_password()
	{
		$response["status"] = "fail";
		$password  = Request::input("password");

		if($password == "waterqwerty")
		{
			$response["status"] = "success";
			//
			$user       = Request::user()->id;
			$action     = "Pass Up Recompute";
			Audit_trail::audit(null,null,$user,$action); 
			//
		}

		return response()->json($response, 200);
	}

	public function check_password2()
	{
		$response["status"] = "fail";
		$password  = Request::input("password");
		$slot_no   = Request::input("slot_no");

		if($password == "waterqwerty")
		{
			$check = Tbl_slot::where('slot_no', $slot_no)->where("slot_sponsor","!=","0")->first();
			if(!$check)
			{
				$response["status"] = "noslot";
			}
			else
			{
				$user       = Request::user()->id;
				$action     = "Single Recompute";
				Audit_trail::audit(null,$slot_no,$user,$action); 
				//

		        $binary = Tbl_mlm_plan::where('mlm_plan_enable', 1)->where('mlm_plan_code', 'BINARY')->first();
		        if($binary)
		        {
		        	$slot_info = Tbl_slot::where('slot_no', $slot_no)->where("slot_sponsor","!=","0")->first();
		        	if($slot_info)
		        	{
		        		Mlm_complan_manager::binary($slot_info);
		        	}
		        }

				$response["status"] = "success";
			}
		}

		return response()->json($response, 200);
	}

	public function recomputesingle()
	{
		$check_password = Request::input("password");

		if($check_password == "waterqwerty")
		{
			$slot_no        = Request::input("slot_no");
			//
			$user       = Request::user()->id;
			$action     = "Single Recompute";
			Audit_trail::audit(null,$slot_no,$user,$action); 
			//

	        $binary = Tbl_mlm_plan::where('mlm_plan_enable', 1)->where('mlm_plan_code', 'BINARY')->first();
	        if($binary)
	        {
	        	$slot_info = Tbl_slot::where('slot_no', $slot_no)->where("slot_sponsor","!=","0")->first();
	        	if($slot_info)
	        	{
	        		$check_if_need = Tbl_binary_points::where("binary_cause_slot_id",$slot_info->slot_id)->first();
	        		if(!$check_if_need)
	        		{
	        			Mlm_complan_manager::binary($slot_info);
	        		}
	        	}
	        }

	        $leveling = Tbl_mlm_plan::where('mlm_plan_enable', 1)->where('mlm_plan_code', 'LEVELING_BONUS')->first();
	        if($leveling)
	        {
	        	$slot_info = Tbl_slot::where('slot_no', $slot_no)->where("slot_sponsor","!=","0")->first();
	        	if($slot_info)
	        	{
	        		Mlm_complan_manager::leveling_bonus($slot_info);
	        	}
	        }

			$response["status"] = "success";
			return response()->json($response, 200);
		}
	}

	public function recompute_single_pass_up()
	{
		$check_password = Request::input("password");

		if($check_password == "waterqwerty")
		{
			$slot_no        = Request::input("slot_no");
			if($slot_no != null)
			{
				//
				$user       = Request::user()->id;
				$action     = "Single Pass Up Recompute";
				Audit_trail::audit(null,$slot_no,$user,$action); 
				//

				$slot_id		= Tbl_slot::where('slot_no','=',$slot_no)->first()->slot_id;

				$earning_log     = Tbl_earning_log::where("earning_log_cause_id","=",$slot_id)->where("earning_log_plan_type","=","PASS UP")->first();

				if(!$earning_log)
				{
					$pass_up = Tbl_mlm_plan::where('mlm_plan_enable', 1)->where('mlm_plan_code', 'PASS_UP')->first();
					if($pass_up)
					{
						$slot_info = Tbl_slot::where('slot_id', $slot_id)->where("slot_sponsor","!=","0")->first();
						if($slot_info)
						{
							Mlm_complan_manager::pass_up($slot_info);
						}
					}
					$response["status"] = "Success";
					
				}
				else 
				{
					$response["status"] = "All Ready Recompute";
				}
			}
			else 
			{
				$response["status"] = "No Slot no.";
			}
			return response()->json($response, 200);
		}
	}
	public static function leveling_bonus_recompute()
	{
		$check_password = Request::input("password");

		if($check_password == "waterqwerty")
		{
			
			$check_slot = Request::input("slot_no");
			if($check_slot != null)
			{
				$slot_info		= Tbl_slot::where('slot_no','=',$check_slot)->first();
				if($slot_info)
				{
					//
					$user       = Request::user()->id;
					$action     = "Leveling Bonus Recompute";
					Audit_trail::audit(null,$check_slot,$user,$action); 
					//
					$tree_placement      = Tbl_tree_placement::where("placement_child_id",$slot_info->slot_id)->orderBy("placement_level","ASC")->get();
        
					foreach($tree_placement as $tree)
					{
						$slot_placement  = Tbl_slot::where("slot_id",$tree->placement_parent_id)->first();
						
						$points_settings = Tbl_leveling_bonus_points::where("membership_id",$slot_placement->slot_membership)->where("slot_id",$slot_placement->slot_id)->get();
						// dd($points_settings);
						if(count($points_settings) == 0)
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
					$response['status_code'] 	= 400;
					$response['status'] 	 	= 'success';
					$response['status_message'] = 'Recompute Success';
				}
				else 
				{
					$response['status_code'] 	= 401;
					$response['status'] 	 	= 'error';
					$response['status_message'] = 'No slot ID register';
				}
			}
			else 
			{
				$response['status_code'] 	= 401;
				$response['status'] 	 	= 'warning';
				$response['status_message'] = 'No input slot';
			}
		}
		else 
		{
			$response['status_code'] 	= 401;
			$response['status'] 	 	= 'warning';
			$response['status_message'] = 'Wrong Password';
		}

		return response()->json($response, 200);
	}

	public function start()
	{
		$count = Request::input('count');
		$total_count = Request::input('total');
		if(Request::input('password')== "asd")
		{
			if($count > 0)
			{
				$slot = Tbl_slot::where('slot_id', Request::input('count'))->first();
				$slot_membership = Tbl_membership::where('membership_id', $slot->slot_membership)->first();
				if($slot->slot_membership != 0)
				{ 
					$check_kind_of_upgrade = Tbl_membership_upgrade_settings::first() ? Tbl_membership_upgrade_settings::first()->membership_upgrade_settings_method : "direct_downlines";
					if($check_kind_of_upgrade == "direct_downlines")
					{
						$count_sponsored = Tbl_slot::where('slot_sponsor', $slot->slot_id)->count();
						$check_downline_slots  = Tbl_tree_sponsor::where('sponsor_parent_id', $slot->slot_id)->count();						
						$next_membership = Tbl_membership::where('hierarchy', '>', $slot_membership->hierarchy)->where('required_directs', '<=' , $count_sponsored)->where('required_downlines', '<=' , $check_downline_slots)->where('archive', 0)->orderBy('hierarchy', "DESC")->first();
						if($next_membership)
						{
							if($count_sponsored >= $next_membership->required_directs && $check_downline_slots >= $next_membership->required_downlines)
							{
								$update['slot_membership']	= $next_membership->membership_id;
								Tbl_slot::where('slot_id', $slot->slot_id)->update($update);
								$count = $count + 1;
								$return['count'] = $count;
								$return['total_count'] = $total_count;
	
							}
							else
							{
								$count = $count + 1;
								$return['count'] = $count;
								$return['total_count'] = $total_count;
	
							}
						}
						else
						{
							$count = $count + 1;
							$return['count'] = $count;
							$return['total_count'] = $total_count;
						}
					}
					else 
					{
						$slot_sponsor = Tbl_slot::where('slot_id',Request::input('count'))->join('tbl_membership', 'tbl_slot.slot_membership', '=', 'tbl_membership.membership_id')->first();
						$points  = Tbl_slot::where('slot_sponsor',$slot_sponsor->slot_id)->JoinMembership()->sum("given_upgrade_points");
						$get_next_hierarchy = Tbl_membership::where('hierarchy', '>', $slot_sponsor->hierarchy)->where('required_upgrade_points', '<=' , $points)->where('archive', 0)->orderBy('hierarchy', 'DESC')->first();
						$update_points['slot_upgrade_points'] = $points;
						Tbl_slot::where('slot_id', $slot_sponsor->slot_id)->update($update_points);
						if($get_next_hierarchy)
						{
							if($points >= $get_next_hierarchy->required_upgrade_points)
							{
								$log['slot_id'] = $slot_sponsor->slot_id;
								$log['old_membership_id'] = $slot_sponsor->slot_membership;
								$log['new_membership_id'] = $get_next_hierarchy->membership_id;
								$log['upgraded_at']       = Carbon::now();
								
								DB::table('tbl_membership_upgrade_logs')->insert($log);
				
								$update['slot_membership'] = $get_next_hierarchy->membership_id;
								Tbl_slot::where('slot_id', $slot_sponsor->slot_id)->update($update);
							}
							$count = $count + 1;
							$return['count'] = $count;
							$return['total_count'] = $total_count;
						}
						else
						{
							$count = $count + 1;
							$return['count'] = $count;
							$return['total_count'] = $total_count;
						}
					}
				}
				else
				{
					$count = $count + 1;
					$return['count'] = $count;
					$return['total_count'] = $total_count;

				}
				
			}
			else
			{
				$return['total_count'] = Tbl_slot::count();
				$count = $count + 1;
				$return['count']	= $count;
			}
		}
		else
		{
			$return['status']	= 'error';
			$return['status_message'] = "wrong password";
		}

		return response()->json($return);
	}
}
