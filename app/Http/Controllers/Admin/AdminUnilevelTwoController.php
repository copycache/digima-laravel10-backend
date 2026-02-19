<?php
namespace App\Http\Controllers\Admin;

use App\Globals\Slot;
use App\Globals\Log;
use App\Globals\Wallet;
use App\Globals\Audit_trail;
use App\Globals\Special_plan;
use App\Models\Tbl_mlm_unilevel_settings;
use App\Models\Tbl_unilevel_points;
use App\Models\Tbl_stairstep_points;
use App\Models\Tbl_slot;
use App\Models\Tbl_membership;
use App\Models\Tbl_unilevel_distribute;
use App\Models\Tbl_stairstep_rank;
use App\Models\Tbl_tree_sponsor;
use App\Models\Tbl_unilevel_distribute_full;
use App\Models\Tbl_stairstep_settings;
use App\Models\Tbl_override_points;
use App\Models\Tbl_stairstep_distribute;
use App\Models\Tbl_stairstep_distribute_full;
use App\Models\Tbl_membership_unilevel_level;
use App\Models\Tbl_dynamic_compression_record;

use Illuminate\Support\Facades\Request;
use Carbon\Carbon;

class AdminUnilevelTwoController extends AdminController
{
	public static $gpv = 0;
	public static $child_level   = null;
	public static $child_counter = null;

