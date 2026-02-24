<?php
namespace App\Globals;
use App\Globals\Audit_trail;
use App\Models\User;
use App\Models\Tbl_slot;
use App\Models\Tbl_currency;
use App\Models\Tbl_wallet;
use App\Models\Tbl_slot_limit;
use App\Models\Tbl_codes;
use App\Models\Tbl_cashier;
use App\Models\Tbl_slot_code_change_logs;
use App\Models\Tbl_other_settings;
use App\Models\Tbl_dealer;
use App\Models\Tbl_retailer;
use App\Models\Tbl_binary_settings;
use App\Models\Tbl_binary_projected_income_log;
use App\Models\Tbl_tree_placement;
use DB;
use Carbon\Carbon;
use Validator;
use Crypt;
use Hash;
use Request;
use App\Globals\Slot;
use App\Models\Tbl_binary_points;
use App\Models\Tbl_earning_log;
use App\Models\Tbl_leaders_support_log;
use App\Models\Tbl_marketing_support_log;
use App\Models\Tbl_milestone_bonus_settings;
use App\Models\Tbl_tree_sponsor;

class Member
{
	public static function get($type = "member", $search = null, $member_active = null)
	{
		// $return = User::where("type",$type)->where('id','!=',Request::user()->id);

		// if(isset($search))
		// {
		// 	$return->where('users.name', "like", "%". $search . "%")->orWhere('users.email', "like", "%". $search . "%")->select('name','id','email');
		// }

		// $return = $return->limit(10)->get();
		
		// return $return;

		$return = User::where("type",$type)->where('users.id','!=',Request::user()->id)
				->where('name','!=','Administrator')
				->leftjoin('tbl_slot','tbl_slot.slot_owner','users.id')
				->where('tbl_slot.slot_no','!=','root')
				->where('slot_status','!=','blocked')
				->where(function($return) use ($search)
				{
					$return->where('slot_count_id',0)
					->orWhere('slot_count_id',1);
					
				});

				if ($member_active) {
					$return->where(function($query) use ($member_active) {
						$query->where('slot_membership', 0)
							  ->where('slot_type', '!=', 'PS');
					});
				}
				
				if ($search) {
					$return->where(function($query) use ($search) {
						$query->where('users.name', "like", "%". $search . "%")
						->orWhere('tbl_slot.slot_no', "like", "%". $search . "%");
					});
				}

		$return = $return->select('first_name','middle_name','last_name','name','id','email','slot_no')->limit(10)->get();
		return $return;
	}

