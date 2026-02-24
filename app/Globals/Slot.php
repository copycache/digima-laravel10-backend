<?php
namespace App\Globals;
use App\Models\Tbl_slot;
use App\Models\Tbl_earning_log;
use App\Models\Tbl_mlm_settings;
use App\Models\Tbl_wallet_log;
use App\Models\Tbl_points_log;
use App\Models\Tbl_inventory;
use App\Models\Tbl_codes;
use App\Models\Tbl_welcome_bonus_commissions;
use App\Models\User;
use App\Models\Tbl_tree_placement;
use App\Models\Tbl_tree_sponsor;
use App\Models\Tbl_stairstep_rank;
use App\Models\Tbl_currency;
use App\Models\Tbl_wallet;
use App\Models\Tbl_mlm_plan;
use App\Models\Tbl_slot_limit;
use App\Models\Tbl_mlm_board_placement;
use App\Models\Tbl_mlm_board_slot;
use App\Models\Tbl_mlm_universal_pool_bonus_points;
use App\Models\Tbl_binary_settings;
use App\Models\Tbl_item;
use App\Models\Tbl_mlm_incentive_bonus;
use App\Models\Tbl_user_process;
use App\Models\Tbl_unilevel_points;
use App\Models\Tbl_top_recruiter;
use App\Models\Tbl_slot_code_change_logs;
use App\Models\Tbl_stairstep_settings;
use App\Models\Tbl_slot_transfer;
use App\Models\Tbl_other_settings;
use App\Models\Tbl_dealer;
use App\Models\Tbl_retailer;
use App\Models\Tbl_binary_points;
use App\Models\Tbl_membership;
use App\Models\Tbl_share_link_settings;
use App\Models\Tbl_label;
use App\Models\Tbl_product_share_link_logs;
use App\Models\Tbl_signup_bonus_logs;
use App\Models\Tbl_referral_voucher_settings;
use App\Models\Tbl_beneficiary;
use App\Models\Tbl_matrix_placement;
use App\Models\Tbl_unilevel_matrix_bonus_settings;

use App\Globals\Code;
use App\Globals\Tree;
use App\Globals\MLM;
use App\Globals\Wallet;
use App\Globals\Log;
use App\Globals\Audit_trail;
use App\Globals\User_process;
use App\Globals\Stairstep;
use App\Globals\Mlm_complan_manager;
use App\Models\Tbl_code_alias;
use DB;
use Carbon\Carbon;
use Validator;
use Crypt;
use Hash;
use Request;