	public function distribute_points($slot_id,$start_date, $end_date,$full_id, $stairstep_full_id)
	{
		$settings    = Tbl_mlm_unilevel_settings::first();
		$st_settings = Tbl_stairstep_settings::first();

		if($settings && $st_settings)
		{
			$slot                     = Tbl_slot::where("slot_id",$slot_id)->first();
			$personal_as_group 		  = $settings->personal_as_group;
			$gpv_to_wallet_conversion = $settings->gpv_to_wallet_conversion;
			$membership               = Tbl_membership::where("membership_id",$slot->slot_membership)->first();

			if($membership)
			{			
                $start_date           = Carbon::parse($start_date);
                $end_date             = Carbon::parse($end_date);

				$total_sgpv            = Tbl_stairstep_points::where("stairstep_points_slot_id",$slot->slot_id)->where("stairstep_points_type","STAIRSTEP_GPV")->where("stairstep_points_date_created",">=",$start_date)->where("stairstep_points_date_created","<=",$end_date)->sum("stairstep_points_amount");
				$total_spv             = Tbl_stairstep_points::where("stairstep_points_slot_id",$slot->slot_id)->where("stairstep_points_type","STAIRSTEP_PPV")->where("stairstep_points_date_created",">=",$start_date)->where("stairstep_points_date_created","<=",$end_date)->sum("stairstep_points_amount");

				$required_pv          = $membership->membership_required_pv;
				$required_spv         = 0;
				$total_pv             = Tbl_unilevel_points::where("unilevel_points_slot_id",$slot->slot_id)->where("unilevel_points_type","UNILEVEL_PPV")->where("unilevel_points_date_created",">=",$start_date)->where("unilevel_points_date_created","<=",$end_date)->where("unilevel_points_distribute",0)->sum("unilevel_points_amount");
				// $total_gpv            = Tbl_unilevel_points::where("unilevel_points_slot_id",$slot->slot_id)->where("unilevel_points_type","UNILEVEL_GPV")->where("unilevel_points_date_created",">=",$start_date)->where("unilevel_points_date_created","<=",$end_date)->where("unilevel_points_distribute",0)->sum("unilevel_points_amount");
										$this->loop($slot_id,$slot_id,$start_date,$end_date);
				$total_gpv            = Self::$gpv;

				$total_override       = Tbl_override_points::where("slot_id",$slot->slot_id)->where("override_points_date_created",">=",$start_date)->where("override_points_date_created","<=",$end_date)->where("distributed",0)->sum("override_amount");
				$convert_wallet       = 0;
				$status               = 0;
				$status_stairstep     = 0;
				$unilevel_multiplier  = 0;
				$override_converted   = 0;
				$stairstep_override_points   = $total_override;

				if($personal_as_group == 1)
				{
					$total_gpv = $total_gpv + Tbl_unilevel_points::where("unilevel_points_slot_id",$slot->slot_id)->where("unilevel_points_type","UNILEVEL_PPV")->where("unilevel_points_date_created",">=",$start_date)->where("unilevel_points_date_created","<=",$end_date)->where("unilevel_points_distribute",0)->sum("unilevel_points_amount");
				}

				$update_log["unilevel_points_distribute"] = 1;
				Tbl_unilevel_points::where("unilevel_points_slot_id",$slot->slot_id)->where("unilevel_points_type","UNILEVEL_PPV")->where("unilevel_points_date_created",">=",$start_date)->where("unilevel_points_date_created","<=",$end_date)->update($update_log);
				Tbl_unilevel_points::where("unilevel_points_slot_id",$slot->slot_id)->where("unilevel_points_type","UNILEVEL_GPV")->where("unilevel_points_date_created",">=",$start_date)->where("unilevel_points_date_created","<=",$end_date)->update($update_log);
				

				if($total_pv >= $required_pv)
				{
					$status            = 1;
					if($total_gpv != 0)
					{		
						$get_current_rank       = Tbl_stairstep_rank::where("stairstep_rank_id",$slot->slot_stairstep_rank)->first();
						// if($get_current_rank)
						// {
							// if($get_current_rank->stairstep_commission != 0)
							// {
								$income_wallet = $total_gpv * $settings->gpv_to_wallet_conversion;
								if($income_wallet != 0)
								{
									$convert_wallet = $income_wallet;

									$cd_package = Tbl_membership::where('hierarchy',1)->pluck('membership_id')->first();
									$cd_slot_info = Tbl_slot::where('slot_id',$slot_id)->first();
									if($cd_slot_info->slot_membership == $cd_package)
									{
										$cd_earnings    = $income_wallet * 0.2;
										$income_wallet1 = $income_wallet - $cd_earnings;

										Log::insert_wallet($slot_id,$income_wallet1,"UNILEVEL_COMMISSION");
										Log::insert_wallet($slot_id,$cd_earnings,"UNILEVEL_COMMISSION",18);
									}
									else
									{
										Log::insert_wallet($slot_id,$income_wallet,"UNILEVEL_COMMISSION");
									}

									Log::insert_earnings($slot_id,$income_wallet,"UNILEVEL_COMMISSION","UNILEVEL DISTRIBUTION",$slot_id,"", 0);
                					Special_plan::infinity_bonus($slot, "UNILEVEL", $income_wallet);
								
								}
							// }
						// }

				        $sponsor_highest_rank   = Tbl_tree_sponsor::where("sponsor_child_id",$slot_id)->parent()->rank()->orderBy("tbl_stairstep_rank.check_match_level","DESC")->first();
				        if($sponsor_highest_rank)
				        {
					        if($sponsor_highest_rank->check_match_level == null)
					        {
					            $max_level = 0;
					        }
					        else
					        {
					            $max_level = $sponsor_highest_rank->check_match_level;
					        }   	
				        }
				        else
				        {
				        	$max_level = 0;
				        }


				        /* CHECK MATCH INCOME FOR PARENT SPONSOR */
				        $get_parent = Tbl_tree_sponsor::where("sponsor_child_id",$slot_id)->where("sponsor_level","<=",$max_level)->get();

				        if($total_gpv != 0)
				        {
				        	foreach($get_parent as $pt)
				        	{
				        		$parent_slot        = Tbl_slot::where("slot_id",$pt->sponsor_parent_id)->first();
				        		$parent_membership  = Tbl_membership::where("membership_id",$parent_slot->slot_membership)->first();
				        		$parent_required_pv = $parent_membership->membership_required_pv;
				        		$parent_total_ppv   = Tbl_unilevel_points::where("unilevel_points_slot_id",$parent_slot->slot_id)->where("unilevel_points_type","UNILEVEL_PPV")->where("unilevel_points_date_created",">=",$start_date)->where("unilevel_points_date_created","<=",$end_date)->sum("unilevel_points_amount");
				        		
				        		if($parent_total_ppv >= $parent_required_pv)
				        		{
					        		$parent_rank        = Tbl_stairstep_rank::where("stairstep_rank_id",$parent_slot->slot_stairstep_rank)->first();
					        		if($parent_rank)
					        		{
					        			if($pt->sponsor_level <= $parent_rank->check_match_level)
					        			{
					        				if($parent_rank->check_match_percentage != 0)
					        				{
					        					$check_match_income = $total_gpv * ($parent_rank->check_match_percentage/100);
					        					if($check_match_income != 0)
					        					{
					        						Log::insert_wallet($parent_slot->slot_id,$check_match_income,"CHECK_MATCH_INCOME");
					        						Log::insert_earnings($parent_slot->slot_id,$check_match_income,"CHECK_MATCH_INCOME","UNILEVEL DISTRIBUTION",$slot_id,"", $pt->sponsor_level);
					        						
					        						$distri_history = Tbl_unilevel_distribute::where("distribute_full_id",$full_id)->where("slot_id",$parent_slot->slot_id)->first();
					        						if($distri_history)
					        						{
					        							$update_history["check_match_bonus"] = $distri_history->check_match_bonus + $check_match_income;
					        							Tbl_unilevel_distribute::where("distribute_full_id",$full_id)->where("slot_id",$parent_slot->slot_id)->update($update_history);
					        						}
					        					}
					        				}
					        			}
					        		}	
				        		}
				        	}
				        }



				        $sponsor_highest_rank   = Tbl_tree_sponsor::where("sponsor_child_id",$slot_id)->parent()->rank()->orderBy("tbl_stairstep_rank.breakaway_level","DESC")->first();
				        if($sponsor_highest_rank)
				        {
					        if($sponsor_highest_rank->breakaway_level == null)
					        {
					            $max_level_breakaway = 0;
					        }
					        else
					        {
					            $max_level_breakaway = $sponsor_highest_rank->breakaway_level;
					        }   	
				        }
				        else
				        {
				        	$max_level_breakaway = 0;
				        }

				        $get_parent        = Tbl_tree_sponsor::where("sponsor_child_id",$slot_id)->where("sponsor_level","<=",$max_level_breakaway)->get();
				        
				        $slot_root         = Tbl_slot::where("slot_id",$slot_id)->first();
				        $get_root_current_rank  = Tbl_stairstep_rank::where("stairstep_rank_id",$slot_root->slot_stairstep_rank)->first();
				       
				        if($total_gpv != 0 && $get_root_current_rank)
				        {
				        	foreach($get_parent as $pt)
				        	{
				        		$parent_slot        = Tbl_slot::where("slot_id",$pt->sponsor_parent_id)->first();
				        		$parent_membership  = Tbl_membership::where("membership_id",$parent_slot->slot_membership)->first();
				        		$parent_total_ppv   = Tbl_unilevel_points::where("unilevel_points_slot_id",$parent_slot->slot_id)->where("unilevel_points_type","UNILEVEL_PPV")->where("unilevel_points_date_created",">=",$start_date)->where("unilevel_points_date_created","<=",$end_date)->sum("unilevel_points_amount");
				        		
				        		$parent_rank        = Tbl_stairstep_rank::where("stairstep_rank_id",$parent_slot->slot_stairstep_rank)->first();
				        		if($parent_rank)
				        		{
				        			if($pt->sponsor_level <= $parent_rank->breakaway_level)
				        			{
				        				if($parent_rank->stairstep_rank_id == $get_root_current_rank->stairstep_rank_id)
				        				{
											$breakaway_bonus = ($parent_rank->equal_bonus/100) * $total_gpv;
											Log::insert_wallet($parent_slot->slot_id,$breakaway_bonus,"BREAKAWAY_BONUS");
											Log::insert_earnings($parent_slot->slot_id,$breakaway_bonus,"BREAKAWAY_BONUS","UNILEVEL DISTRIBUTION",$slot_id,"", $pt->sponsor_level);
			        						
			        						$distri_history = Tbl_unilevel_distribute::where("distribute_full_id",$full_id)->where("slot_id",$parent_slot->slot_id)->first();
			        						if($distri_history)
			        						{
			        							$update_history["breakaway_bonus"] = $distri_history->breakaway_bonus + $breakaway_bonus;
			        							Tbl_unilevel_distribute::where("distribute_full_id",$full_id)->where("slot_id",$parent_slot->slot_id)->update($update_history);
			        						}
				        				}
				        			}
				        		}

				        	}
				        }
					}
				}

				if($total_override != 0)
				{
					$check_stairstep_rank  = Tbl_stairstep_rank::where("stairstep_rank_id",$slot->slot_stairstep_rank)->first();
					if($check_stairstep_rank)
					{
						$required_spv = $check_stairstep_rank->stairstep_rank_personal;
						if($total_spv >= $check_stairstep_rank->stairstep_rank_personal)
						{
							$status_stairstep = 1;
							$total_override_income = $total_override * $st_settings->override_multiplier;
							$override_converted    = $total_override_income;
							Log::insert_wallet($slot_id,$total_override_income,"OVERRIDE_COMMISSION");
							Log::insert_earnings($slot_id,$total_override_income,"OVERRIDE_COMMISSION","UNILEVEL DISTRIBUTION",$slot_id,"", 0);
						}
					}
				}

				$update_override["distributed"] = 1;
				Tbl_override_points::where("slot_id",$slot->slot_id)->where("override_points_date_created",">=",$start_date)->where("override_points_date_created","<=",$end_date)->where("distributed",0)->update($update_override);

				$insert_distribute["unilevel_distribute_date_start"] = $start_date;
				$insert_distribute["unilevel_distribute_end_start"]	 = $end_date;
				$insert_distribute["unilevel_personal_pv"]			 = $total_pv;
				$insert_distribute["unilevel_required_personal_pv"]	 = $required_pv;
				$insert_distribute["unilevel_group_pv"]				 = round($total_gpv, 2);
				$insert_distribute["status"]						 = $status;
				$insert_distribute["unilevel_amount"]				 = $convert_wallet;
				$insert_distribute["unilevel_multiplier"]			 = $unilevel_multiplier;
				$insert_distribute["unilevel_date_distributed"]		 = Carbon::now();
				$insert_distribute["distribute_full_id"]		     = $full_id;
				$insert_distribute["slot_id"]		                 = $slot_id;

				Tbl_unilevel_distribute::insert($insert_distribute);

				$insert_stairstep_distribute["stairstep_distribute_date_start"] = $start_date;
				$insert_stairstep_distribute["stairstep_distribute_end_start"]  = $end_date;
				$insert_stairstep_distribute["stairstep_personal_pv"]			= $total_spv;
				$insert_stairstep_distribute["stairstep_required_personal_pv"]  = $required_spv;
				$insert_stairstep_distribute["stairstep_group_pv"]			    = $total_sgpv;
				$insert_stairstep_distribute["status"]						    = $status_stairstep;
				$insert_stairstep_distribute["stairstep_override_amount"]	    = $override_converted;
				$insert_stairstep_distribute["stairstep_override_points"]	    = $stairstep_override_points;
				$insert_stairstep_distribute["stairstep_multiplier"]		    = 1;
				$insert_stairstep_distribute["stairstep_date_distributed"]	    = Carbon::now();
				$insert_stairstep_distribute["distribute_full_id"]		        = $stairstep_full_id;
				$insert_stairstep_distribute["slot_id"]		                    = $slot_id;
				$insert_stairstep_distribute["current_rank_id"]		            = $slot->slot_stairstep_rank;

				Tbl_stairstep_distribute::insert($insert_stairstep_distribute);
			}
		}

		$return["status"]             = "success";
		$return["status_code"]        = 201;
		$return["status_message"]     = "Slot Distributed";

		return $return;
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

		$response  = $this->distribute_points($slot_id,$start_date,$end_date,$full_id,$stairstep_full_id);

		return response()->json($response, 200);
	}