	public static function add_member($data,$area = "admin", $platform = null)
	{
		// Check if the Sponsor Username was Main Account not other slot
		if(isset($data["slot_referral"])) {
			$sponsor_info = Tbl_slot::where('slot_no', $data["slot_referral"])->first();
			if ($sponsor_info && $sponsor_info['slot_type'] != 'PS') {
				if (!$sponsor_info['last_membership']) {
					$data["slot_referral"] = null;
				}
			}
		}
		// $main_slot = $slot_owner ? optional(Tbl_slot::where('slot_owner', $slot_owner)->first())->slot_no : null;
		// $check_exist = $main_slot && $main_slot == $data["slot_referral"];
		if(!isset($data["slot_referral"]))
		{
			$data["slot_referral"] = null;
		}
		if(!isset($data["username"]))
		{
			$data["username"] = null;
		}
		if(!isset($data["first_name"]))
		{
			$data["first_name"] = null;
		}
		if(!isset($data["last_name"]))
		{
			$data["last_name"] = null;
		}
		if(!isset($data["middle_name"]))
		{
			$data["middle_name"] = null;
		}
		if(!isset($data["contact"]))
		{
			$data["contact"] = null;
		}
		if(!isset($data["password"]))
		{
			$data["password"] = null;
		}
		if(!isset($data["password_confirmation"]))
		{
			$data["password_confirmation"] = null;
		}
		$i = 0;
		$messages = [
			'regex' 		=> 'Invalid Characters!',
			'size' 			=> 'Invalid Characters!',
		];
		$messages2 = [
			'regex' 		=> 'Contact must be a Number!',
			'size' 			=> 'Contact no. must be 11+ digits',
		];
		$messages3 = [
			'regex' 		=> 'Username may contain only letters and numbers minimum of 8 characters and maximum of 15 characters!',
			// 'size' 			=> 'Username may contain atleast 6 characters',
		];
		$messages4 = [
			'regex' 		=> 'Please select valid sponsor username',
			// 'size' 			=> 'Username may contain atleast 6 characters',
		];
		if($area == "register_area")
		{
			$rules["password"]    		       = "required|alpha_num";
			$rules["password_confirmation"]    = "required|same:password|alpha_num";
		}
		if($data["register_platform"] == "system")
		{
			$rules["email"]    		= "required|unique:users,email|email";
			$rules3["username"]    	= "required|unique:tbl_slot,slot_no|regex:/^[a-z0-9A-Z]{1,15}$/";
			$rules["first_name"]    = "required|regex:/^[a-zA-Z0-9 -.]*$/";
			$rules["middle_name"]   = "required|regex:/^[a-z0-9 _\/-]{1,100}/i";
			$rules["last_name"]    	= "required|regex:/^[a-zA-Z0-9 -.]*$/";
			$rules["password"] 		= "required|alpha_num";
			$rules2["contact"]		= "required|regex:/^[0-9]*$/|size:11";
			$rules4["slot_referral"] 				= "required|exists:tbl_slot,slot_no";
		}
		else
		{
			$rules["social_id"] = "unique:users,social_id";
		}

		if(isset($data["dealer_code"]))
		{
			$dealer = Tbl_dealer::where("dealer_code",$data["dealer_code"])->first();
			if($dealer)
			{
				$max_retailer = Tbl_other_settings::where("key","max_retailer")->first() ? Tbl_other_settings::where("key","max_retailer")->first()->value : 0;
				$count = Tbl_retailer::where("dealer_slot_id",$dealer->slot_id)->count();
				if($count >= $max_retailer)
				{
					$dealers_error = "Dealer already reached the limit of registered retailer.";
				}
				else
				{
					$insert["registered_as_retailer"] = 1;
				}
			}
			else
			{
				$dealers_error = "Dealer's link not found...";
			}
		}
		$check_if_fullname_exist = User::where("first_name",$data["first_name"])->where("middle_name",$data["middle_name"])->where("last_name",$data["last_name"])->first();
		$check_other_setting = Tbl_other_settings::where("key","allow_duplicated_name")->first() ? Tbl_other_settings::where("key","allow_duplicated_name")->first()->value : $check_other_setting = null;
		$validator = Validator::make($data, $rules, $messages);
		$validator2 = Validator::make($data, $rules2, $messages2);
		$validator3 = Validator::make($data, $rules3, $messages3);
		$validator4 = Validator::make($data, $rules4, $messages4);

        if ($validator->fails() || $validator2->fails() || $validator3->fails() || $validator4->fails())
		{
			$return["status"]         = "error";
			$return["status_code"]    = 400;
			$return["status_message"] = [];
			$len = count($validator->errors()->getMessages());

			foreach ($validator->errors()->getMessages() as $key => $value)
			{
				foreach($value as $val)
				{
					$return["status_message"][$i] = $val;
				  $i++;
				}
			}
			$len2 = count($validator2->errors()->getMessages());
			foreach ($validator2->errors()->getMessages() as $key => $value)
			{
				foreach($value as $val)
				{
					$return["status_message"][$i] = $val;
					$i++;
				}
			}
			$len3 = count($validator3->errors()->getMessages());
			foreach ($validator3->errors()->getMessages() as $key => $value)
			{
				foreach($value as $val)
				{
					$return["status_message"][$i] = $val;
					$i++;
				}
			}
			$len4 = count($validator4->errors()->getMessages());
			foreach ($validator4->errors()->getMessages() as $key => $value)
			{
				foreach($value as $val)
				{
					$return["status_message"][$i] = "Please enter valid sponsor username";
					$i++;
				}
			}
		}
		else if(isset($dealers_error))
		{
			$return["status"]            = "error";
			$return["status_code"]       = 400;
			$return["status_message"]    = [];

			$return["status_message"][0] = $dealers_error;
		}
		else if($check_other_setting == 0 && $check_if_fullname_exist != null)
		{
				$return["status"]            = "error";
				$return["status_code"]       = 400;
				$return["status_message"]    = [];
				$return["status_message"][0] = "Duplicated Fullname";
		}
		else
		{
			if($data["register_platform"] == "system")
			{
				$insert["email"]			= $data["email"];
				$insert["password"]			= Hash::make($data["password"]);
				$insert["crypt"]			= Crypt::encryptString($data["password"]);
				$insert["created_at"]		= Carbon::now();
				$insert["type"]				= "member";
				$insert["first_name"]		= $data["first_name"];
				$insert["middle_name"]		= isset($data["middle_name"]) ? $data["middle_name"] : null;
				$insert["last_name"]		= $data["last_name"];
				$insert["contact"]			= $data["contact"];
				$insert["country_id"]	    = $data["country_id"];
				$insert["name"]	            = $data["first_name"]." ".$data["last_name"];
				$insert["email_verified"]	= 1;
			}
			else
			{
				$insert["created_at"]				= Carbon::now();
				$insert["type"]						= "member";
				$insert["crypt"]					= Crypt::encryptString($data["social_id"]);
				// $insert["email"]					= isset($data["email"]) ? $data["email"] : null;
				$insert["first_name"]				= isset($data["first_name"]) ? $data["first_name"] : null;
				$insert["middle_name"]				= isset($data["middle_name"]) ? $data["middle_name"] : null;
				$insert["last_name"]				= isset($data["last_name"]) ? $data["last_name"] : null;
				$insert["name"]	            		= isset($data["first_name"]) && isset($data["last_name"]) ? $data["first_name"]." ".$data["last_name"] : null;
				$insert["registration_platform"]    = $data["register_platform"];
				$insert["social_id"]				= $data["social_id"];
				$insert["password"]					= Hash::make($data["social_id"]);
			}

			$status_data_id = User::insertGetId($insert);
			$slot_limit     = Tbl_other_settings::where("key","default_slot_limit")->first() ? Tbl_other_settings::where("key","default_slot_limit")->first()->value : 0;
			$insert_limit["user_id"] 		  = $status_data_id;
			$insert_limit["active_slots"]	  = 0;
			$insert_limit["slot_limit"]       = $slot_limit;

			Tbl_slot_limit::insert($insert_limit);
			if(isset($data['slot_referral']))
			{
				$slot_id                   = Tbl_slot::where('slot_no',$data['slot_referral'])->value('slot_id');
				if($slot_id )
				{
					$item_id = $data['slot_link'] != "referral" ? $data['item_id'] : null;
					Slot::create_blank_slot($status_data_id,$slot_id,$data['slot_link'],0,0,$data["username"],$item_id);
					goto already_create;
				}
			}
			else if(isset($data["dealer_code"]))
			{
				$dealer_code = isset($data["dealer_code"]) ? $data["dealer_code"] : 0;

				if($dealer_code)
				{
					Slot::create_blank_slot($status_data_id,0,0,0,$dealer_code);
					goto already_create;
				}
			}
			else
			{
				Slot::create_blank_slot($status_data_id,0,null,0,0,$data["username"],null);
			}


			already_create:
			$status_data_name = User::where('id',$status_data_id)->first();

			//audit trail
			if(isset($data['user']))
			{
				$action = 'Create Member';
				$member = User::where('id',$status_data_id)->first();
				Audit_trail::audit(null,serialize($member),$data['user']['id'],$action);
			}


			$return["status"]         	 = "success";
			$return["status_code"]    	 = 201;
			$return["status_message"] 	 = "Member Created";
			$return["status_data_id"] 	 = $status_data_id;
			$return["status_data_name"]  = $status_data_name->name;
			$return["status_data_email"] = $status_data_name->email;
		}
		return $return;
	}