class Slot
{
	public static function create_dealers_code($slot_id)
	{
		$return    = 0;
		$condition = false;
	    while($condition == false)
	    {
			$return  = Slot::generateRandomString(50);
			$check   = Tbl_dealer::where("dealer_code",$return)->first();
			$already = Tbl_dealer::where("slot_id",$slot_id)->first();
			if(!$check && !$already)
			{
				$insert["slot_id"]     = $slot_id;
				$insert["dealer_code"] = $return;
				Tbl_dealer::insert($insert);

				$condition = true;
			}
	    }

	    return $return;
	}
	public static function get()
	{
		return Tbl_slot::where("archive",0)->get();
	}
	public static function transfer($owner_id,$slot_id,$password,$transferred_to)
	{
		$owner_info         = User::where("id",$owner_id)->first();
		if($owner_info->registered_as_retailer == 0)
		{
			if(Hash::check($password, $owner_info->password))
			{
				$transferred_info = User::where("email",$transferred_to)->first();
				if($transferred_info)
				{
					if($transferred_info->registered_as_retailer == 0)
					{
						if($transferred_info->id != $owner_info->id)
						{
							$check_slot = Tbl_slot::where("slot_id",$slot_id)->where("from_bundle",1)->where("slot_owner",$owner_info->id)->first();
							if($check_slot)
							{

								$count_settings = Tbl_other_settings::where("key","slot_transfer")->first() ? Tbl_other_settings::where("key","slot_transfer")->first()->value : 0;
								$count_transfer_times = Tbl_slot_transfer::where("slot_id",$check_slot->slot_id)->count();
								if($count_settings == 0 || $count_settings > $count_transfer_times)
								{
									$count_active               = Tbl_slot::where("slot_owner",$transferred_info->id)->where("membership_inactive",0)->count();
									if($count_active == 0)
									{
										$insert["owner_id"]			= $owner_info->id;
										$insert["transferred_to"]	= $transferred_info->id;
										$insert["slot_id"]			= $check_slot->slot_id;
										$insert["date_transferred"]	= Carbon::now();
										Tbl_slot_transfer::insert($insert);

										$update["slot_owner"] = $transferred_info->id;
										Tbl_slot::where("slot_id",$check_slot->slot_id)->update($update);

										$block_inactive["slot_status"] = "blocked"; 
										Tbl_slot::where("slot_owner",$transferred_info->id)->where("membership_inactive",1)->update($block_inactive);
										$response["status"] = "success";
									}
									else
									{
										$response["status"]  = "error";
										$response["message"] = "Can only be transferred on those who does not owned a slot.";
									}
								}
								else
								{
									$response["status"] = "error";
									$response["message"] = "This slot cannot be transferred anymore";
								}
							}
							else
							{
								$response["status"] = "error";
								$response["message"] = "Invalid slot";
							}
						}
						else
						{
							$response["status"] = "error";
							$response["message"] = "Invalid email";
						}
					}
					else
					{
						$response["status"] = "error";
						$response["message"] = "You cannot transfer your slot to a retailer.";
					}
				}
				else
				{
					$response["status"] = "error";
					$response["message"] = "Invalid email";
				}
			}
			else
			{
				$response["status"] = "error";
				$response["message"] = "Incorrect password";
			}
		}
		else
		{
			$response["status"] = "error";
			$response["message"] = "Retailers cannot use this module";
		}

		 return $response;
	}
	public static function get_full_unilevel()
	{
		$start_date = Request::input("start_date") ? Carbon::parse(Request::input("start_date"))->startOfDay()  : Carbon::now()->startOfMonth();
		$end_date   = Request::input("end_date") ? Carbon::parse(Request::input("end_date"))->endOfDay() : Carbon::now()->endOfMonth();
		$filter     = Request::input("filter"); // Get the filter value from the request

		$slot = Tbl_slot::where('tbl_slot.slot_id', "!=", 1)
			->where("tbl_slot.archive", 0)
			->leftJoin(DB::raw("(SELECT unilevel_points_slot_id, 
										SUM(CASE WHEN unilevel_points_type = 'UNILEVEL_PPV' THEN unilevel_points_amount ELSE 0 END) as total_ppv,
										SUM(CASE WHEN unilevel_points_type = 'UNILEVEL_GPV' THEN unilevel_points_amount ELSE 0 END) as total_gpv
								FROM tbl_unilevel_points
								WHERE unilevel_points_date_created BETWEEN '$start_date' AND '$end_date'
								AND unilevel_points_distribute = 0
								GROUP BY unilevel_points_slot_id) as bonuses"), "bonuses.unilevel_points_slot_id", "=", "tbl_slot.slot_id")
			->leftJoin("tbl_slot as sponsor", "sponsor.slot_id", "=", "tbl_slot.slot_sponsor")
			->leftJoin("tbl_stairstep_rank as stairstep", "stairstep.stairstep_rank_id", "=", "tbl_slot.slot_stairstep_rank")
			->select("tbl_slot.*", "sponsor.slot_no as slot_sponsor_no",
					DB::raw("COALESCE(stairstep.stairstep_rank_name, '---') as current_rank"),
					DB::raw("COALESCE(bonuses.total_ppv, 0) as total_ppv"), 
					DB::raw("COALESCE(bonuses.total_gpv, 0) as total_gpv"));
					
		if ($filter !== 'all') {
			$slot->where(function($slot) {
				$slot->where(DB::raw("COALESCE(bonuses.total_ppv, 0)"), ">", 0)
					->orWhere(DB::raw("COALESCE(bonuses.total_gpv, 0)"), ">", 0);
			});
		}
		$count_slot = $slot->count();
		$slot = $slot->paginate(30);
		// $data["slot_all"] 	  = Tbl_slot::where("tbl_slot.archive",0)->get();
		$data["slot_all_sum"] = $count_slot;
		// $data["slot_all_sum"] = Tbl_slot::where("tbl_slot.archive",0)->count();
		$data["slot"] 		  = $slot;
		$data["start_date"]   = $start_date->format("Y-m-d");
		$data["end_date"] 	  = $end_date->format("Y-m-d");

		return $data;
	}
	public static function get_unilevel_list()
	{
		$start_date = Request::input("start") ? Carbon::parse(Request::input("start"))->startOfDay()  : Carbon::now()->startOfMonth();
		$end_date   = Request::input("end") ? Carbon::parse(Request::input("end"))->endOfDay() : Carbon::now()->endOfMonth();
		$filter     = Request::input("filter"); // Get the filter value from the request

		$query = Tbl_slot::where('tbl_slot.slot_id', "!=", 1)
			->where("tbl_slot.archive", 0)
			->leftJoin(DB::raw("(SELECT unilevel_points_slot_id, 
						SUM(CASE WHEN unilevel_points_type = 'UNILEVEL_PPV' THEN unilevel_points_amount ELSE 0 END) as total_ppv,
						SUM(CASE WHEN unilevel_points_type = 'UNILEVEL_GPV' THEN unilevel_points_amount ELSE 0 END) as total_gpv
				FROM tbl_unilevel_points
				WHERE unilevel_points_date_created BETWEEN '$start_date' AND '$end_date'
				AND unilevel_points_distribute = 0
				GROUP BY unilevel_points_slot_id) as bonuses"), "bonuses.unilevel_points_slot_id", "=", "tbl_slot.slot_id")
			->select("tbl_slot.*", 
					DB::raw("COALESCE(bonuses.total_ppv, 0) as total_ppv"), 
					DB::raw("COALESCE(bonuses.total_gpv, 0) as total_gpv"));
		
		// Apply filter only if it's not "all"
		if ($filter !== 'all') {
			$query->where(function($query) {
				$query->where(DB::raw("COALESCE(bonuses.total_ppv, 0)"), ">", 0)
					->orWhere(DB::raw("COALESCE(bonuses.total_gpv, 0)"), ">", 0);
			});
		}
		$data = $query->paginate(30);
		return $data;
	}
	public static function get_unplaced_slot($owner_id,$sponsor_id)
	{
		$sponsor = Tbl_slot::where("slot_id",$sponsor_id)->where("slot_owner",$owner_id)->first();
		if($sponsor)
		{
			// return Tbl_slot::where("archive",0)->where("slot_owner",$owner_id)->where("slot_placement",0)->where("membership_inactive",0)->Owner()->select("slot_no","slot_id","slot_placement","name","slot_membership")->get();
			return Tbl_slot::where("archive",0)->where("slot_sponsor",$sponsor_id)->where("slot_placement",0)->where("membership_inactive",0)->where("slot_status","active")->Owner()->select("slot_no","slot_id","slot_placement","name","slot_membership")->get();
		}
		else
		{
			return null;
		}
	}
	public static function get_placement_downline($slot_id)
	{
		return Tbl_tree_placement::where("placement_parent_id",$slot_id)->child()->get();
	}
	public static function get_unplaced_downline_slot($owner_id,$sponsor_id)
	{
		$sponsor = Tbl_slot::where("slot_id",$sponsor_id)->where("slot_owner",$owner_id)->first();
		if($sponsor)
		{
			return Tbl_slot::where("archive",0)->where('slot_status', 'active')->where("slot_sponsor",$sponsor_id)->where("slot_placement",0)->where("membership_inactive",0)->Owner()->select("slot_no","slot_id","slot_placement","name","slot_membership")->get();
			// return Tbl_slot::where("archive",0)->where("slot_owner",$owner_id)->where("slot_placement",0)->where("membership_inactive",0)->Owner()->select("slot_no","slot_id","slot_placement","name","slot_membership")->get();
		}
		else
		{
			return null;
		}
	}
	public static function get_full($filter, $limit = null)
	{
		$user_slot_owner  	= Tbl_slot::where('slot_no','root')->first()->slot_owner;
		$active_user_id		= Request::user()->id;

		if($user_slot_owner == $active_user_id)
		{
			$query 			= Tbl_slot::where("tbl_slot.archive",0);
		}
		else
		{
			$query 			= Tbl_slot::where("tbl_slot.archive",0)->where('tbl_slot.slot_owner','!=',$user_slot_owner);
		}

		$query 			= $query->leftJoin("users","users.id","=","tbl_slot.slot_owner")
					   ->leftJoin("tbl_slot as sponsor","sponsor.slot_id","=","tbl_slot.slot_sponsor")
					   ->leftJoin("tbl_slot as placement","placement.slot_id","=","tbl_slot.slot_placement")
					   ->leftJoin("tbl_slot as matrix_placement","matrix_placement.slot_id","=","tbl_slot.matrix_sponsor")
					   ->leftJoin("tbl_membership","tbl_membership.membership_id","=","tbl_slot.slot_membership")
					   ->where('users.type' , '!=', 'stockist')
					   ->select("tbl_slot.*","users.*","tbl_membership.*","placement.slot_no as slot_placement_no","matrix_placement.slot_no as matrix_sponsor","tbl_membership.*","sponsor.slot_no as slot_sponsor_no",
					   			 DB::raw("DATE_FORMAT(tbl_slot.slot_date_created, '%m/%d/%Y (%h:%i %p)') as slot_date_created"),
					   	         DB::raw("DATE_FORMAT(tbl_slot.slot_date_placed, '%m/%d/%Y (%h:%i %p)') as slot_date_placed_new"));

		if($filter['kyc_status'] != 'all')
		{
			$query->where("users.verified",$filter["kyc_status"]);
		}
		if($filter['membership'] != 'all')
		{
			$query->where('membership_name', $filter['membership']);
		}

		if($filter['type'] != 'all')
		{
			$query->where('tbl_slot.slot_type', $filter['type']);
		}

		if($filter['ranking'] != 'all')
		{
			$stairstep_rank 	= Tbl_stairstep_rank::where('stairstep_rank_name', $filter['ranking'])->first();
			$query->where('tbl_slot.slot_stairstep_rank', $stairstep_rank->stairstep_rank_id);
		}

		if($filter['search'] != '' || $filter['search'] != null)
		{
			if($user_slot_owner == $active_user_id)
			{
				$query->where(function($query) use ($filter) {
					$query->where("tbl_slot.slot_no", "like", "%" . $filter["search"] . "%")
						  ->orWhere("users.name", "like", "%" . $filter["search"] . "%")
						  ->orWhere("tbl_slot.slot_id_number", "like", "%" . $filter["search"] . "%");
				});
			}
			else
			{
				$query->where('tbl_slot.slot_owner', '!=', $user_slot_owner)
					->where(function($query) use ($filter) {
						$query->where("tbl_slot.slot_no", "like", "%" . $filter["search"] . "%")
								->orWhere("users.name", "like", "%" . $filter["search"] . "%")
								->orWhere("tbl_slot.slot_id_number", "like", "%" . $filter["search"] . "%");
					});
			}
		}

		if ($limit)
		{
			$data = $query->paginate($limit);
		}
		else
		{
			$data = $query->get();
		}

		$currency_id = Tbl_currency::where("currency_default",1)->first() ? Tbl_currency::where("currency_default",1)->first()->currency_id : 0;

		foreach($data as $key => $d)
		{
			$data[$key]->wallet  		 = Tbl_wallet::where("currency_id",$currency_id)->where("slot_id",$d->slot_id)->first() ? Tbl_wallet::where("currency_id",$currency_id)->where("slot_id",$d->slot_id)->first()->wallet_amount : 0;
			$data[$key]->cashin  		 = Tbl_wallet::where("currency_id", 15)->where("slot_id",$d->slot_id)->first() ? Tbl_wallet::where("currency_id",15)->where("slot_id",$d->slot_id)->first()->wallet_amount : 0;
			$data[$key]->voucher_wallet  = Tbl_wallet::where("currency_id",13)->where("slot_id",$d->slot_id)->first()->wallet_amount ?? 0;
			// $data[$key]->earning = Tbl_earning_log::where("earning_log_currency_id",$currency_id)->where("earning_log_slot_id",$d->slot_id)->sum("earning_log_amount");
			$data[$key]->earning    = Tbl_earning_log::where("earning_log_slot_id",$d->slot_id)
									->where(function ($query)
									{
										$query->where('earning_log_plan_type','!=','SIGN UP BONUS')
											->where('earning_log_plan_type','!=','REFERRAL VOUCHER');
									})->sum("earning_log_amount");

			// $data[$key]->voucher    = Tbl_earning_log::where("earning_log_slot_id",$d->slot_id)
			// 						->where(function ($query)
			// 						{
			// 							$query->where('earning_log_plan_type','SIGN UP BONUS')
			// 								->orWhere('earning_log_plan_type','REFERRAL VOUCHER');
			// 						})->sum("earning_log_amount");
			
		}
		return $data;
	}
	public static function get_unplaced($search = null)
	{
		$query = Tbl_slot::where("tbl_slot.archive",0)
					   ->leftJoin("users","users.id","=","tbl_slot.slot_owner")
					   ->leftJoin("tbl_slot as sponsor","sponsor.slot_id","=","tbl_slot.slot_sponsor")
					   ->leftJoin("tbl_membership","tbl_membership.membership_id","=","tbl_slot.slot_membership")
					   ->where("tbl_slot.slot_placement","0")
					   ->where("tbl_slot.slot_sponsor","!=","0")
					   ->select("tbl_slot.*","users.*","tbl_membership.*","sponsor.slot_no as slot_sponsor_no",
					   			 DB::raw("DATE_FORMAT(tbl_slot.slot_date_created, '%m/%d/%Y (%h:%i %p)') as slot_date_created"),
									DB::raw("DATE_FORMAT(tbl_slot.slot_date_placed, '%m/%d/%Y (%h:%i %p)') as slot_date_placed_new"));

		if(isset($search))
		{
			$query->where('tbl_slot.slot_no', "like", "%". $search . "%");
		}
		$return = $query->limit(10)->get();
		// dd($return);
		return $return;
	}

	public static function create_slot($data,$custom_slot_no = null)
	{
		$return["i"]        	  = 0;
		$return["status_message"] = [];
		$data['store_name']    = Tbl_slot::where('slot_owner', $data["slot_owner"])->value('slot_no');

		if($data['slot_owner'] === null)
		{
			$return["status_message"][$return["i"]] = "Please input slot owner..";
			$return["status"]         	  = "error";
			$return["status_code"]    	  = 400;
		}
		else
		{
			/* PREVENTS MULTIPLE PROCESS AT ONE TIME */
			$process_returned = User_process::check($data["slot_owner"]);
			if( User_process::check($data["slot_owner"]) == 1)
			{
				$return["status_message"][$return["i"]] = "Please try again...";
				$return["i"]++;
			}

			/* CUSTOMIZE VALIDATION STARTS HERE */
			$return = Slot_create::validate_membership_code($return,$data["code"],$data["pin"], ( !isset($data['import']) ? 1 : 0 ) );
			$return = Slot_create::validate_slot_limit($return,$data["slot_owner"],$data["code"],$data["pin"]);
			$return = Slot_create::validate_required($return,$data["slot_owner"],$data["slot_sponsor"],$not_import = 1);
			$return = Slot_create::validate_slot_no($return,$custom_slot_no);
			$return = Slot_create::validate_slot_if_retailer($return,$data["slot_owner"]);

			/* INACTIVE CHECK */
			$inactive_data	      = Slot_create::check_inactive($return,$data["slot_owner"]);
			$proceed_to_inactive  = $inactive_data["proceed_to_inactive"];
			$check_inactive       = $inactive_data["check_inactive"];
			$return 			  = Slot_create::validate_inactive($return,$data["slot_sponsor"],$check_inactive,$proceed_to_inactive);


			$i = $return["i"];

			if($i == 0)
			{
				$slot_sponsor     = Tbl_slot::where("slot_no",$data["slot_sponsor"])->first();
				$from_admin       = array_key_exists("from_admin",$data) ? 1 : 0;
				$check_code       = Code::use_membership_code($data["code"],$data["pin"],$data["slot_owner"],$from_admin, isset($data['slot_id']) ? $data['slot_id'] : null, $slot_sponsor->slot_id);

				if($check_code["status"] == "unused")
				{
					$repetition = $check_code['slot_quantity'];
					$bundle     = $repetition > 1 ? 1 : 0;

					for($reps = 1; $reps <= $repetition; $reps++)
					{
						if($proceed_to_inactive == 0)
						{
							$slot_sponsor 				       = Tbl_slot::where("slot_no",$data["slot_sponsor"])->first();
							$membership_id                     = Code::get_membership($data["code"],$data["pin"]);
							$user                              = User::where("id",$data["slot_owner"])->first();

							$check_default_added_days          = Tbl_other_settings::where("key","default_added_days")->first() ? Tbl_other_settings::where("key","default_added_days")->first()->value : 0;
							if($check_default_added_days == 0)
							{
								$added_days = Tbl_codes::where("code_activation",$data["code"])->where("code_pin",$data["pin"])->inventory()->inventoryitem()->CheckIfArchived()->where("item_type","membership_kit")->first()->added_days;
							}
							else 
							{
								$added_days	= $check_default_added_days;
							}
							$slot_count_id					   = Tbl_slot::where('slot_owner',$user->id)->orderBy('slot_count_id','DESC')->pluck('slot_count_id')->first()+1;
							$custom_slot_no					   = Tbl_slot::where('slot_owner',$user->id)->pluck('slot_no')->first() ?? null;
							$custom_slot_no					   = $custom_slot_no == null ? null : $custom_slot_no."-".$slot_count_id;
							$maintain_date                     = Carbon::now()->addDays($added_days);
							$insert['slot_id_number'] = Slot::generate_slot_id_number();
							$insert["slot_owner"]              = $user->id;
							$insert["slot_sponsor"]            = $slot_sponsor->slot_id;
							$insert["slot_membership"]         = $membership_id;
							$insert['slot_no']				   = $custom_slot_no == null ? Slot::name_based_on_settings($user->first_name) : $custom_slot_no;
							$insert["slot_position"]           = "";
							$insert["slot_type"]           	   = "PS";
							$insert["slot_used_code"]          = $check_code["code_id"];
							$insert["slot_date_created"]       = Carbon::now();
							$insert["slot_stairstep_rank"]     = Slot::get_default_stairstep();
							$insert["from_bundle"]			   = $bundle;
							$insert["maintained_until_date"]   = $maintain_date;
							$insert["slot_count_id"] 		   = $slot_count_id;
							$insert["store_name"]   		   = $data['store_name'];
							$check_plan_welcome_bonus = Tbl_mlm_plan::where('mlm_plan_code','=','WELCOME_BONUS')->first() ? Tbl_mlm_plan::where('mlm_plan_code','=','WELCOME_BONUS')->first()->mlm_plan_enable : 0;
							$check_commission = Tbl_welcome_bonus_commissions::where('membership_id', $membership_id)->first()->commission;
							if($check_plan_welcome_bonus == 1 && $check_commission) {
								$insert["welcome_bonus_notif"] = 1;
							}
							$new_id   						   = Tbl_slot::insertGetId($insert);
							$new_slot 						   = Tbl_slot::where("slot_id",$new_id)->first();



							Tree::insert_tree_sponsor($new_slot, $new_slot, 1);
							MLM::create_entry($new_id);

							Slot_create::new_slot_initial_data($new_id,$slot_sponsor,$membership_id,$data["slot_owner"]);

							$return["status"]             = "success";
							$return["status_code"]        = 201;
							$return["status_message"]     = "Slot Created";
							$return["status_data_id"]     = $new_slot->slot_no;
							$return["status_data_id_inc"] = $new_slot->slot_id;
							$return["binary_placement_enable"] = Tbl_slot::where("slot_id", $new_slot->slot_id)->JoinMembership()->first()->binary_placement_enable;
							$return["sponsor_binary_placement_enable"] = Tbl_slot::where("slot_id", $new_slot->slot_sponsor)->JoinMembership()->first()->binary_placement_enable;

							Wallet::generateSlotWalletAddress($new_id);

							//audit trail
							isset($data['user']) ? Audit_trail::audit(null,serialize($new_slot),$data['user']['id'],'Create Slot') : null;
						}
						else if($proceed_to_inactive == 1)
						{
							$slot_sponsor 				       = Tbl_slot::where("slot_no",$data["slot_sponsor"])->first();
							$membership_id                     = Code::get_membership($data["code"],$data["pin"]);
							$user                              = User::where("id",$data["slot_owner"])->first();
							$check_default_added_days          = Tbl_other_settings::where("key","default_added_days")->first() ? Tbl_other_settings::where("key","default_added_days")->first()->value : 0;
							if($check_default_added_days == 0)
							{
								$added_days = Tbl_codes::where("code_activation",$data["code"])->where("code_pin",$data["pin"])->inventory()->inventoryitem()->CheckIfArchived()->where("item_type","membership_kit")->first()->added_days;
							}
							else 
							{
								$added_days	= $check_default_added_days;
							}
							// $added_days  					   = Tbl_codes::where("code_activation",$data["code"])->where("code_pin",$data["pin"])->inventory()->inventoryitem()->CheckIfArchived()->where("item_type","membership_kit")->first()->added_days;
							$maintain_date                     = Carbon::now()->addDays($added_days);
							$update["slot_sponsor"]            = $slot_sponsor->slot_id;
							$update["slot_membership"]         = $membership_id;
							$update["slot_position"]           = "";
							$update["slot_type"]           	   = "PS";
							$update["slot_used_code"]          = $check_code["code_id"];
							$update["slot_date_created"]       = Carbon::now();
							$update["membership_inactive"]     = 0;
							$update["slot_stairstep_rank"]     = Slot::get_default_stairstep();
							$update["from_bundle"]			   = $bundle;
							$update["maintained_until_date"]   = $maintain_date;
							$update["slot_count_id"] 		   = Tbl_slot::where('slot_id',$check_inactive->slot_id)->orderBy('slot_count_id','DESC')->pluck('slot_count_id')->first()+1;
							$update["store_name"]   		   = $data['store_name'];
							$check_plan_welcome_bonus = Tbl_mlm_plan::where('mlm_plan_code','=','WELCOME_BONUS')->first() ? Tbl_mlm_plan::where('mlm_plan_code','=','WELCOME_BONUS')->first()->mlm_plan_enable : 0;
							$check_commission = Tbl_welcome_bonus_commissions::where('membership_id', $membership_id)->first()->commission;
							if($check_plan_welcome_bonus == 1 && $check_commission) {
								$update["welcome_bonus_notif"] = 1;
							}

							Tbl_slot::where("slot_id",$check_inactive->slot_id)->update($update);
							$new_slot = Tbl_slot::JoinMembership()->where("slot_id",$check_inactive->slot_id)->first();
							Tree::insert_tree_sponsor($new_slot, $new_slot, 1);
							MLM::create_entry($check_inactive->slot_id);

							Slot_create::new_slot_initial_data($new_slot->slot_id,$slot_sponsor,$membership_id,$data["slot_owner"]);

							$return["status"]             = "success";
							$return["status_code"]        = 201;
							$return["status_message"]     = "Slot Activated";
							$return["status_data_id"]     = $new_slot->slot_no;
							$return["status_data_id_inc"] = $new_slot->slot_id;
							$return["binary_placement_enable"] = Tbl_slot::where("slot_id", $new_slot->slot_id)->JoinMembership()->first()->binary_placement_enable;
							$return["sponsor_binary_placement_enable"] = Tbl_slot::where("slot_id", $new_slot->slot_sponsor)->JoinMembership()->first()->binary_placement_enable;

							/*audit trail*/
							isset($data['user']) ? Audit_trail::audit(null,serialize($new_slot),$data['user']['id'],'Create Slot') : null;
							$proceed_to_inactive = $repetition > 1 ? 0 : 1;
						}


			            /* VORTEX */
			            $vortex_plan = Tbl_mlm_plan::where("mlm_plan_code","VORTEX_PLAN")->where("mlm_plan_enable",1)->first();
			            if($vortex_plan)
			            {
			                $membership_income = Tbl_membership::where("membership_id",$new_slot->slot_membership)->first();
			                $vortex_token      = $membership_income ? $membership_income->vortex_registered_token : 0;

			                if($vortex_token != 0)
			                {
			                    Vortex::insert_token($new_slot->slot_id,$new_slot->slot_id,"SLOT_CREATION",$vortex_token);
			                }
			            }
					}
				}
				else
				{
					$return["status_message"][$i] = isset($check_code["status_error"]) ? $check_code["status_error"] : "Invalid Code.";
					$return["status"]			  = 'error';
					$return["status_code"]        = 400;
				}
			}
			else
			{
				$return["status"]         = "error";
				$return["status_code"]    = 400;
			}
		}

        return $return;
	}

	public static function place_imported_slots($data)
	{
		$check_if_already_placed = Tbl_slot::where('slot_no', $data['slot_code'])->first();
		if($check_if_already_placed->slot_placement == 0)
		{
			$check_if_placement_is_placed = Tbl_slot::where('slot_no', $data['placement'])->first();
			if($check_if_placement_is_placed)
			{
				if($check_if_placement_is_placed->slot_placement > 0)
				{
					$update["slot_placement"]    = $check_if_placement_is_placed->slot_id;
					$update["slot_position"]     = strtoupper($data['position']);
					$update["slot_type"]         = "PS";
					$update["slot_date_placed"]  = Carbon::now();
					Tbl_slot::where("slot_no",$data['slot_code'])->update($update);
					$new_slot = Tbl_slot::where("slot_no",$data['slot_code'])->first();
					Tree::insert_tree_placement($new_slot, $new_slot, 1);
					MLM::placement_entry($new_slot->slot_id);

				}
				elseif($check_if_placement_is_placed->slot_id == 1)
				{

					$update["slot_placement"]    = $check_if_placement_is_placed->slot_id;
					$update["slot_position"]     = strtoupper($data['position']);
					$update["slot_type"]         = "PS";
					$update["slot_date_placed"]  = Carbon::now();
					Tbl_slot::where("slot_no",$data['slot_code'])->update($update);
					$new_slot = Tbl_slot::where("slot_no",$data['slot_code'])->first();
					Tree::insert_tree_placement($new_slot, $new_slot, 1);
					MLM::placement_entry($new_slot->slot_id);
				}
			}
		}
	}

	public static function import_members($data)
	{
		$register["register_platform"]		=  'system';
		if($data['email'])
		{
			$register["email"]    				= str_replace(" ","",$data['email']);
		}
		else
		{
			$register["email"]			=	$data['slot_no']."@email.com";
		}

		$register["first_name"]     		= $data['first_name'];
		if(isset($data['middle_name']))
		{
			$register["middle_name"] = $data['middle_name'];
		}
		else
		{
			$register["middle_name"] = '';
		}
		$register["last_name"]    			= $data['last_name'];
		$register["password"] 				= $data['password'];
		$register["contact"]                = $data["contact_number"];
		$register["country_id"]	    		= 1;

		$member_result = Member::add_member($register);

		if(isset($data['slot_no']))
		{
			$custom_slot_no = $data['slot_no'];
		}
		else
		{
			$custom_slot_no = null;
		}

		if($member_result['status_code'] == 201)
		{
			$data['slot_owner'] = $member_result['status_data_id'];
			$status = Self::create_blank_slot($data['slot_owner'],0,0,0,0,$custom_slot_no);
			return $status;
			
			
		}
		elseif($member_result['status_message'][0] == "The email has already been taken.")
		{
			$data['slot_owner'] = $member_result['status_data_id'];
			$owner = User::where('email',$register['email'])->value('id');
			$status = Self::create_blank_slot($data['slot_owner'],0,0,0,0,$custom_slot_no);
			return $status;
		}
		else
		{
			dd($member_result);
		}
	}
	public static function import_member_slot($data)
	{
		$register["register_platform"]		=  'system';
		if($data['email'])
		{
			$register["email"]    				= str_replace(" ","",$data['email']);
		}
		else
		{
			$register["email"]			=	$data['slot_code']."@email.com";
		}

		$register["first_name"]     		= $data['first_name'];
		if(isset($data['middle_name']))
		{
			$register["middle_name"] = $data['middle_name'];
		}
		else
		{
			$register["middle_name"] = '';
		}
		$register["last_name"]    			= $data['last_name'];
		$register["password"] 				= $data['password'];
		$register["contact"]                = $data["contact_number"];
		$register["country_id"]	    		= 1;

		$member_result = Member::add_member($register);

		if($member_result['status_code'] == 201)
		{
			$data['slot_owner'] = $member_result['status_data_id'];
			$data['slot_sponsor'] = $data['sponsor'];
			$inventory = Tbl_inventory::where('inventory_branch_id', 1)->where('inventory_item_id', $data['package_number'])->value('inventory_id');
			$code 	   = Tbl_codes::where('code_inventory_id', $inventory)->CheckIfSold(0)->CheckIfUsed(0)->CheckIfArchived(0)->first();
			if($code)
			{
				$data['code'] = $code->code_activation;
				$data['pin']  = $code->code_pin;
				$data['import'] = 1;
				$status = Self::create_slot($data,$data['slot_code']);
				return $status;
			}
			else
			{
				dd($inventory,$code,$data);
			}
		}
		elseif($member_result['status_message'][0] == "The email has already been taken.")
		{
			$owner = User::where('email',$register['email'])->value('id');
			$data['slot_owner'] = $owner;
			$data['slot_sponsor'] = $data['sponsor'];
			$inventory = Tbl_inventory::where('inventory_branch_id', 1)->where('inventory_item_id', $data['package_number'])->value('inventory_id');
			$code 	   = Tbl_codes::where('code_inventory_id', $inventory)->CheckIfSold(0)->CheckIfUsed(0)->CheckIfArchived(0)->first();
			if($code)
			{
				$data['code'] = $code->code_activation;
				$data['pin']  = $code->code_pin;
				$data['import'] = 1;
				$status = Self::create_slot($data,$data['slot_code']);
				return $status;
			}
			else
			{
				dd($inventory,$code,$data);
			}
		}
		else
		{
			dd($member_result);
		}
	}

	public static function import_slots($data,$import_settings)
	{
		$return['status'] 		    = "Processing";
		$return['status_message'] 	= "";

		$return['process_returned'] = "Success";
		$return['process_message']  = null;
		$i = 0;
		$yes_placement              = 1;
		$yes_sponsor                = 1;
		if(isset($data['email']))
		{
			$email = $data['email'];
		}
		else
		{
			$email = $data['slot_no'].'@email.com';
		}

		$slot_no   = $data["slot_no"];
		$sponsor   = $data["sponsor"];
		$data['slot_sponsor']   = $data["sponsor"];
		$placement = $data["placement"];
		$position  = strtoUpper($data["position"]);

		$slot      = Tbl_slot::where("slot_no",$slot_no)->first();
		$sponsor   = Tbl_slot::where("slot_no",$sponsor)->first();
		$placement = Tbl_slot::where("slot_no",$placement)->first();

		if(!$sponsor && $import_settings["rematrix"] == 1)
		{
			$return['process_message'][$i] = "Sponsor Slot not found";
			$i++;

			$yes_placement = 0;
			$yes_sponsor   = 0;
		}

		if(!$placement && $import_settings["rematrix"] == 1)
		{
			$return['process_message'][$i] = "Placement Slot not found";
			$i++;

			$yes_placement = 0;
		}
		else if($import_settings["rematrix"] == 1 && $slot)
		{
			$check_placement = Tbl_slot::where("slot_id","!=",$slot->slot_id)->where("slot_id","<",$slot->slot_id)->where("slot_placement",$placement->slot_id)->where("slot_position",$position)->first();
			if($check_placement)
			{
				$return['process_message'][$i] = "Placement already occupied by other slot";
				$i++;

				$yes_placement = 0;
			}
		}

		if(strtoUpper($position) != "LEFT" && strtoUpper($position) != "RIGHT" && $import_settings["rematrix"] == 1)
		{
			$return['process_message'][$i] = "Invalid Positions";
			$i++;

			$yes_placement = 0;
		}

		if($import_settings["create_if_not_exist"] == 0 && !$slot)
		{
			$return['process_message'][$i] = "Imported Slot not found";
			$i++;
		}

		if($slot)
		{
			if($import_settings["rematrix"] == 1 && $yes_sponsor == 1)
			{
				$update_slot["slot_sponsor"]   = $sponsor->slot_id;
				Tbl_slot::where("slot_id",$slot->slot_id)->update($update_slot);
				Tbl_tree_sponsor::where("sponsor_child_id",$slot->slot_id)->delete();
				Tree::insert_tree_sponsor($slot, $slot, 1);
			}

			if($import_settings["rematrix"] == 1 && $yes_sponsor == 1 && $yes_placement == 1)
			{
				$update_slot = null;
				$update_slot["slot_placement"] = $placement->slot_id; 
				$update_slot["slot_position"]  = $position;
				Tbl_slot::where("slot_id",$slot->slot_id)->update($update_slot);
				Tbl_tree_placement::where("placement_child_id",$slot->slot_id)->delete();

				Tree::insert_tree_placement($slot, $slot, 1);
			}

			if($import_settings["reset_wallet"] == 1)
			{
				DB::table("tbl_slot")->where("slot_id",$slot->slot_id)->update(["slot_wallet"=>0]);
				DB::table("tbl_slot")->where("slot_id",$slot->slot_id)->update(["slot_total_earnings"=>0]);
				DB::table("tbl_slot")->where("slot_id",$slot->slot_id)->update(["slot_total_payout"=>0]);
				DB::table("tbl_slot")->where("slot_id",$slot->slot_id)->update(["slot_pairs_per_day_date"=>""]);
				DB::table("tbl_slot")->where("slot_id",$slot->slot_id)->update(["slot_pairs_per_day"=>0]);
				DB::table("tbl_slot")->where("slot_id",$slot->slot_id)->update(["bonus_no"=>0]);
				DB::table("tbl_slot")->where("slot_id",$slot->slot_id)->update(["meridiem" => ""]);

				Tbl_wallet::where("slot_id",$slot->slot_id)->update(["wallet_amount"=>0]);
				Tbl_wallet_log::where("wallet_log_slot_id",$slot->slot_id)->delete();
				Tbl_earning_log::where("earning_log_slot_id",$slot->slot_id)->delete();

			}

			if($import_settings["reset_points"] == 1)
			{
				DB::table("tbl_slot")->where("slot_id",$slot->slot_id)->update(["slot_left_points"=>0]);
				DB::table("tbl_slot")->where("slot_id",$slot->slot_id)->update(["slot_right_points"=>0]);
				DB::table("tbl_slot")->where("slot_id",$slot->slot_id)->update(["slot_personal_spv"=>0]);
				DB::table("tbl_slot")->where("slot_id",$slot->slot_id)->update(["slot_group_spv"=>0]);
				Tbl_binary_points::where("binary_points_slot_id",$slot->slot_id)->delete();
			}


			if($import_settings["reentry"] == 1)
			{
				if($yes_sponsor == 1)
				{
					MLM::create_entry($slot->slot_id);
				}

				if($yes_placement == 1)
				{
					MLM::placement_entry($slot->slot_id);
				}
			}
			if($import_settings["create_if_not_exist"] == 1)
			{
				$return['process_message'][$i] = "Slot number already exist";
				$i++;
			}
		}
		else if($i == 0)
		{
			if($import_settings["create_if_not_exist"] == 1 && $i == 0)
			{
				$code = Tbl_codes::inventory()->inventoryitem()->CheckIfArchived()->where('code_used', 0)->where('code_sold', 0)->where("item_type","membership_kit")->first();
				if($code)
				{
					$register["register_platform"]		= 'system';
					$register["email"]     				= $email;
					$register["first_name"]     		= $data['first_name'];
					$register["middle_name"]			= isset($data['middle_name']) ? $data['middle_name'] : '';
					$register["last_name"]    			= $data['last_name'];
					$register["password"] 				= $data['password'];
					$register["contact"]                = $data["contact_number"];
					$register["country_id"]	    		= 1;

					$member_result = Member::add_member($register);

					if($member_result['status'] == 'success')
					{
						$data['code'] = $code->code_activation;
						$data['pin']  = $code->code_pin;
						$data['import'] = 1;
						$data['slot_owner'] = $member_result['status_data_id'];
						$return = Self::create_slot($data, $data['slot_no']);
						$return['process_returned'] = $return['status'];
						$return['process_message'][$i] = $return['status_message'];
					}
					else if($member_result['status_message'][0] == "The email has already been taken.")
					{
						$owner = User::where('email',$register['email'])->value('id');
						$data['code'] = $code->code_activation;
						$data['pin']  = $code->code_pin;
						$data['import'] = 1;
						$data['slot_owner'] = $owner;
						$return = Self::create_slot($data, $data['slot_no']);
						$return['process_returned'] = $return['status'];
						$return['process_message'][$i] = $return['status_message'];
					}
					else
					{
						$return['process_message'][$i] = $member_result['status_message'][0];
						$i++;
					}
				}
				else
				{
					$return['process_message'][$i] = "No Inventory";
					$i++;
				}
			}
		}


		if($i != 0)
		{
			$return['process_returned'] = "Error";
		}

		return $return;
	}


	// public static function import_custom_member($data, $count)
	// {
	// 	$register["register_platform"]		=  'system';
	// 	$register["email"]    				= $data['email'];
	// 	$register['name']					= $data['full_name'];
	// 	$register["password"] 				= 12345678;
	// 	$register["contact"]                = $data["number"];
	// 	$register["country_id"]	    		= 1;

	// 	$parts = explode(" ", $data['full_name']);
	// 	$register['last_name'] = array_pop($parts);
	// 	$register['first_name'] = implode(" ", $parts);


	// 	$member_result = Member::add_member($register);
	// 	if($member_result['status_code'] == 201)
	// 	{
	// 		$data['slot_owner'] = $member_result['status_data_id'];
	// 		if($data['sponsor'] == "ADMIN")
	// 		{
	// 			$sponsor_slot = Tbl_slot::where('slot_no', 'root')->first();
	// 		}
	// 		else
	// 		{
	// 			$sponsor = User::where("users.name", "like", '%'.str_replace(' ','%',$data['sponsor']). '%')->first();
	// 			if($sponsor)
	// 			{
	// 				$sponsor_check = Tbl_slot::where('slot_owner', $sponsor->id)->first();
	// 				if($sponsor_check)
	// 				{
	// 					$sponsor_slot = Tbl_slot::where('slot_owner', $sponsor->id)->first();
	// 				}
	// 				else
	// 				{
	// 					$sponsor_slot = Tbl_slot::where('slot_no', 'root')->first();
	// 				}
	// 			}
	// 			else
	// 			{
	// 				$sponsor_slot = Tbl_slot::where('slot_no', 'root')->first();
	// 			}

	// 		}
	// 		$sponsor = $sponsor_slot->slot_no;
	// 		$data['slot_sponsor'] = $sponsor;

	// 		$code = Tbl_codes::inventory()->inventoryitem()->CheckIfArchived()->where('code_used', 0)->where('code_sold', 0)->where("item_type","membership_kit")->first();
	// 		if($code)
	// 		{
	// 			$data['code'] = $code->code_activation;
	// 			$data['pin']  = $code->code_pin;
	// 			$data['import'] = 1;
	// 			$return = Self::create_slot($data);
	// 		}
	// 		else
	// 		{
	// 			$return['message'] = "no inventory";
	// 		}
	// 	}
	// 	else
	// 	{
	// 		$return = $member_result;
	// 	}

	// 	return $return;
	// }
	public static function get_default_stairstep()
	{
		$rank = 0;
		$get_first_rank = Tbl_stairstep_rank::where("archive",0)
											->where("stairstep_rank_personal",0)
											->where("stairstep_rank_group",0)
											->where("stairstep_rank_personal_all",0)
											->where("stairstep_rank_group_all",0)
											->where("stairstep_rank_level",1)
											->first();
	    if($get_first_rank)
	    {
	    	$rank = $get_first_rank->stairstep_rank_id;
	    }

		return $rank;
	}

	public static function create_blank_slot($owner,$sponsor = 0,$slot_link = null, $stockist_slot = 0,$dealer_code = 0,$custom_slot_no = null,$item_id = null)
	{
		$user = User::where("id",$owner)->first();
		$insert['slot_id_number'] = Slot::generate_slot_id_number();
		$insert["slot_owner"]              = $user->id;
		$insert["slot_sponsor"]            = $slot_link == "referral" ? $sponsor : 0;
		$insert["slot_sponsor_member"]     = $slot_link == "referral" ? $sponsor : 0;
		$insert["slot_sponsor_product"]    = $slot_link == "product" ? $sponsor : 0;
		$insert["slot_membership"]         = 0;
		if($custom_slot_no)
		{
			$insert["slot_no"]				= $custom_slot_no;
		}
		else
		{
			$insert["slot_no"]                 = Slot::name_based_on_settings($user->first_name);
		}
		$insert["slot_position"]           = "";
		$insert["slot_type"]           	   = $stockist_slot == 0 ? "--" : "SS";
		$insert["slot_used_code"]          = 0;
		$insert["slot_date_created"]       = Carbon::now();
		$insert["membership_inactive"]     = 1;
		$insert["slot_count_id"]  		   = 0;

		if($dealer_code != "0")
		{
			$dealer = Tbl_dealer::where("dealer_code",$dealer_code)->first();
			if($dealer)
			{
				$insert["is_retailer"] = 1;
			}
		}

		$new_id   						  = Tbl_slot::insertGetId($insert);

		if($dealer_code)
		{
			$dealer = Tbl_dealer::where("dealer_code",$dealer_code)->first();

			if($dealer)
			{
				$insert_retailer["slot_id"]			 = $new_id;
				$insert_retailer["dealer_slot_id"]	 = $dealer->slot_id;
				$insert_retailer["retailer_created"] = Carbon::now();

				Tbl_retailer::insert($insert_retailer);
			}
		}
		if($slot_link == "referral" || $slot_link == "product_share_link")
		{ 
			$check_enable = Tbl_mlm_plan::where('mlm_plan_code','SHARE_LINK')->first() ? Tbl_mlm_plan::where('mlm_plan_code','SHARE_LINK')->first()->mlm_plan_enable : 0;
			if($check_enable == 1)
			{
				if($sponsor != 0)
				{
					$share_link = Tbl_share_link_settings::first();
					if($share_link)
					{
						$date_today = Carbon::today();
						$check_count = Tbl_earning_log::where('earning_log_slot_id',$sponsor)->where("earning_log_plan_type","SHARE LINK")->whereDate('earning_log_date_created',$date_today)->count();
						if($check_count < $share_link->share_link_maximum_register_per_day)
						{
							$check_sum = Tbl_earning_log::where('earning_log_slot_id',$sponsor)->where("earning_log_plan_type","SHARE LINK")->sum("earning_log_amount");
							if($check_sum < $share_link->share_link_maximum_income)
							{
								$total       = $check_sum + $share_link->share_link_income_per_registration;

								if($total <= $share_link->share_link_maximum_income)
								{
									$share_earning =  $share_link->share_link_income_per_registration;
									$details = '';
									Log::insert_earnings($sponsor,$share_earning,"SHARE_LINK","Member Registration",$new_id,$details);
									Log::insert_wallet($sponsor,$share_earning,"SHARE_LINK");
								}
								else 
								{
									$diff    = $total - $share_link->share_link_maximum_income;
									$share_earning =  $share_link->share_link_income_per_registration - $diff;
									$details = '';
									Log::insert_earnings($sponsor,$share_earning,"SHARE_LINK","Member Registration",$new_id,$details);
									Log::insert_wallet($sponsor,$share_earning,"SHARE_LINK");
								}
								
							}
						}
					}
				}
			}

			$share_link_status = Tbl_mlm_plan::where('mlm_plan_code','SHARE_LINK_V2')->first()->mlm_plan_enable ?? 0;

			if($share_link_status == 1)
			{
				if($sponsor != 0)
				{
					$slot_info  	= Tbl_slot::where('slot_id',$sponsor)->first()->slot_membership;
					$share_settings = Tbl_membership::where('membership_id',$slot_info)->first();
					
					if($share_settings)
					{
						$date_today 		= Carbon::today();
						$share_link_label 	= Tbl_label::where('plan_code','SHARE_LINK_V2')->first()->plan_name;
						$check_count 		= Tbl_earning_log::where('earning_log_slot_id',$sponsor)->where("earning_log_plan_type",$share_link_label)->whereDate('earning_log_date_created',$date_today)->count();
						
						if($check_count < $share_settings->share_link_maximum_register_per_day)
						{
							$check_sum 		= Tbl_earning_log::where('earning_log_slot_id',$sponsor)->where("earning_log_plan_type",$share_link_label)->sum("earning_log_amount");
							if($check_sum < $share_settings->share_link_maximum_income)
							{
								$total       = $check_sum + $share_settings->share_link_income_per_registration;

								if($total <= $share_settings->share_link_maximum_income)
								{
									$share_earning =  $share_settings->share_link_income_per_registration;
									$details = '';
									Log::insert_earnings($sponsor,$share_earning,"SHARE_LINK_V2","Member Registration",$new_id,$details);
									Log::insert_wallet($sponsor,$share_earning,"SHARE_LINK_V2");
								}
								else 
								{
									$diff    = $total - $share_settings->share_link_maximum_income;
									$share_earning =  $share_settings->share_link_income_per_registration - $diff;
									$details = '';
									Log::insert_earnings($sponsor,$share_earning,"SHARE_LINK_V2","Member Registration",$new_id,$details);
									Log::insert_wallet($sponsor,$share_earning,"SHARE_LINK_V2");
								}
								
							}
						}
					}
				}
			}

			$referral_vouher_status = Tbl_mlm_plan::where('mlm_plan_code','REFERRAL_VOUCHER')->first()->mlm_plan_enable ?? 0;

			if($referral_vouher_status == 1)
			{
				if($sponsor != 0)
				{
					$get_sponsor_info					= Tbl_slot::where('slot_id',$sponsor)->pluck('slot_membership')->first();

					if($get_sponsor_info)
					{
						$income 						= Tbl_referral_voucher_settings::where("membership_id",$get_sponsor_info)->first();

						if($income)
						{
							Log::insert_earnings($sponsor,$income->referrer_income,"REFERRAL_VOUCHER","Referral",$new_id,"",0,13);
							Log::insert_wallet($sponsor,$income->referrer_income,"REFERRAL_VOUCHER",13);

							Log::insert_earnings($new_id,$income->referee_income,"REFERRAL_VOUCHER","Referral",$sponsor,"",0,13);
							Log::insert_wallet($new_id,$income->referee_income,"REFERRAL_VOUCHER",13);
						}
					}
				}
			}
			// /*FOR BESTLABPH ONLY*/
			// $sign_up_plan_status							= Tbl_mlm_plan::where('mlm_plan_code','SIGN_UP_BONUS')->first()->mlm_plan_enable;

			// $slot_info									= Tbl_slot::where('slot_id',$new_id)->first();
			// if($sign_up_plan_status == 1)
			// {
			// 	Mlm_complan_manager::sign_up_bonus($slot_info);
			// }			
			// /*******************/  

		}
		else
		{
			// /*FOR BESTLABPH ONLY*/
			// $slot_info									= Tbl_slot::where('slot_id',$new_id)->first();
			// $amount = 2000;
			// Log::insert_wallet($slot_info->slot_id,$amount,"REGISTRATION VOUCHER",13);
			// /*******************/  
		}
		if($slot_link == "product_share_link")
		{
			$product_sharelink_logs['slot_owner']		= $new_id;
			$product_sharelink_logs['slot_sponsor']		= $sponsor;
			$product_sharelink_logs['item_id']			= $item_id;
						
			Tbl_product_share_link_logs::insert($product_sharelink_logs);

		}

		// /*FOR 3s ONLY*/ 
		// $new_slot 						  				= Tbl_slot::where("slot_id",$new_id)->first();
		// Tree::insert_tree_sponsor($new_slot, $new_slot, 1);
		// /***********/ 

        Wallet::generateSlotWalletAddress($new_id);
	}

	public static function get_auto_position()
	{
		$response = Tbl_slot::leftJoin("tbl_tree_placement","tbl_tree_placement.placement_parent_id","=","tbl_slot.slot_id")
                    ->select("tbl_slot.*","tbl_tree_placement.placement_parent_id",DB::raw("count(tbl_tree_placement.placement_parent_id) as count"))
                    ->groupBy('tbl_tree_placement.placement_parent_id',"tbl_slot.slot_id")
                    ->orderBy("slot_id","ASC")
                    ->where(function ($query)
                    {
                        $query->where("tbl_tree_placement.placement_level",1)
                              ->orWhere("tbl_tree_placement.placement_parent_id",null);
                    })
                    ->having('count', '<=', 1)
                    ->first();
        if($response)
        {
	        $check_left  = Tbl_slot::where("slot_placement",$response->slot_id)->where("slot_position","LEFT")->first();
	        $check_right = Tbl_slot::where("slot_placement",$response->slot_id)->where("slot_position","RIGHT")->first();
	        if(!$check_left)
	        {
	        	$response["slot_no"]  = $response->slot_no;
	        	$response["position"] = "LEFT";
	        }
	        else if(!$check_right)
	        {
	        	$response["slot_no"]  = $response->slot_no;
	        	$response["position"] = "RIGHT";
	        }
		}
		return $response;
	}

	public static function get_board_auto_position($board_level)
	{
		$response = DB::table("tbl_mlm_board_slot")
		->leftJoin("tbl_mlm_board_placement","tbl_mlm_board_placement.placement_parent_id","=","tbl_mlm_board_slot.slot_id")
		->select("tbl_mlm_board_slot.board_level","tbl_mlm_board_slot.slot_id as slot_id",DB::raw("SUM( ( CASE WHEN tbl_mlm_board_placement.board_level = $board_level THEN 1 ELSE 0 END ) ) AS count"))
		->groupBy("tbl_mlm_board_slot.slot_id","tbl_mlm_board_slot.board_level")
		->orderBy("slot_id","ASC")
		->where(function ($query)
		{
			$query->where("tbl_mlm_board_placement.placement_level",1)
				->orWhere("tbl_mlm_board_placement.placement_parent_id",null);
		})
		->where('tbl_mlm_board_slot.board_level',$board_level)
		->having('count', '<=', 1)
		->first();
		if($response)
		{

			$check_left  = Tbl_mlm_board_placement::where("placement_parent_id",$response->slot_id)->where("placement_position","LEFT")->where('board_level', $board_level)->first();
			$check_right = Tbl_mlm_board_placement::where("placement_parent_id",$response->slot_id)->where("placement_position","RIGHT")->where('board_level', $board_level)->first();
			if(!$check_left)
			{
				$response->position = "LEFT";
			}
			else if(!$check_right)
			{
				$response->position = "RIGHT";
			}

		}
		return $response;
	}

	public static function get_matrix_auto_position($slot = null, $default = 1)
	{
		$matrix_settings = Tbl_unilevel_matrix_bonus_settings::first();

		if($default == 0) {
			$placement_slot_id = $slot->slot_id;
		} else if ($default == 1) {
			if($matrix_settings->matrix_placement_start_at != 1) {
				$upline_sponsor_info = Tbl_slot::where([
					['slot_owner', $slot->slot_owner],
					['slot_count_id', $matrix_settings->matrix_placement_start_at],
					['matrix_sponsor', '!=', 0],
					['matrix_position', '!=', null]
					])->first();
				if($upline_sponsor_info) {
					$placement_slot_id = $upline_sponsor_info->slot_id;
				} else {
					$tree_sponsor = Tbl_tree_sponsor::where('sponsor_child_id', $slot->slot_id)->orderBy('tree_sponsor_id', 'desc')->get();
				
					foreach($tree_sponsor as $t_sponsor) {
						$sponsor_info = Tbl_slot::where('slot_id',$t_sponsor->sponsor_parent_id)->first();
						$sponsor_slots = Tbl_slot::where('slot_owner',$sponsor_info->slot_owner)
						->pluck('slot_id', 'slot_count_id');

						foreach($sponsor_slots as $count => $s_slots) {
							if($count >= $matrix_settings->matrix_placement_start_at) {
								$placement_slot_id = $s_slots;
								break;
							} else if($s_slots == 1) {
								$placement_slot_id = $s_slots;
							}
						}
					}
				}
				
			} else {
				$placement_slot_id = $slot->slot_sponsor;
			}
		}
		
		$response = DB::table("tbl_slot")
			->where('slot_id', $placement_slot_id)
			->leftJoin("tbl_matrix_placement", "tbl_matrix_placement.parent_id", "=", "tbl_slot.slot_id")
			->select(
				"tbl_slot.slot_id as slot_id",
				DB::raw("SUM(CASE WHEN tbl_matrix_placement.level = 1 THEN 1 ELSE 0 END) AS count")
			)
			->groupBy("tbl_slot.slot_id")
			->orderBy("tbl_slot.slot_id", "ASC")
			->where(function ($query) {
				$query->where("tbl_matrix_placement.level", 1)
						->orWhere("tbl_matrix_placement.parent_id", null);
			})
			->first();
			
		if($response) {
			$get_placement_board = Tbl_matrix_placement::where('parent_id',$response->slot_id)->where('level',1)->get();
			if(count($get_placement_board) > 0) {
				if(count($get_placement_board) >= 2) {
					$check_if_exist = [];
					foreach ($get_placement_board as $key => $value) {
						$check_if_exist[] = Tbl_matrix_placement::where('parent_id',$value['child_id'])->count();
						if($check_if_exist < 1) {
							$response->position = "LEFT";
						} else {
							$response->position = "RIGHT";
						}
					}
				} else {
					$response->position = "RIGHT";
				}
			} else {
				$response->position = "LEFT";
			}
		}
		return $response;
	}

	public static function place_slot($data,$type = "admin_area",$owner_id = 0)
	{
		// dd($data, $type);

		$i = 0;
		$return["status_message"] = [];


		$placement = $data["slot_placement"];
		$position  = $data["slot_position"];
		$slot_no   = $data["slot_code"];
		$is_placement_enable = Tbl_slot::where("slot_no", $placement)->JoinMembership()->value('binary_placement_enable');
		if($is_placement_enable) {

			$return["status"]         = "error";
			$return["status_message"][$i] = 'The membership package of this slot is not applicable for Binary Placement!';
			$return["status_code"]    = 400;

		} else {
			$rules["slot_placement"]  = "required|exists:tbl_slot,slot_no";
			$rules["slot_code"]       = "required|exists:tbl_slot,slot_no";
	
			$validator = Validator::make($data, $rules);
			if ($validator->fails())
			{
				foreach ($validator->errors()->getMessages() as $key => $value)
				{
					foreach($value as $val)
					{
						$return["status_message"][$i] = $val;
						$i++;
					}
				}
			}
			else
			{
				 if($position != "LEFT" && $position != "RIGHT")
				 {
					$return["status_message"][$i] = "Position error...";
					$i++;
				 }
	
				 $target_slot     	 	= Tbl_slot::where("slot_no",$slot_no)->first();
	
	
				/* PREVENTS MULTIPLE PROCESS AT ONE TIME */
				$user_process_level = 1;
				Tbl_user_process::where("user_id",$target_slot->slot_owner)->delete();
	
				$insert_user_process["level_process"] = $user_process_level;
				$insert_user_process["user_id"]       = $target_slot->slot_owner;
				Tbl_user_process::where("user_id",$target_slot->slot_owner)->where("level_process",$user_process_level)->insert($insert_user_process);
	
				while($user_process_level <= 4)
				{
					$user_process_level++;
					$insert_user_process["level_process"] = $user_process_level;
					$insert_user_process["user_id"]       = $target_slot->slot_owner;
					Tbl_user_process::where("user_id",$target_slot->slot_owner)->where("level_process",$user_process_level)->insert($insert_user_process);
	
					$count_process_before = Tbl_user_process::where("user_id",$target_slot->slot_owner)->where("level_process", ($user_process_level - 1) )->count();
	
					if($count_process_before != 1)
					{
					   $return["status_message"][$i] = "Please try again...";
					   $i++;
						 break;
					}
				}
	
				Tbl_user_process::where("user_id",$target_slot->slot_owner)->delete();
	
				 $slot_id         	 	= $target_slot->slot_id;
				 $slot_sponsor_id 	 	= $target_slot->slot_sponsor;
				 $placement       	 	= Tbl_slot::where("slot_no",$placement)->first()->slot_id;
				 $check_placement 	 	= Tbl_slot::where("slot_placement",$placement)->where("slot_position",$position)->first();
				 $check_binary_settings  = Tbl_binary_settings::first();
				$check_plan_binary      = Tbl_mlm_plan::where('mlm_plan_code','=','BINARY')->first()->mlm_plan_enable;
				if($type != 'matrix')
				{
					if($check_binary_settings)
					{
						if($check_binary_settings->crossline == 1 && $check_plan_binary == 1)
						{
							if($slot_sponsor_id != $placement)
							{
								$check_sponsor_under = Tbl_tree_placement::where('placement_parent_id',$slot_sponsor_id)->where('placement_child_id',$placement)->first();
								if($check_sponsor_under == null)
								{
									$return["status_message"][$i] = "Attempting crossline...";
									$i++;
								}
							}
						}
					}
					
				
					if($check_placement)
					{
						$return["status_message"][$i] = "Placement already taken...";
						$i++;
					}
					
					else
					{
						$check_placement = Tbl_slot::where("slot_id",$placement)->first();
						if( ($check_placement->slot_placement == 0 && $check_placement->slot_sponsor != 0) || $check_placement->membership_inactive == 1)
						{
							$return["status_message"][$i] = "Placement is not allowed on unplaced slot";
							$i++;
						}
					}
	
					if($target_slot->slot_placement != 0)
					{
						$return["status_message"][$i] = "This slot is already placed.";
						$i++;
					}
				}
	
				 if($type == "member_owned")
				 {
					 $slot_owned  = Tbl_slot::where("slot_no",$slot_no)->where("slot_owner",$owner_id)->first();
					 if(!$slot_owned)
					 {
						$return["status_message"][$i] = "Error 501...";
						$i++;
					 }
	
				 }
	
				 if($type == "member_downline")
				 {
					 $slot_owned  = Tbl_slot::where("slot_no",$slot_no)->first();
					 if(!$slot_owned)
					 {
						$return["status_message"][$i] = "Error 501...";
						$i++;
					 }
					 else
					 {
						 $check_sponsor = Tbl_slot::where("slot_id",$slot_owned->slot_sponsor)->first();
						 if(!$check_sponsor)
						 {
							$return["status_message"][$i] = "Error 503...";
							$i++;
						 }
					 }
				 }
			}

			if($i == 0)
			{
				if($type != 'matrix') {
					$update["slot_placement"]    = $placement;
					$update["slot_position"]     = $position;
					$update["slot_type"]         = "PS";
					$update["slot_date_placed"]  = Carbon::now();
		
					//audit trail old value
					$old_value = Tbl_slot::where("slot_id",$slot_id)->first();
					//end
					Tbl_slot::where("slot_id",$slot_id)->update($update);
				}
	
				$new_slot = Tbl_slot::JoinMembership()->where("slot_id",$slot_id)->first();
				$check_board = Tbl_mlm_plan::where('mlm_plan_code', 'BOARD')->first();
				if($check_board->mlm_plan_enable == 1) {
					$insert_board_slot['slot_id'] = $slot_id;
					$insert_board_slot['placement'] = $placement;
					$insert_board_slot['placement_position'] = $position;
					DB::table('tbl_mlm_board_slot')->insert($insert_board_slot);
					Tree::insert_board_placement($placement, $slot_id, 1, 1);
				}

				$binary_plan = Tbl_mlm_plan::where("mlm_plan_code","BINARY")->where("mlm_plan_enable",1)->first();
				if($binary_plan && $new_slot->binary_realtime_commission == 0) {
					Member::update_binary_projected_income_reset_date($new_slot);
				}
				Tree::insert_tree_placement($new_slot, $new_slot, 1, $placement);
				MLM::placement_entry($slot_id);
				
				if(isset($data['user']))
				{
					$action = 'Slot Placement';
					Audit_trail::audit(serialize($old_value),serialize($new_slot),$data['user']['id'],$action);
				}
	
	
				$return["status"]             = "success";
				$return["status_code"]        = 201;
				$return["status_message"]     = "Slot placed";
				$return["status_data_id"]     = $new_slot->slot_no;
				$return["status_data_id_inc"] = $new_slot->slot_id;
			}
			else
			{
				$return["status"]         = "error";
				$return["status_code"]    = 400;
			}
		}
		
        return $return;
	}

	public static function place_slot_matrix($data) {
		$placement = $data["slot_placement"];
		$position = $data["slot_position"];
		$slot_no = $data["slot_code"];
		$target_slot = Tbl_slot::where("slot_no",$slot_no)->first();
		$slot_id = $target_slot->slot_id;
		$placement = Tbl_slot::where("slot_no",$placement)->first()->slot_id;
		$matrix_placement = Tbl_slot::where("slot_id", $slot_id)->JoinMembership()->value('matrix_placement');
		$slot_count_id = Tbl_slot::where("slot_id", $slot_id)->value('slot_count_id');
		$check_matrix_placement_start = Tbl_unilevel_matrix_bonus_settings::first()->matrix_placement_start_at;
		$check_matrix = Tbl_mlm_plan::where('mlm_plan_code', 'UNILEVEL_MATRIX_BONUS')->first(); 
		// dd($matrix_placement,  $placement, $matrix_placement = Tbl_slot::where("slot_no", $placement)->JoinMembership()->first());
		if($check_matrix && $check_matrix->mlm_plan_enable && $matrix_placement && ($check_matrix_placement_start <= $slot_count_id)) {
			// $insert_matrix['slot_id'] = $slot_id;
			$update_matrix['matrix_sponsor'] = $placement;
			$update_matrix['matrix_position'] = $position;

			Tbl_slot::where('slot_id', $slot_id)->update($update_matrix);

			$new_slot = Tbl_slot::where('slot_id', $slot_id)->first();
			Tree::insert_matrix_placement($new_slot, $new_slot, 1);
			
			Mlm_complan_manager::unilevel_matrix_bonus_commission($new_slot);
			Special_plan::check_livewell_rank($new_slot);
		}
	}

	public static function name_based_on_settings($name)
	{
		/*SLOT CREATION FORMAT*/
		// mlm_slot_no_format_type
		// 1 = Name Abbreviation + Auto
		// 2 = Number
		// 3 = Auto Number
		// 4 = Random Letters
		// 5 = Random Numbers
		$setting = Tbl_mlm_settings::first();
	    $return  = "";
	    $ctr     = 1;

	    $condition = false;

	    while($condition == false)
	    {
		    // mlm_slot_no_format
			if($setting->mlm_slot_no_format_type == 1)
			{
				$return = strtoUpper(substr($name, 0, 3)).Slot::generateRandomString(6);
			}
			else if($setting->mlm_slot_no_format_type == 2)
			{
				$return = Slot::generateRandomString(6,"number");
			}
			else if($setting->mlm_slot_no_format_type == 3)
			{
				$return = str_pad( (Tbl_slot::count() + $ctr) , 6, "0", STR_PAD_LEFT);
			}
			else if($setting->mlm_slot_no_format_type == 4)
			{
				$return = Slot::generateRandomString(6,"alpha");
			}
			else if($setting->mlm_slot_no_format_type == 5)
			{
				$return = Slot::generateRandomString(6,"number");
			}

			$ctr++;


			$check = Tbl_slot::where("slot_no",$return)->first();
			if(!$check)
			{
				$condition = true;
			}
	    }

		return $return;
	}

	public static function generateRandomString($length = 5,$type = "all")
	{
		if($type == "all")
		{
	    	$characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		}
		else if($type == "number")
		{
			$characters = '0123456789';
		}
		else if ($type == "alpha")
		{
			$characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		}

	    $charactersLength = strlen($characters);
	    $randomString = '';
	    for ($i = 0; $i < $length; $i++)
	    {
	        $randomString .= $characters[rand(0, $charactersLength - 1)];
	    }
	    return $randomString;
	}

	public static function get_slot_information($id)
	{
		$response 					 = Tbl_slot::where("tbl_slot.slot_id",$id)
				 					 ->leftJoin("users","users.id","tbl_slot.slot_owner")
									 ->first();

		$response->beneficiary_first_name		= Tbl_beneficiary::where('user_id',$response->id)->value("beneficiary_first_name") ?? null;
		$response->beneficiary_middle_name		= Tbl_beneficiary::where('user_id',$response->id)->value("beneficiary_middle_name") ?? null;
		$response->beneficiary_last_name		= Tbl_beneficiary::where('user_id',$response->id)->value("beneficiary_last_name") ?? null;
		$response->beneficiary_contact			= Tbl_beneficiary::where('user_id',$response->id)->value("beneficiary_contact") ?? null;

		return $response;

	}

	public static function submit_slot_information($request)
	{
		/* Slot Information */
		$rules["slot_no"] 					= "required";
		$rules["slot_owner"] 				= "required";
		$rules["slot_status"] 				= "required";
		$rules["slot_membership"] 			= "required";
		// $rules["slot_sponsor"] = "required";
		// $rules["slot_placement"] = "required";
		// $rules["slot_position"] = "required";

		/* Member Information */

		$get_user_info						= Tbl_slot::where('slot_id', $request['slot_id'])->leftjoin('users','users.id','slot_owner')->first();
		
		if($get_user_info)
		{
			if($get_user_info->email != $request['email'])
			{
				$rules["email"] = "required|unique:users,email|email";
			}
		}
		$rules["first_name"] 								= "required";
		$rules["last_name"] 								= "required";
		$rules["contact"] 									= "required";
		$rules["country_id"] 								= "required";
		// $rules["beneficiary_first_name"] 					= "required";
		// $rules["beneficiary_middle_name"] 					= "required";
		// $rules["beneficiary_last_name"] 					= "required";
		// $rules["beneficiary_contact"] 						= "required";

		if($request["show_password"] != null)
		{
			$rules["show_password"] = "required";
		}

		$validator = Validator::make($request, $rules);

        if ($validator->fails())
        {
			// $return["status"]         = "error";
			// $return["status_code"]    = 400;
			// $return["status_message"] = [];

			// $i = 0;
			// $len = count($validator->errors()->getMessages());

			// foreach ($validator->errors()->getMessages() as $key => $value)
			// {
			// 	foreach($value as $val)
			// 	{
			// 		$return["status_message"][$i] = $val;

			// 	    $i++;
			// 	}
			// }
	      		$return["status"] = "error";
				$return["status_code"] = 400;
				$return["status_message"] = $validator->messages()->all();
	      }
	      else
	      {

			$slot_id = $request['slot_id'];

			$slot_no_old = Tbl_slot::where("slot_id", $slot_id)->first()->slot_no;

			// dd($slot_no_old,$request["slot_no"]);
			if($slot_no_old != $request["slot_no"])
			{
				$unique_no   = Tbl_slot::where("slot_no", $request["slot_no"])->where("slot_id","!=", $slot_id)->first();

				if($unique_no != null)
				{
					$return["status"] = "warning";
					$return["status_code"] = 400;
					$return["status_message"][0] = "Duplicate Slot Code!";
					return $return;
				}

			}
        	$update_slot["slot_no"] = $request["slot_no"];
        	$update_slot["slot_owner"] = $request["slot_owner"];
        	$update_slot["slot_status"] = $request["slot_status"];
			$update_slot["slot_membership"] = $request["slot_membership"];
			$update_slot["store_name"] = $request["store_name"];
			$update_slot["slot_livewell_rank"] = $request["slot_livewell_rank"];

        	// $update_slot["slot_sponsor"] = $request["slot_sponsor"];
        	// $update_slot["slot_placement"] = $request["slot_placement"];
			// $update_slot["slot_position"] = $request["slot_position"];

			//audit trail old value
			$old_value['slot'] = Tbl_slot::where("slot_id", $slot_id)->first();
			//end
			Tbl_slot::where("slot_id", $slot_id)->update($update_slot);
			//audit trail new value
			$new_value['slot'] = Tbl_slot::where("slot_id", $slot_id)->first();
			//end

			/*BESTLABPH*/
			Tbl_signup_bonus_logs::where('slot_id',$slot_id)->update(['membership_id' => $request['slot_membership']]);
			/***********/ 
			$user_id = $request['id'];
        	$update_user["email"] = $request["email"];
        	$update_user["name"] = $request["first_name"] . " " . $request["last_name"];
        	$update_user["first_name"] = $request["first_name"];
        	$update_user["middle_name"] = $request["middle_name"];
        	$update_user["last_name"] = $request["last_name"];
        	$update_user["contact"] = $request["contact"];
        	$update_user["country_id"] = $request["country_id"];
			$update_user["email_verified"] = $request["email_verified"];
			$update_user["verified"] = $request["verified"];
			$company_account_exist = User::where('company_account', 1)->first();

			if ($company_account_exist && $request["company_account"] == 1 && $company_account_exist->id != $user_id) {
				$return["status"] = "Error";
				$return["status_code"] = 400;
				$return["status_message"][] = "Only one account is allowed to have a company account!";
				return $return;
			} else {
				$update_user["company_account"] = $request["company_account"];
			}

			DB::table('tbl_beneficiary')->updateOrInsert(
				[
					'user_id'										=> $user_id,
				],
				[
					'user_id'										=> $user_id,
					'beneficiary_name'								=> $request['beneficiary_first_name']." ".$request['beneficiary_middle_name']." ".$request['beneficiary_last_name'],
					'beneficiary_first_name'						=> $request['beneficiary_first_name'],
					'beneficiary_middle_name'						=> $request['beneficiary_middle_name'],
					'beneficiary_last_name'							=> $request['beneficiary_last_name'],
					'beneficiary_contact'							=> $request['beneficiary_contact'],
				]);

			if($request["show_password"] != null)
			{
        		$update_user["crypt"] = Crypt::encryptString($request["show_password"]);
        		$update_user["password"] = Hash::make($request["show_password"]);
			}
			//audit trail old value
			$old_value['user'] = User::where("id", $user_id)->first();
			//end
			User::where("id", $user_id)->update($update_user);
			//audit trail new value
			$new_value['user'] = User::where("id", $user_id)->first();
			//end
			if(isset($request['user']))
			{
				$action  ='Update Slot & User Info' ;
				Audit_trail::audit(serialize($old_value),serialize($new_value),$request['user']['id'],$action);
			}
			
			if($slot_no_old != $request["slot_no"])
			{
				$insert_slot_no_change["user_id"] 		= Request::user()->id;
				$insert_slot_no_change["slot_id"] 		= $request['slot_id'];
				$insert_slot_no_change["old_slot_code"] = $slot_no_old;
				$insert_slot_no_change["new_slot_code"] = $request['slot_no'];
				$insert_slot_no_change["date_change"] 	= Carbon::now();

				Tbl_slot_code_change_logs::insert($insert_slot_no_change);
			}
			

			$return["status"]         = "success";
			$return["status_code"]    = 201;
			$return["status_message"] = "Slot Updated";
        }
        return $return;
	}

	public static function get_slot_details($id)
	{
		$query = Tbl_slot::where("tbl_slot.slot_id", $id);
		$query = $query->leftJoin("users", "tbl_slot.slot_owner", "=", "users.id");
		$query = $query->leftJoin("tbl_membership", "tbl_slot.slot_membership", "=", "tbl_membership.membership_id");
		$query = $query->leftJoin("tbl_country", "users.country_id", "=", "tbl_country.country_id");
		$query = $query->first();

		if ($query)
		{
			$query->sponsor    = Tbl_slot::where("tbl_slot.slot_id", $query->slot_sponsor)->first() ? Tbl_slot::where("tbl_slot.slot_id", $query->slot_sponsor)->first() : null;
			$query->placement  = Tbl_slot::where("tbl_slot.slot_id", $query->slot_placement)->first() ? Tbl_slot::where("tbl_slot.slot_id", $query->slot_placement)->first() : null;

			$slot_count = Tbl_slot::where("tbl_slot.slot_owner", $query->slot_owner)->where("tbl_slot.archive", 0)->count();

			if ($slot_count)
			{
				$query->slot_count = $slot_count;
			}


			$query->code_source     = Tbl_codes::where("code_id",$query->slot_used_code)->first() ? Tbl_codes::where("code_id",$query->slot_used_code)->first() : null;

			$query->slot_wallet        = Wallet::get_wallet_default($id);
			$query->cashin_wallet	   = Tbl_wallet::where("currency_id", 15)->where("slot_id",$id)->first();
			// $query->slot_earning       = Log::get_earning_amount($id);
			$query->slot_earning    = Tbl_earning_log::where("earning_log_slot_id",$query->slot_id)->where('earning_log_plan_type','!=','SIGN UP BONUS')->where('earning_log_plan_type','!=','REFERRAL VOUCHER')->sum("earning_log_amount");
			$query->voucher       	= Tbl_earning_log::where("earning_log_slot_id",$query->slot_id)->where('earning_log_plan_type','SIGN UP BONUS')->where('earning_log_plan_type','REFERRAL VOUCHER')->sum("earning_log_amount");
			

			$query->slot_downline_sponsor   = Tbl_tree_sponsor::where("sponsor_parent_id",$id)->count();
			$query->slot_downline_placement = Tbl_tree_placement::where("placement_parent_id",$id)->count();

			$query->product_code    = Tbl_codes::where("code_sold_to",$query->slot_owner)->Inventory()->InventoryItem()->where("item_type","product")->count();
			$query->membership_code = Tbl_codes::where("code_sold_to",$query->slot_owner)->Inventory()->InventoryItem()->where("item_type","membership_kit")->count();
		}

		return $query;
	}

	public static function get_slot_earnings($data, $limit = null)
	{
		$query = Tbl_earning_log::where("tbl_earning_log.earning_log_slot_id", $data["id"]);
		$query = $query->leftJoin("tbl_slot", "tbl_slot.slot_id", "=", "tbl_earning_log.earning_log_slot_id");

		if ($data["search"])
		{
			$query = $query->where("tbl_slot.slot_no", 'LIKE', '%' . $data["search"] . '%');
		}

		if ($data["from"] && $data["to"])
		{
			$query = $query->whereBetween("tbl_earning_log.earning_log_date_created", [$data["from"], $data["to"]]);
		}

		if ($data["type"] && $data["type"] != "all")
		{
			$query = $query->where("tbl_earning_log.earning_log_plan_type", $data["type"]);
		}

		if ($limit)
		{
			$query = $query->paginate($limit);
		}
		else
		{
			$query = $query->get();
		}
		foreach ($query as $key => $value) 
		{
			$query[$key]['earning_log_cause_name'] = Tbl_slot::where('slot_id',$value['earning_log_cause_id'])->join('users','users.id', '=','tbl_slot.slot_owner')->pluck('name')->first();
		}

		return $query;
	}

	public static function get_slot_total_earnings($id)
	{
		$query = Tbl_earning_log::where("tbl_earning_log.earning_log_slot_id", $id);
		$query = $query->sum("earning_log_amount");

		return $query;
	}

	public static function get_slot_distributed($data, $limit = null)
	{
		$query = Tbl_earning_log::where("tbl_earning_log.earning_log_cause_id", $data["id"]);
		$query = $query->leftJoin("tbl_slot", "tbl_slot.slot_id", "=", "tbl_earning_log.earning_log_cause_id");

		if ($data["search"])
		{
			$query = $query->where("tbl_slot.slot_no", 'LIKE', '%' . $data["search"] . '%');
		}

		if ($data["from"] && $data["to"])
		{
			$query = $query->whereBetween("tbl_earning_log.earning_log_date_created", [$data["from"], $data["to"]]);
		}

		if ($data["type"] && $data["type"] != "all")
		{
			$query = $query->where("tbl_earning_log.earning_log_plan_type", $data["earning_log_plan_type"]);
		}

		if ($limit)
		{
			$query = $query->paginate($limit);
		}
		else
		{
			$query = $query->get();
		}

		foreach ($query as $key => $q)
		{
			$query[$key]->tbl_slot_owner = Tbl_slot::where("slot_id",$q->earning_log_slot_id)->first()->slot_no;
		}

		//dd($query);
		return $query;
	}

	public static function get_slot_total_distributed($id)
	{
		$query = Tbl_earning_log::where("tbl_earning_log.earning_log_cause_id", $id);
		$query = $query->sum("earning_log_amount");

		return $query;
	}

	public static function get_slot_wallet($data, $limit = null)
	{
		// dd($data);
		$query = Tbl_wallet_log::where("tbl_wallet_log.wallet_log_slot_id", $data["id"]);
		$query = $query->leftJoin("tbl_slot", "tbl_slot.slot_id", "=", "tbl_wallet_log.wallet_log_slot_id");

		if ($data["from"] && $data["from"] != "null" && $data["to"] && $data["to"] != "null")
		{
			$query = $query->whereBetween("tbl_wallet_log.wallet_log_date_created", [$data["from"], $data["to"]]);
		}

		if ($limit)
		{
			$return = $query->paginate($limit);
		}
		else
		{
			$return = $query->get();
		}
		return $return;
	}

	public static function get_slot_total_wallet($id)
	{
		$query = Tbl_wallet_log::where("tbl_wallet_log.wallet_log_slot_id", $id);
		$query = $query->sum("wallet_log_amount");

		return $query;
	}

	public static function get_slot_payout($data, $limit = null)
	{
		$query = Tbl_wallet_log::where("tbl_wallet_log.wallet_log_slot_id", $data["id"])->where('wallet_log_details','CASH OUT');
		$query = $query->leftJoin("tbl_slot", "tbl_slot.slot_id", "=", "tbl_wallet_log.wallet_log_slot_id");

		if ($data["from"] && $data["from"] != "null" && $data["to"] && $data["to"] != "null")
		{
			$query = $query->whereBetween("tbl_wallet_log.wallet_log_date_created", [$data["from"], $data["to"]]);
		}

		if ($limit)
		{
			$query = $query->paginate($limit);
		}
		else
		{
			$query = $query->get();
		}
		return $query;
	}

	public static function get_slot_total_payout($id)
	{
		$query = Tbl_wallet_log::where("tbl_wallet_log.wallet_log_slot_id", $id)->where('wallet_log_details','CASH OUT');
		$query = $query->sum("wallet_log_amount");

		return $query;
	}

	public static function get_slot_points($data, $limit = null)
	{
		$query = Tbl_points_log::where("tbl_points_log.points_log_slot_id", $data["id"]);
		$query = $query->leftJoin("tbl_slot", "tbl_slot.slot_id", "=", "tbl_points_log.points_log_slot_id");

		if ($data["from"] && $data["from"] != "null" && $data["to"] && $data["to"] != "null")
		{
			$query = $query->whereBetween("tbl_points_log.points_log_date_created", [$data["from"], $data["to"]]);
		}

		if ($data["type"] && $data["type"] != "all")
		{
			$query = $query->where("tbl_points_log.points_log_type", $data["type"]);
		}

		if ($limit)
		{
			$query = $query->paginate($limit);
		}
		else
		{
			$query = $query->get();
		}

		foreach ($query as $key => $value)
		{
			$slot_trigger = Tbl_slot::where("tbl_slot.slot_id", $value->points_log_cause_id)->first();

			if ($slot_trigger)
			{
				$query[$key]->slot_trigger = $slot_trigger;
			}
			else
			{
				$query[$key]->slot_trigger = null;
			}
		}

		return $query;
	}

	public static function get_slot_total_points($id)
	{
		$query = Tbl_points_log::where("tbl_points_log.points_log_slot_id", $id);
		$query = $query->sum("points_log_amount");

		return $query;
	}

	public static function get_slot_network($data, $limit = null)
	{
		$type  = $data["type"];
		if(!$type)
		{
			$type = "placement";
		}
		if($type == "placement")
		{
			$query = Tbl_tree_placement::where("placement_parent_id", $data["id"])->child()->orderBy("placement_level","ASC")->leftJoin("users","users.id","=","tbl_slot.slot_owner");
		}
		else if($type == "sponsor")
		{
			$query = Tbl_tree_sponsor::where("sponsor_parent_id", $data["id"])->child()->orderBy("sponsor_level","ASC")->leftJoin("users","users.id","=","tbl_slot.slot_owner");
		}
		else if($type == "matrix")
		{
			$query = Tbl_matrix_placement::where("parent_id", $data["id"])->child()->orderBy("level","ASC")->leftJoin("users","users.id","=","tbl_slot.slot_owner");
		}
		if($data["search"])
		{
			$query = $query->where('tbl_slot.slot_no', 'LIKE', '%' . $data["search"] . '%')
                		   ->orWhere('users.name','LIKE','%' . $data["search"] . '%');
		}
		if($data["level"] && $data["level"] != "all")
		{
			$query = $query->where($type."_level",$data["level"]);
		}

		if ($limit)
		{
			$query = $query->paginate($limit);
		}
		else
		{
			$query = $query->get();
		}
		return $query;
	}

	public static function get_slot_codevault($data, $limit = null)
	{
		$query = Tbl_codes::soldTo($data["id"])->inventory()->inventoryItem()->inventoryItemMembership();

		if ($limit)
		{
			$query = $query->paginate($limit);
		}
		else
		{
			$query = $query->get();
		}

		foreach ($query as $key => $value)
		{
			if($value->code_used == 1)
			{
				$query[$key]['code_user_name'] = Tbl_slot::where('slot_id',$value->code_used_by)->Owner()->value('name');
				$query[$key]['code_user_slot'] = Tbl_slot::where('slot_id',$value->code_used_by)->Owner()->value('slot_no');
			}
			else
			{
				$query[$key]['code_user_name'] = "UNUSED";
			}
		}



		return $query;
	}
	public static function get_slots_pass_up()
	{
		$query 					  =  Tbl_slot::where("archive",0)->paginate(10);
		$return['slot'] 		  = $query;
		// dd($return);
		return $return;
	}
	public static function get_slots_pass_up_all()
	{
		$return['full_slot_list'] = Tbl_slot::where("archive",0)->select('slot_id','slot_no')->get();
		// dd($return);
		return $return;
	}
	public static function get_slots_binary()
	{
		$query 					  =  Tbl_slot::whereNotIn('slot_id',function($query) {

												$query->select('binary_cause_slot_id')->from('tbl_binary_points');

											 })
											->whereIn('slot_id',function($query) {

												$query->select('placement_child_id')->from('tbl_tree_placement');

											 });

		$query		             = $query->where("archive",0);
		$query		             = $query->where("membership_inactive",0);

		$return                  = $query->paginate(10);
		// dd($return);
		return $return;
	}
	public static function get_slots_binary_all()
	{
		$query 					  =  Tbl_slot::whereNotIn('slot_id',function($query) {

										$query->select('binary_cause_slot_id')->from('tbl_binary_points');

									})
									->whereIn('slot_id',function($query) {

										$query->select('placement_child_id')->from('tbl_tree_placement');

									});

		$query		             = $query->where("archive",0);
		$query		             = $query->where("membership_inactive",0);

		$return                  = $query->paginate(10);
		// dd($return);
		return $return;
	}

	public static function generate_slot_id_number() {
		// Define the prefix
		$check_alias = Tbl_code_alias::first();

		$prefix = $check_alias->code_alias_name ?? 'IEC';

		if (!$check_alias) {
			Tbl_code_alias::insert(['code_alias_name' => $prefix]);
		}
		// Loop until a unique ID is generated
		do {
			// Generate a random 9-digit number
			$number = str_pad(rand(0, 999999999), 9, '0', STR_PAD_LEFT);
			$slot_id_number = $prefix . "-" . $number;
	
			// Check if the generated ID already exists in the database
			$exists = Tbl_slot::where('slot_id_number', $slot_id_number)->exists();
		} while ($exists); // Repeat until a unique ID is found
	
		return $slot_id_number; // Return the unique slot ID
	}

    // public static function check_placement($slot_placement,$slot_position)
	// {
	// 	$slot    = Tbl_slot::where("slot_id",$slot_id)->first();
	// 	if($slot)
	// 	{
	// 		if($slot_position == "LEFT" || $slot_position == "RIGHT")
	// 		{
	// 			$exist = Tbl_slot::where("slot_id",$slot_placement)->where("slot_position",$slot_position)->first();
	// 			if($exist)
	// 			{

	// 			}
	// 			else
	// 			{

	// 			}
	// 		}
	// 		else
	// 		{
	// 			$message = "Only LEFT or RIGHT for position.";
	// 			$status  = "no_position";
	// 		}
	// 	}
	// 	else
	// 	{
	// 		$message = "Placement slot not found.";
	// 		$status  = "no_placement";
	// 	}

	// 	$data["message"] = $message;
	// 	$data["status"]  = $status;
	// }
}