	public function loop($parent_id,$slot_id,$start_date,$end_date)
	{
		$parent             = Tbl_slot::where("slot_id",$parent_id)->first();
		$child_tree         = Tbl_tree_sponsor::where("sponsor_parent_id",$slot_id)->where("sponsor_level",1)->get();
		$parent_membership  = Tbl_membership::where("membership_id",$parent->slot_membership)->first();
        $start_date         = Carbon::parse($start_date);
        $end_date           = Carbon::parse($end_date);
		$settings           = Tbl_mlm_unilevel_settings::first();
		$personal_as_group  = $settings->personal_as_group;
		
		if($settings && $parent_membership)
		{
			foreach($child_tree as $tree)
			{
				$plus                 = 0;
				$proceed_to_loop      = 1;
				$slot                 = Tbl_slot::where("slot_id",$tree->sponsor_child_id)->first();
				                        // Tbl_slot::where("slot_id",$tree->sponsor_child_id)->update(["slot_personal_spv"=>0]);
				$membership           = Tbl_membership::where("membership_id",$slot->slot_membership)->first();

				if($membership)
				{
					$check = Tbl_membership_unilevel_level::where("membership_id",$parent_membership->membership_id)->where("membership_level",Self::$child_counter[$slot_id] + 1)->where("membership_entry_id",$membership->membership_id)->first();

					if($check)
					{
						$required_pv          = $membership->membership_required_pv;
						$total_pv             = Tbl_unilevel_points::where("unilevel_points_slot_id",$slot->slot_id)->where("unilevel_points_type","UNILEVEL_PPV")->where("unilevel_points_date_created",">=",$start_date)->where("unilevel_points_date_created","<=",$end_date)->where("unilevel_points_distribute",0)->sum("unilevel_points_amount");

						if($total_pv >= $required_pv)
						{
							$cause_level                               = Tbl_tree_sponsor::where("sponsor_parent_id",$parent_id)->where("sponsor_child_id",$slot_id)->first() ? Tbl_tree_sponsor::where("sponsor_parent_id",$parent_id)->where("sponsor_child_id",$slot_id)->first()->sponsor_level : 0;
							$dynamic_record["slot_id"]			       = $parent_id;
							$dynamic_record["earned_points"]	       = $total_pv * ($check->membership_percentage/100);
							$dynamic_record["cause_slot_id"]	       = $slot->slot_id;
							$dynamic_record["dynamic_level"]	       = Self::$child_counter[$slot_id] + 1;
							$dynamic_record["cause_slot_level"]	       = $cause_level;
							$dynamic_record["start_date"]		       = $start_date;
							$dynamic_record["end_date"]			       = $end_date;
							$dynamic_record["date_created"]		       = Carbon::now();
							$dynamic_record["cause_slot_ppv"]	       = $total_pv;
							$dynamic_record["cause_slot_percentage"]   = $check->membership_percentage;
							Tbl_dynamic_compression_record::insert($dynamic_record);


							Self::$gpv = Self::$gpv + ($total_pv * ($check->membership_percentage/100));
							Self::$child_level[$tree->sponsor_child_id]   = Self::$child_counter[$slot_id] + 1;
							$plus = 1;
						}
					}
					else
					{
						$proceed_to_loop = 0;
					}
				}

				if($proceed_to_loop == 1)
				{
					Self::$child_counter[$tree->sponsor_child_id]   = Self::$child_counter[$slot_id] + $plus;
					$this->loop($parent_id,$tree->sponsor_child_id,$start_date,$end_date);
				}

			}
		}
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