	public static function check_credentials($member)
	{
		$password = User::where("social_id", $member)->first();
		if($password)
		{
			return Crypt::decryptString($password->crypt);
		}
		else
		{
			return 0;
		}
	}

	public static function update_limit($data)
	{
		// dd($data);
		if($data["slot_limit"] !== null)
		{
			if($data["update_all"] !=1)
			{
				$user_id		= $data["user_id"];
				$active_slots	= $data["active_slots"];
				if($data["slot_limit"])
				{

				}
				$slot_limit 	= (int)$data["slot_limit"];

				if($user_id != 0)
				{
					if($slot_limit >= 0)
					{
						$update["slot_limit"] = $slot_limit;

						//Audit trail old value
						$old_value	= Tbl_slot_limit::where("user_id",$user_id)->first();
						//end
						Tbl_slot_limit::where("user_id",$user_id)->update($update);
						//Audit trail new value
						$new_value  = Tbl_slot_limit::where("user_id",$user_id)->first();
						//end
						$action = 'Update Limit per Slot';
						Audit_trail::audit(serialize($old_value),serialize($new_value),$data['user']['id'],$action);

						$return["status"]         = "success";
				        $return["status_code"]    = 201;
				        $return["status_message"] = "Slot limit Updated";
					}
				}
				else
				{
					$return["status"]         = "Error";
	    	   		$return["status_code"]    = 201;
	    	    	$return["status_message"] = "Error";

				}
			}
			else
			{
				// $user_id		= $data["user_id"];
				// $active_slots	= $data["active_slots"];
				$slot_limit 	= (int)$data["slot_limit"];
				// if($user_id != 0)
				// {

					if($slot_limit >= 0)
					{
						$update["slot_limit"] = $slot_limit;
						$count  			  = Tbl_slot_limit::get();
						foreach ($count as $key => $value)
						{
							Tbl_slot_limit::where("user_id",$value->user_id)->update($update);
						}
						$action = 'Update Limit All';
						Audit_trail::audit(null,$slot_limit,$data['user']['id'],$action);

						$return["status"]         = "success";
				        $return["status_code"]    = 201;
				        $return["status_message"] = "Slot limit Updated";
					}
				// }
				// else
				// {
				// 	$return["status"]         = "Error";
	    	   	// 	$return["status_code"]    = 201;
	    	    // 	$return["status_message"] = "Error";

				// }
			}
		}
		else
		{
			$return["status"]         = "Error";
			$return["status_code"]    = 201;
			$return["status_message"] = "Please fill slot limit";

		}
		return $return;

	}

	public static function check_password($password)
	{
		$cashier = Tbl_cashier::where('cashier_user_id', Request::user()->id)->first();
		if($cashier->cashier_position == 'Manager')
		{
			$cashier_user 		= User::where('id', $cashier->cashier_user_id)->first();
			$encrypted_password = Crypt::decryptString($cashier_user->crypt);
			if($encrypted_password == $password)
			{
				$return["status"]         = "success";
				$return["status_code"]    = 200;
				$return["status_message"] = "Passwords match";
				return $return;
			}
			else
			{
				$return["status"]         = "error";
				$return["status_code"]    = 400;
				$return["status_message"] = "Passwords do not match";
				return $return;
			}
		}
		else
		{
			$manager_list = Tbl_cashier::where('cashier_branch_id', $cashier->cashier_branch_id)->where('cashier_position', 'Manager')->join('users', 'tbl_cashier.cashier_user_id', '=', 'users.id')->get();
			foreach($manager_list as $key => $value)
			{
				$encrypted_password = Crypt::decryptString($value->crypt);
				if($encrypted_password == $password)
				{
					$return["status"]         = "success";
					$return["status_code"]    = 200;
					$return["status_message"] = "Passwords match";
					return $return;
				}
				else
				{
					$return["status"]         = "error";
					$return["status_code"]    = 400;
					$return["status_message"] = "Passwords do not match";
					return $return;
				}
			}
		}
	}

	public static function slot_info($type = "member", $search = null)
	{
		$query = User::where(function($two)  use ($type) {$two->where('type', $type)->orWhere('type','=','admin');});

		if(isset($search))
		{
			$query->where('users.name', "like", "%". $search . "%")->orWhere('users.email', "like", "%". $search . "%")->select('name','id','email');
		}

		$return = $query->limit(10)->get();
		// dd($return);
		return $return;
	}

	public static function select_user($id = null)
	{
		if($id)
		{
			$response = User::where('id',$id)->first()->id;
		}
		return $response;
	}

	public static function get_unplaced($code = null)
	{
		if($code)
		{
			$response = Tbl_slot::where('slot_no',$code)->where('slot_type', 'PS')->where('membership_inactive', 0)->first()->slot_no;
		}
		return $response;
	}
	public static function slot_code_history($filter)
	{
		$query = Tbl_slot_code_change_logs::Owner()->where("slot_id", $filter["id"])
											->select('old_slot_code','new_slot_code','date_change','name');
		$response = $query->paginate(5);

		return $response;
	}

	public static function verify($data)
	{
		$update['verified'] = $data['status'];

		if($data['status'] == 2)
		{
			$update['valid_id'] = 'https://image.flaticon.com/icons/svg/71/71619.svg';
		}

		User::where('id', $data['id'])->update($update);

		$response['status'] = 'success';
		$response['id'] = $data['id'];
		$response['status_message'] = 'Successfully Updated';
		return $response;
	}
	public static function new_register_check($data,$area = "admin", $platform = null)
	{
		// dd($data);
		if(!isset($data["first_name"]))
		{
			$data["first_name"] = null;
		}
		if(!isset($data["last_name"]))
		{
			$data["last_name"] = null;
		}
		if(!isset($data["middle_name"]))
		{
			$data["middle_name"] = null;
		}
		if(!isset($data["contact"]))
		{
			$data["contact"] = null;
		}
		if(!isset($data["password"]))
		{
			$data["password"] = null;
		}
		if(!isset($data["password_confirmation"]))
		{
			$data["password_confirmation"] = null;
		}
		$i = 0;
		$messages = [
			'regex' 		=> 'Invalid Input!',
			'size' 			=> 'Invalid Input!',
		];
		$messages2 = [
			'regex' 		=> 'Contact must be a Number!',
			'size' 			=> 'Contact no. must be 11+ digits',
		];
		if($area == "register_area")
		{
			$rules["password"]    		       = "required|alpha_num";
			$rules["password_confirmation"]    = "required|same:password|alpha_num";
		}

		if($data["register_platform"] == "system")
		{
			$rules["email"]    		= "required|unique:users,email|email";
			$rules["first_name"]    = "required|regex:/^[a-zA-Z0-9 -.]*$/";
			// $rules["middle_name"]   = "regex:/^[a-zA-Z0-9 ]*$/";
			$rules["last_name"]    	= "required|regex:/^[a-zA-Z0-9 -.]*$/";
			$rules["password"] 		= "required|alpha_num";
			$rules2["contact"]		= "required|regex:/^[0-9]*$/|size:11";
			
		}
		else
		{
			$rules["social_id"] = "unique:users,social_id";
		}

		if(isset($data["dealer_code"]))
		{
			$dealer = Tbl_dealer::where("dealer_code",$data["dealer_code"])->first();
			if($dealer)
			{
				$max_retailer = Tbl_other_settings::where("key","max_retailer")->first() ? Tbl_other_settings::where("key","max_retailer")->first()->value : 0;
				$count = Tbl_retailer::where("dealer_slot_id",$dealer->slot_id)->count();
				if($count >= $max_retailer)
				{
					$dealers_error = "Dealer already reached the limit of registered retailer.";
				}
				else
				{
					$insert["registered_as_retailer"] = 1;
				}
			}
			else
			{
				$dealers_error = "Dealer's link not found...";
			}
		}
		$check_if_fullname_exist = User::where("first_name",$data["first_name"])->where("middle_name",$data["middle_name"])->where("last_name",$data["last_name"])->first();
		$check_other_setting = Tbl_other_settings::where("key","allow_duplicated_name")->first() ? Tbl_other_settings::where("key","allow_duplicated_name")->first()->value : $check_other_setting = null;
		$validator = Validator::make($data, $rules, $messages);
		$validator2 = Validator::make($data, $rules2, $messages2);

        if ($validator->fails() || $validator2->fails())
		{
			$return["status"]         = "error";
			$return["status_code"]    = 400;
			$return["status_message"] = [];
			$len = count($validator->errors()->getMessages());

			foreach ($validator->errors()->getMessages() as $key => $value)
			{
				foreach($value as $val)
				{
					$return["status_message"][$i] = $val;
				  $i++;
				}
			}
			$len2 = count($validator2->errors()->getMessages());
			foreach ($validator2->errors()->getMessages() as $key => $value)
			{
				foreach($value as $val)
				{
					$return["status_message"][$i] = $val;
					$i++;
				}
			}
		}
		else if(isset($dealers_error))
		{
			$return["status"]            = "error";
			$return["status_code"]       = 400;
			$return["status_message"]    = [];

			$return["status_message"][0] = $dealers_error;
		}
		else if($check_other_setting == 0 && $check_if_fullname_exist != null)
		{
				$return["status"]            = "error";
				$return["status_code"]       = 400;
				$return["status_message"]    = [];
				$return["status_message"][0] = "Duplicated Fullname";
		}
		else
		{
			$return["status"]         	 = "success";
			$return["status_code"]    	 = 201;
		}
		return $return;
	}

	public static function update_binary_projected_income_reset_date($slot) {
		$slot_info = Tbl_slot::where('slot_id', $slot->slot_id)->JoinMembership()
			->select(['slot_membership', 'last_binary_projected_income_reset_date', 'binary_realtime_commission', 'binary_waiting_commission_reset_days', 'slot_date_placed'])
			->first();
		if ($slot_info && $slot_info->slot_membership 
			&& !$slot_info->last_binary_projected_income_reset_date 
			&& $slot_info->binary_realtime_commission == 0 
			&& $slot_info->binary_waiting_commission_reset_days) {
			
			if ($slot_info->slot_date_placed) {
				Tbl_slot::where('slot_id', $slot->slot_id)->update([
					'last_binary_projected_income_reset_date' => Carbon::parse($slot_info->slot_date_placed),
				]);
			}
		}
	}

	public static function check_the_cycle_of_binary_projected_income($slot) {
		$slot_info = Tbl_slot::where('slot_id', $slot->slot_id)->JoinMembership()->first();
		$data["start_date"] = Carbon::parse($slot_info->last_binary_projected_income_reset_date);
		$number_of_days = $slot_info->binary_waiting_commission_reset_days;
		$data["end_date"] = Carbon::parse($slot_info->last_binary_projected_income_reset_date)->addDays($number_of_days);

		return $data;
	}

	public static function check_binary_projected_income($slot) {
		$data = Member::check_the_cycle_of_binary_projected_income($slot);
		
		if($data["end_date"] <= Carbon::now()) {
			Member::binary_projected_income_flushout($slot);
		} 
	}


	public static function check_all_slot_binary_projected_income() {
		$slots = Tbl_slot::where('slot_id', '!=', 1)->JoinMembership()->where('binary_realtime_commission', 0)->get();
		foreach ($slots as $key => $slot) {
			Member::update_binary_projected_income_reset_date($slot);
			Member::check_binary_projected_income($slot);
		}
	}

	public static function binary_projected_income_flushout($slot) {
		$slot_info = Tbl_slot::where('slot_id', $slot->slot_id)->JoinMembership()->first();
		$projected_income = Tbl_binary_projected_income_log::where('slot_id', $slot_info->slot_id)->where('status', 0)->sum('wallet_amount');
		if($slot_info->slot_left_points || $slot_info->slot_right_points || $projected_income) {
			$data = Member::check_the_cycle_of_binary_projected_income($slot);
			$points = [
				'slot_right_points' => 0, 
				'slot_left_points' => 0
			];
			
			$flushout_points = [
				"left" => $slot_info->slot_left_points ?? 0,
				"right" => $slot_info->slot_right_points ?? 0
			];
			
			$receive = [
				"left" => -$flushout_points["left"],
				"right" => -$flushout_points["right"]
			];
			
			$old = $flushout_points;
			$new = ["left" => 0, "right" => 0];
			
			Log::insert_points($slot_info->slot_id, $receive["left"], "BINARY_LEFT_FLUSHOUT", $slot_info->slot_id, 0);
			Log::insert_points($slot_info->slot_id, $receive["right"], "BINARY_RIGHT_FLUSHOUT", $slot_info->slot_id, 0);
			
			Tbl_slot::where('slot_id', $slot_info->slot_id)->update($points);
			
			Log::insert_binary_points(
				$slot_info->slot_id, 
				$receive, 
				$old, 
				$new, 
				$slot_info->slot_id, 
				0, 
				$projected_income,
				0, 
				"Reset Binary Projected Income", 
				0, 
				$flushout_points, 
				0,
				$data["end_date"],
				0
			);		
		} 

		$number_of_days = $slot_info->binary_waiting_commission_reset_days;
		$reset_date = Carbon::parse($slot_info->last_binary_projected_income_reset_date)->addDays($number_of_days);

		Tbl_slot::where('slot_id', $slot_info->slot_id)->update([
			'last_binary_projected_income_reset_date' => $reset_date,
		]);

		Tbl_binary_projected_income_log::where('slot_id', $slot_info->slot_id)->update([
			'status' => 2,
			'date_status_change' => Carbon::now(),
		]);
	}

	public static function log_binary_projected_income($slot) {
		$slot_info = Tbl_slot::where('slot_id', $slot->slot_id)->JoinMembership()->first();
		if($slot_info && $slot_info->slot_membership && $slot_info->binary_realtime_commission == 1) {
			$projected_income_log = Tbl_binary_projected_income_log::where('slot_id', $slot_info->slot_id)->where('status', 0)->get();
	
			if($projected_income_log) {
				foreach($projected_income_log as $log) {
					Log::insert_wallet($log->slot_id, $log->wallet_amount,"BINARY");
					Log::insert_earnings($log->slot_id, $log->wallet_amount,"BINARY","SLOT PLACEMENT",$log->cause_slot_id, "", $log->cause_level);
					
					Tbl_binary_projected_income_log::where('slot_id', $log->slot_id)->update([
						'status' => 1,
						'date_status_change' => Carbon::now(),
					]);
				}
			}
			$projected_income_log_with_income = Tbl_binary_points::where("binary_points_slot_id", $slot->slot_id)->where("binary_points_projected_income", "!=", 0)->get();
			foreach($projected_income_log_with_income as $log) {
				$update["binary_points_income"] = $log->binary_points_projected_income;
				$update["binary_points_projected_income"] = 0;
				Tbl_binary_points::where("binary_points_id", $log->binary_points_id)->update($update);
			}
		}
	}

	public static function check_all_slot_id_number() {
 
		$slots = Tbl_slot::where('slot_id_number', null)->get();
 
		foreach ($slots as $slot) {
			$slot_id_number = Slot::generate_slot_id_number();
			$update_slot['slot_id_number'] = $slot_id_number;
			Tbl_slot::where('slot_id', $slot->slot_id)->update($update_slot);
		}
	}

	public static function get_strong_leg_position($slot_id)
	{
		$settings = Tbl_binary_settings::first();

		if (!$settings->binary_auto_placement_based_on_direct) {
			return null;
		}

		$slot = Tbl_slot::select('slot_position')->where('slot_id', $slot_id)->first();
		if (!$slot) return null;

		$children = Tbl_tree_placement::where('placement_parent_id', $slot_id)
			->where('position_type', 'OUTER')
			->select('placement_position', DB::raw('COUNT(*) as total'))
			->groupBy('placement_position')
			->pluck('total', 'placement_position');

		$leftCount = $children['LEFT'] ?? 0;
		$rightCount = $children['RIGHT'] ?? 0;

		$position = null;

		if ($leftCount !== $rightCount) {
			if ($settings->binary_priority_leg_position === 'strong') {
				$position = $leftCount > $rightCount ? 'LEFT' : 'RIGHT';
			} elseif ($settings->binary_priority_leg_position === 'weak') {
				$position = $leftCount < $rightCount ? 'LEFT' : 'RIGHT';
			}
		} else {
			switch ($settings->binary_default_position_without_spill) {
				case 'left':
					$position = 'LEFT';
					break;
				case 'right':
					$position = 'RIGHT';
					break;
				case 'sponsor_position':
					$position = $slot->slot_position;
					break;
				case 'sponsor_opposite_position':
					$position = $slot->slot_position === 'LEFT' ? 'RIGHT' : 'LEFT';
					break;
				default:
					$position = null;
					break;
			}
		}
		$outerChildIds = Tbl_tree_placement::where('placement_parent_id', $slot_id)
			->where('position_type', 'OUTER')
			->orderByDesc('tree_placement_id')
			->pluck('placement_child_id');

		$validDirects = Tbl_slot::joinMembership()
			->whereIn('slot_id', $outerChildIds)
			->where('slot_sponsor', $slot_id)
			->where('free_slot_membership', 0)
			->count();

		return $validDirects < $settings->binary_number_of_direct_for_auto_placement ? $position : null;
	}

	public static function get_milestone_cycle_info($slot_info)
	{
		$settings = Tbl_milestone_bonus_settings::first();

		if (!$settings || !$settings->milestone_limit) {
			return null;
		}

		$logs = Tbl_earning_log::where('earning_log_slot_id', $slot_info->slot_id)
			->where('earning_log_plan_type', 'MILESTONE BONUS');

		$now = Carbon::now();
		$cycle = strtolower($settings->milestone_cycle_limit);
		$type = strtolower($settings->milestone_type_limit); // 'earnings' or 'pairs'

		$start = $end = null;

		switch ($cycle) {
			case 'daily':
				$start = $now->copy()->startOfDay();
				$end = $now->copy()->endOfDay();
				break;

			case 'halfday':
				$isAM = $now->format('A') === 'AM';
				$start = $isAM ? $now->copy()->startOfDay() : $now->copy()->setTime(12, 0, 0);
				$end = $isAM ? $now->copy()->setTime(11, 59, 59) : $now->copy()->endOfDay();
				break;

			case 'weekly':
				$start = $now->copy()->startOfWeek();
				$end = $now->copy()->endOfWeek();
				break;
		}

		if ($start && $end) {
			$logs = $logs->whereBetween('earning_log_date_created', [$start, $end]);
		}

		$milestone['value'] = ($type === 'earnings')
			? $logs->sum('earning_log_amount')
			: $logs->count();

		return $milestone;
	}

    public static function get_count_direct($slot)
	{
		$slot_direct = [];

		// Count recurring directs (after marketing_support_date_end if set)
		$slot_direct['recurring_direct'] = Tbl_tree_placement::Child()
			->where('placement_parent_id', $slot->slot_id)
			->when($slot->marketing_support_date_end, function ($query, $dateEnd) {
				return $query->where('tbl_slot.slot_date_placed', '>=', $dateEnd);
			})
			->count();

		// Count left direct
		$slot_direct['left_direct'] = Tbl_tree_placement::where('placement_parent_id', $slot->slot_id)
			->where('placement_position', 'LEFT')
			->count();

		// Count right direct
		$slot_direct['right_direct'] = Tbl_tree_placement::where('placement_parent_id', $slot->slot_id)
			->where('placement_position', 'RIGHT')
			->count();

		return $slot_direct;
	}

    public static function update_daily_marketing_support_income($slot_id) {
        $slot = Tbl_slot::JoinMembership()->where('slot_id', $slot_id)->first();
        if($slot) {
            $now = Carbon::now();
			$incomeCount = $slot->marketing_support_count_income + 1;
            Tbl_marketing_support_log::where('log_slot_id', $slot->slot_id)
                ->where('log_claimed', 0)
                ->where('log_status', 0)
				->where('log_income_count', $incomeCount)
                ->where('log_date_created', '<=', $now)
                ->update(['log_claimed' => 1]);
				
			$hasPendingLogs = Tbl_marketing_support_log::where([
					['log_slot_id', '=', $slot->slot_id],
					['log_claimed', '=', 0],
					['log_status', '=', 0],
					['log_income_count', '=', $incomeCount]
				])->exists();
				
			if(!$hasPendingLogs) {
				$sumLogIncome = DB::table('tbl_marketing_support_log')
					->where('log_slot_id', $slot->slot_id)
					// ->where('log_claimed', 1)
					// ->where('log_status', 0)
					->where('log_income_count', $incomeCount)
					->sum('log_income');
					
				$dateIncome = DB::table('tbl_marketing_support_log')
					->where('log_slot_id', $slot->slot_id)
					// ->where('log_claimed', 1)
					// ->where('log_status', 0)
					->where('log_income_count', $incomeCount)
					->orderByDesc('log_date_created')
					->value('log_date_created');

				$updateLog = DB::table('tbl_marketing_support_log')
					->where('log_slot_id', $slot->slot_id)
					->where('log_claimed', 1)
					->where('log_status', 0)
					->update(['log_status' => 1]);
					
				if($updateLog) {
					Tbl_slot::where('slot_id', $slot->slot_id)
						->update([
							'marketing_support_activate' => 0,
							'marketing_support_date_end' => $dateIncome,
					]);	

					Tbl_slot::where('slot_id', $slot->slot_id)
        				->increment('marketing_support_count_income');

					Log::insert_wallet($slot->slot_id, $sumLogIncome,"MARKETING SUPPORT", 1, null, $dateIncome);
					Log::insert_earnings($slot->slot_id, $sumLogIncome,"MARKETING SUPPORT","SLOT PLACEMENT", $slot->slot_id, "", 1, 1, $dateIncome);
				}
			}
        }
    }

	public static function update_leader_support_income($slot_id) {
        $slot = Tbl_slot::JoinMembership()->where('slot_id', $slot_id)->first();
        if($slot) {
            $now = Carbon::now();
			
			$pendingLogs = Tbl_leaders_support_log::where('log_slot_id', $slot->slot_id)
				->where('log_status', 0)
				->where('log_date_end', '<=', $now)
				->get();

			foreach($pendingLogs as $log) {
				// Update log status
				Tbl_leaders_support_log::where('log_id', $log->log_id)
           			->update(['log_status' => 1]);

				// Insert wallet and earnings logs
				Log::insert_wallet($slot->slot_id, $log->log_income,"LEADERS SUPPORT", 1, null, $log->log_date_end);
				Log::insert_earnings($slot->slot_id, $log->log_income,"LEADERS SUPPORT","SLOT CREATION", $log->log_cause_slot_id, "", 1, 1, $log->log_date_end);
			}
        }
    }
}
