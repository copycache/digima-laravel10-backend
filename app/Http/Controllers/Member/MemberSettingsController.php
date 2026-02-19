<?php
namespace App\Http\Controllers\Member;
use App\Models\Tbl_binary_points;
use App\Models\Tbl_welcome_bonus_commissions;
use Illuminate\Support\Facades\Request;
use App\Models\Users;
use App\Models\Tbl_address;
use App\Models\Refregion;
use App\Models\Tbl_genealogy_settings;
use App\Models\Tbl_slot;
use App\Models\Tbl_tree_placement;
use App\Models\Tbl_mlm_board_placement;
use App\Models\Tbl_mlm_unilevel_settings;
use App\Models\Tbl_unilevel_points;
use App\Models\Tbl_membership;
use App\Models\Tbl_tin_logs;
use App\Models\Tbl_island_group;
use App\Models\Refbrgy;
use App\Models\Refcitymun;
use App\Models\Refprovince;
use App\Models\Tbl_beneficiary;


use App\Globals\Location;
use App\Globals\Slot;
use Crypt;
use Hash;
use Storage;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Aws\S3\S3Client;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use League\Flysystem\Filesystem;
use App\Models\Tbl_receiver_infomation;

use Illuminate\Http\Request as Request2;

class MemberSettingsController extends MemberController
{

	public function upload_profile(Request2 $request)
	{
		$file = $request->file('file');

        $path_prefix = 'https://s3.us-west-000.backblazeb2.com/';
        $path = "mlm/profile";
        $storage_path = storage_path();

        if ($file->isValid())
        {
            $full_path = Storage::disk('s3')->putFile($path, $file, "public");
            $url = Storage::disk('s3')->url($full_path);

            $update['profile_picture'] = $url;
            Users::where('id',Request::user()->id)->update($update);
            $response = "success";
           return 	response()->json($response);
        }
	}
	
	public function upload_id(Request2 $request)
	{
		$file = $request->file('file');
		$path_prefix = 'https://s3.us-west-000.backblazeb2.com/';
		$path = "mlm/valid_id";
		$storage_path = storage_path();
		$check_user = Users::where('id',Request::user()->id)->first()->valid_id;
		
		if($check_user == 'https://image.flaticon.com/icons/svg/71/71619.svg')
		{
			if ($file->isValid())
			{
				$full_path = Storage::disk('s3')->putFile($path, $file, "public");
				$url = Storage::disk('s3')->url($full_path);

				$update['valid_id'] = $url;
				Users::where('id',Request::user()->id)->update($update);
				$response['status'] = "success";
				$response['status_message'] = "Successfully submitted. Please wait for your confirmation.";
			}
		}
		else
		{
			$response['status'] = "error";
			$response['status_message'] = "You have already submitted your valid ID. Please wait for confirmation.";

		}
		
		return 	response()->json($response);

	}
	
	public function get_location()
	{
		$location     	= Request::input("location");
		$response 		= Location::$location(Request::all());
		return 	response()->json($response);
	}
    public function get_user_info()
    {
		$response 						= Request::user();

		$response->beneficiary_info 	= Tbl_beneficiary::where('user_id',Request::user()->id)->first();
        $response->user_email 			= Self::hide_mail(Request::user()->email);
        $response->user_phone 			= Self::hide_mobile(Request::user()->contact);
        $response->name 				= Request::user()->first_name." ".Request::user()->last_name;
        $response->store_name			= Tbl_slot::where('slot_id',Request::input('slot_id'))->pluck('store_name')->first();
		$response->accumulated_left_points = Tbl_binary_points::where('binary_points_slot_id',Request::input('slot_id'))->where('binary_receive_left', '>=', 0)->sum('binary_receive_left');
		$response->accumulated_right_points = Tbl_binary_points::where('binary_points_slot_id',Request::input('slot_id'))->where('binary_receive_right', '>=', 0)->sum('binary_receive_right');	
		// Created By: Centy - 10-27-2023
		$Achievers_Rank = Tbl_slot::where('slot_id',Request::input('slot_id'))->AchieversRankAttribute()->select('tbl_achievers_rank.achievers_rank_name')->first();
		$Livewell_Rank = Tbl_slot::where('slot_id',Request::input('slot_id'))->LivewellRankAttribute()->select('tbl_livewell_rank.livewell_rank_name')->first();

		if (isset($Achievers_Rank)) {
			$response->achievers_rank = $Achievers_Rank->achievers_rank_name;
		} else {
			$response->achievers_rank = '---'; // Change 'Default Value' to the desired default value
		}

		if (isset($Livewell_Rank)) {
			$response->livewell_rank = $Livewell_Rank->livewell_rank_name;
		} else {
			$response->livewell_rank = '---'; // Change 'Default Value' to the desired default value
		}

		$birthdate = Request::user()->birthdate == null ? "February,2,1996" : Request::user()->birthdate;

		$birth = explode(",",$birthdate);
        $response->birth_month 		=	$birth[0];
        $response->birth_day 		=	$birth[1];
		$response->birth_year 		=	$birth[2];
		if ($response->crypt)
		{
			try
			{
				$response->user_password = Crypt::decrypt($response->crypt);
			}
			catch (\Exception $e)
			{
				try
                {
                    $response->user_password = Crypt::decryptString($response->crypt);
                }
                catch (\Exception $e)
                {
                    $response->show_password = "";
                }

			}
		}
		else
		{
			$response->user_password = "";
		}
		$response->date_registered  =   Carbon::parse($response->created_at)->format("F d, Y");
        return 	response()->json($response);
	}

	public function get_user_add_ons_info()
    {
		$slot_id = Request::input('id');
		$board_level = Request::input('board_level');
		$settings = Self::what_show();
		$slot                    = Tbl_slot::where("slot_id",$slot_id)->first();
		$total_ppv               = 0;
		if($slot)
		{
			$membership                            = Tbl_membership::where("membership_id",$slot->slot_membership)->first();
			if($membership)
			{
				$membership->membership_unilevel_level = $membership->membership_unilevel_level;
				$level                                 = 1;
				$first_date                      = Carbon::now()->startOfMonth()->format("Y-m-d");
				$end_date                        = Carbon::now()->endOfMonth()->format("Y-m-d");

				$pluss          = Tbl_unilevel_points::where("unilevel_points_slot_id",$slot->slot_id)->where("unilevel_points_type","UNILEVEL_PPV")->where("unilevel_points_date_created",">=",$first_date)->where("unilevel_points_date_created","<=",$end_date)->sum("unilevel_points_amount");
				$total_ppv = $total_ppv + $pluss;
			}
		}
		$response['user_info'] = Tbl_slot::where('tbl_slot.slot_id',$slot_id)->owner()->JoinMembership()->first();
		$response['user_info']["settings"] = $settings;
		$response['user_info']["is_dynamic"] =  Tbl_mlm_unilevel_settings::first()->is_dynamic;
		$response['user_info']["dynamic_ppv"] =  number_format($total_ppv,0);
		$response['user_info']["total_recruits"] = Tbl_slot::where('slot_sponsor',$slot_id)->count();
		$response['user_info']['binary_counts'] = Self::count_child($slot_id);
		$response['user_info']['board_counts'] = Self::count_board_child($slot_id,$board_level);
		$response['user_info']['sponsor_username'] = Tbl_slot::where('tbl_slot.slot_id',$response['user_info']->slot_sponsor)->pluck('slot_no')->first();
		$response['user_info']['accumulated_left_points']			= Tbl_binary_points::where('binary_points_slot_id',Request::input('id'))->sum('binary_receive_left');
		$response['user_info']['accumulated_right_points']			= Tbl_binary_points::where('binary_points_slot_id',Request::input('id'))->sum('binary_receive_right');
		// dd($response['user_info']);
		
        return 	response()->json($response);
    }

	public static function hide_mobile($mobile)
	{
	    return 	substr($mobile, 0, -4). "****" ;
	}

	public static function  hide_mail($email)
	{
	    $mail_part 				= explode("@", $email);
	    // $mail_part[0] 			= substr($email, 0, 4).str_repeat("*", strlen($mail_part[0]));
	    $mail_part[0] 			= str_repeat("*", strlen($mail_part[0]));

	    return implode("@", $mail_part);
	}

	public function update_user_info()
	{
		$team_name = Request::input('team_name');
		$gender    = Request::input('gender');
		$birth_month = Request::input('birth_month');
		$birth_day = Request::input('birth_day');
		$birth_year = Request::input('birth_year');
		$store_name 			= Request::input('store_name');
		$birthdate 				= Request::input('birth_month').",".Request::input('birth_day').",".Request::input('birth_year');
		$_ctr = 0;
		if($gender != null)
		{
			$update['gender'] 		= $gender;
			$_ctr+=1;
		}
		if($team_name != null)
		{
			$update['team_name']    = $team_name;
			$_ctr+=1;
		}
		if ($birth_month != null && $birth_day != null && $birth_year != null) 
		{
			$update['birthdate'] 	= $birthdate;
			$_ctr+=1;
		}
		// dd($_ctr);
		if($_ctr != 0) 
		{
			Users::where('id',Request::user()->id)->update($update);
		}
		if($store_name)
		{
			Tbl_slot::where('slot_id',Request::input('slot_id'))->update(['store_name' =>$store_name]);
		}
  		return response()->json("Profile Updated Successfully!",200);
	}

	public function get_addresses()
	{
		$address_list = Tbl_address::where('tbl_address.user_id',Request::user()->id)->where("tbl_address.archived",0)
						->leftjoin('tbl_receiver_infomation','tbl_receiver_infomation.address_id','tbl_address.address_id')->select('*',DB::raw('tbl_address.address_id as address_id'))->get();

		$address_list->count = count($address_list);
		
		foreach($address_list as $key=>$list)
		{
			// dd($list);
			$address_list[$key]['refregion']			= Refregion::where('refregion.regCode',	 	 '=' ,$list->regCode)->pluck('regDesc')->first();
			$address_list[$key]['refprovince']			= Refprovince::where('refprovince.provCode', 	 '=' ,$list->provCode)->pluck('provDesc')->first();
			$address_list[$key]['refcitymun']			= Refcitymun::where('refcitymun.citymunCode', '=' ,$list->citymunCode)->pluck('citymunDesc')->first();
			$address_list[$key]['refbrgy']				= Refbrgy::where('refbrgy.brgyCode', 		 '=' ,$list->brgyCode)->pluck('brgyCode')->first();
			$address_list[$key]['brgyDesc']				= Refbrgy::where('refbrgy.brgyCode', 		 '=' ,$list->brgyCode)->pluck('brgyDesc')->first();
			// // $address_list->select(DB::raw('refregion.regDesc as reg_desc'),'refregion.*','refprovince.*','refcitymun.*','refbrgy.*','tbl_address.*')->get();

			$address_list[$key]['island_group_name']  	= Tbl_island_group::where('id',$list->island_group)->pluck('island_group')->first();
			$address_list[$key]['additional_info']  	= $list->additional_info;
			$address_list[$key]['barangay_city'] 		= $address_list[$key]['brgyDesc'].", ".$address_list[$key]['refcitymun'];
			$address_list[$key]['region_province'] 		= $address_list[$key]['refprovince'].", ".$address_list[$key]['refregion']." - ".$list->address_postal_code;

			// $address_list[$key]['island_group_name']  	= Tbl_island_group::where('id',$list->island_group)->pluck('island_group')->first();
			// $address_list[$key]['additional_info']  	= $list->additional_info;
			// $address_list[$key]['barangay_city'] 		= $list->brgyDesc.", ".$list->citymunDesc;
			// $address_list[$key]['region_province'] 		= $list->provDesc.", ".$list->reg_desc." - ".$list->address_postal_code;
		}
		return 	response()->json($address_list);
	}

	public function add_addresses()
	{
		$count 							= Tbl_address::where('user_id',Request::user()->id)->count();

		$insert['is_default'] 			= $count == 0 ? 1 : 0;
		$insert['address_postal_code'] 	= Request::input('address_postal_code');
		$insert['island_group'] 		= Request::input('island_group');
  		$insert['regCode'] 				= Request::input('regCode');
  		$insert['provCode'] 			= Request::input('provCode');
  		$insert['citymunCode'] 			= Request::input('citymunCode');
  		$insert['brgyCode'] 			= Request::input('brgyCode');
  		$insert['additional_info'] 		= Request::input('additional_info');
  		$insert['user_id'] 				= Request::user()->id;
		$insert['is_default'] = Tbl_address::where("user_ID", Request::user()->id)->where("is_default", 1)->where("archived", 0)->count() ? 0 : 1;
  		$address_id = Tbl_address::insertGetId($insert);

		// RECEIVER'S INFORMATION
		$insert_receiver['address_id'] 				= $address_id;
		$insert_receiver['receiver_name'] 			= Request::input('receiver_name');
  		$insert_receiver['receiver_contact_number'] = Request::input('receiver_contact_number');
  		$insert_receiver['receiver_email'] 			= Request::input('receiver_email');

		Tbl_receiver_infomation::insert($insert_receiver);

  		return response()->json("Address Added Successfully!",200);
	}
	public function update_address_status()
	{
		$userId = auth()->id();
		$action = request('action');
		$addressId = request('address_id');

		if ($action === "default") {
			// Reset all addresses to non-default
			Tbl_address::where('user_id', $userId)->update(['is_default' => 0]);

			// Set the selected address as default
			Tbl_address::where('address_id', $addressId)->update(['is_default' => 1]);

			$return = [
				"status" => "success",
				"message" => "Address has been set as the default."
			];
		} 
		else {
			// Check if the user has only one active address
			if (Tbl_address::where('user_id', $userId)->where('archived', 0)->count() === 1) {
				$return = [
					"status" => "error",
					"message" => "Cannot delete the only remaining active address."
				];
			} else {
				// Use transaction to ensure atomic operations
				DB::transaction(function () use ($userId, $addressId) {
					// Archive the selected address
					Tbl_address::where('address_id', $addressId)->update(['archived' => 1]);
		
					// Check if there's no remaining default address
					$hasDefault = Tbl_address::where('user_id', $userId)
						->where('archived', 0)
						->where('is_default', 1)
						->exists();
		
					if (!$hasDefault) {
						// Get the first active address and set it as default
						$address = Tbl_address::where('user_id', $userId)
							->where('archived', 0)
							->first();
		
						if ($address) {
							Tbl_address::where('address_id', $address->address_id)->update(['is_default' => 1]);
						}
					}
				});
		
				$return = [
					"status" => "success",
					"message" => "Address has been deleted successfully."
				];
			}
		}
		

		return response()->json($return);
	}

	public function update_address()
	{
		$update['address_postal_code'] 	= Request::input('address_postal_code');
  		$update['regCode'] 				= Request::input('regCode');
  		$update['provCode'] 			= Request::input('provCode');
  		$update['citymunCode'] 			= Request::input('citymunCode');
  		$update['brgyCode'] 			= Request::input('brgyCode');
  		$update['additional_info'] 		= Request::input('additional_info');

  		Tbl_address::where('address_id',Request::input('address_id'))->update($update);

		// RECEIVER'S INFORMATION

		$update_receiver['receiver_name'] 			= Request::input('receiver_name');
  		$update_receiver['receiver_contact_number'] = Request::input('receiver_contact_number');
  		$update_receiver['receiver_email'] 			= Request::input('receiver_email');

		$check = Tbl_receiver_infomation::where('address_id',Request::input('address_id'))->first();
		if($check)
		{
			Tbl_receiver_infomation::where('address_id',Request::input('address_id'))->update($update_receiver);
		}
		else
		{
			$update_receiver['address_id'] = Request::input('address_id');
			Tbl_receiver_infomation::insert($update_receiver);
		}
		
  		return response()->json("Address Updated Successfully!",200);
	}
	public function update_password()
	{
		$response = Request::user();
		if ($response->crypt)
		{
			try
			{
				$response->user_password = Crypt::decrypt($response->crypt);
			}
			catch (\Exception $e)
			{
				try
                {
                    $response->user_password = Crypt::decryptString($response->crypt);
                }
                catch (\Exception $e)
                {
                    $response->show_password = "";
                }

			}
		}
		else
		{
			$response->user_password = "";
		}

		if(Request::input('current_password') == $response->user_password)
		{
			$update["password"]			= Hash::make(Request::input('new_password'));
			$update["crypt"]			= Crypt::encryptString(Request::input('new_password'));
			Users::where('id',Request::user()->id)->update($update);
			$return["status"]           = "success";
			$return["status_code"]      = 200;
			$return["message"]          = "Successfully Updated";

		}
		else if(Request::input('current_password') == $response->user_password)
		{
			$update["password"]			= Hash::make(Request::input('new_password'));
			$update["crypt"]			= Crypt::encryptString(Request::input('new_password'));
			Users::where('id',Request::user()->id)->update($update);
			$return["status"]           = "success";
			$return["status_code"]      = 200;
			$return["message"]          = "Successfully Updated";

		}
		else
		{
			$return["status"]           = "error";
			$return["status_code"]      = 201;
			$return["message"]          = "Wrong Current Password";

		}
		return response()->json($return);
	}
	public function update_changes()
	{
		if(Request::input('header')=="email_confirm"||Request::input('header')=="email")
		{
			$count = Users::where('email',Request::input('email'))->count();

			if($count==0)
			{
				$update["email"]			= Request::input('email');
				Users::where('id',Request::user()->id)->update($update);

				$message['message'] = "EMAIL SUCCESSFULL UPDATED!";
				$message['status'] = "success";
			}
			else
			{
				$message['message'] = "EMAIL ALREADY EXIST!";
				$message['status'] = "error";
			}
		}
		else
		{
			$update["contact"]			= Request::input('contact');
			Users::where('id',Request::user()->id)->update($update);
			$message['message'] = "PHONE SUCCESSFULL UPDATED!";
			$message['status'] = "success";
		}

		return response()->json($message,200);
	}
	public function add_tin()
	{
		$data["tin"] = Request::input('add_tin');
		$i = 0;
		$rules["tin"] = "required";

		$validator = Validator::make($data, $rules);
		if($validator->fails())
		{
			$message["status"] = "error";

			$len = count($validator->errors()->getMessages());
			foreach ($validator->errors()->getMessages() as $key => $value)
			{
				foreach($value as $val)
				{
					$message["message"][$i] = $val;
				    $i++;
				}
			}
		}
		else
		{
			$insert_tin["user_id"] 			= Request::user()->id;
			$insert_tin["tin"]				= $data["tin"];
			$insert_tin["tin_date_change"] 	= Carbon::now();
			Tbl_tin_logs::insert($insert_tin);
			$update['tin'] = $data["tin"];
			Users::where('id',Request::user()->id)->update($update);
			$message['message'][$i] = "TIN SUCCESFULLY ADDED";
			$message['status'] = "success";
		}
		return response()->json($message,200);
	}
	public function edit_tin()
	{
		$data["tin"] = Request::input('edit_tin');
		$rules["tin"] = "required";
		$i = 0;
		$validator = Validator::make($data, $rules);
		if($validator->fails())
		{
			$message["status"] = "error";
			$len = count($validator->errors()->getMessages());
			foreach ($validator->errors()->getMessages() as $key => $value)
			{
				foreach($value as $val)
				{
					$message["message"][$i] = $val;
				    $i++;
				}
			}
		}
		else
		{
			$insert_tin["user_id"] 			= Request::user()->id;
			$insert_tin["tin"]				=  $data["tin"];
			$insert_tin["tin_date_change"] 	= Carbon::now();
			Tbl_tin_logs::insert($insert_tin);
			$update['tin'] =  $data["tin"];
			Users::where('id',Request::user()->id)->update($update);
			$message['message'][$i] = "TIN SUCCESFULLY UPDATED";
			$message['status'] = "success";
		}

		return response()->json($message,200);
	}
	public function what_show()
    {
        $return = Tbl_genealogy_settings::first();
        if(!$return)
        {
            $update['show_full_name']       = 1;
            $update['show_slot_no']         = 1;
            $update['show_date_joined']     = 1;
            $update['show_directs_no']      = 1;
            $update['show_binary_points']   = 1;
            $update['show_maintenance_pv']  = 1;
            $update['show_sponsor_username']  = 1;
            Tbl_genealogy_settings::insert($update);
        }
		$return2 = Tbl_genealogy_settings::first();
        return $return2;
	}
	public static function count_child($placement_parent_id)
    {
        $count['left']      = Tbl_tree_placement::where('placement_parent_id',$placement_parent_id)->where('placement_position','LEFT')->count();
        $count['right']     = Tbl_tree_placement::where('placement_parent_id',$placement_parent_id)->where('placement_position','RIGHT')->count();
        return $count;
    }

    public static function count_board_child($placement_parent_id, $board_level = 1)
    {
        $count['left']      = Tbl_mlm_board_placement::where('placement_parent_id',$placement_parent_id)->where('board_level', $board_level)->where('placement_position','LEFT')->count();
        $count['right']     = Tbl_mlm_board_placement::where('placement_parent_id',$placement_parent_id)->where('board_level', $board_level)->where('placement_position','RIGHT')->count();
        return $count;
    }
	public function check_address()
	{
		return Tbl_address::where('user_id',Request::user()->id)->where('archived',0)->where('is_default',1)->count();
	}
	public function kyc_front_id(Request2 $request)
	{
		$file = $request->file('file');
		$path_prefix = 'https://s3.us-west-000.backblazeb2.com/';
		$path = "mlm/valid_id";
		$storage_path = storage_path();

		if ($file->isValid())
		{
			$full_path = Storage::disk('s3')->putFile($path, $file, "public");
			$url = Storage::disk('s3')->url($full_path);

			$update['front_id'] = $url;
			Users::where('id',Request::user()->id)->update($update);
			$response['status'] = "success";
			$response['status_message'] = "Front ID is Successfully Uploaded!";
		}

		$status_count = 0;
		$check_user = Users::where('id',Request::user()->id)->first();

		if($check_user->front_id != '../../../assets/admin/img/noimage.png')
		{
			$status_count++;
		}
		if($check_user->back_id != '../../../assets/admin/img/noimage.png')
		{
			$status_count++;
		}
		if($check_user->selfie_id != '../../../assets/admin/img/noimage.png')
		{
			$status_count++;
		}
		if($status_count == 3)
		{
			/*  0 = No valid id
				1 = Verified
				2 = Waiting for Approval
				3 = Rejected
			*/
			$update['verified'] = 2;
			Users::where('id',Request::user()->id)->update($update);

			$response['status'] = "success";
			$response['status_message'] = "Valid ID successfully uploaded. Please wait for admin approval!";
		}
		return 	response()->json($response);

	}
	public function kyc_back_id(Request2 $request)
	{
		$file = $request->file('file');
		$path_prefix = 'https://s3.us-west-000.backblazeb2.com/';
		$path = "mlm/valid_id";
		$storage_path = storage_path();

		if ($file->isValid())
		{
			$full_path = Storage::disk('s3')->putFile($path, $file, "public");
			$url = Storage::disk('s3')->url($full_path);

			$update['back_id'] = $url;
			Users::where('id',Request::user()->id)->update($update);
			$response['status'] = "success";
			$response['status_message'] = "Back ID is Successfully Uploaded!";
		}

		$status_count = 0;
		$check_user = Users::where('id',Request::user()->id)->first();

		if($check_user->front_id != '../../../assets/admin/img/noimage.png')
		{
			$status_count++;
		}
		if($check_user->back_id != '../../../assets/admin/img/noimage.png')
		{
			$status_count++;
		}
		if($check_user->selfie_id != '../../../assets/admin/img/noimage.png')
		{
			$status_count++;
		}
		if($status_count == 3)
		{
			/*  0 = No valid id
				1 = Verified
				2 = Waiting for Approval
				3 = Rejected
			*/
			$update['verified'] = 2;
			Users::where('id',Request::user()->id)->update($update);

			$response['status'] = "success";
			$response['status_message'] = "Valid ID successfully uploaded. Please wait for admin approval!";
		}
		return 	response()->json($response);

	}
	public function kyc_selfie_id(Request2 $request)
	{
		$file = $request->file('file');
		$path_prefix = 'https://s3.us-west-000.backblazeb2.com/';
		$path = "mlm/valid_id";
		$storage_path = storage_path();

		if ($file->isValid())
		{
			$full_path = Storage::disk('s3')->putFile($path, $file, "public");
			$url = Storage::disk('s3')->url($full_path);

			$update['selfie_id'] = $url;
			Users::where('id',Request::user()->id)->update($update);
			$response['status'] = "success";
			$response['status_message'] = "Selfie with ID is Successfully Uploaded!";
		}

		$status_count = 0;
		$check_user = Users::where('id',Request::user()->id)->first();

		if($check_user->front_id != '../../../assets/admin/img/noimage.png')
		{
			$status_count++;
		}
		if($check_user->back_id != '../../../assets/admin/img/noimage.png')
		{
			$status_count++;
		}
		if($check_user->selfie_id != '../../../assets/admin/img/noimage.png')
		{
			$status_count++;
		}
		if($status_count == 3)
		{
			/*  0 = No valid id
				1 = Verified
				2 = Waiting for Approval
				3 = Rejected
			*/
			$update['verified'] = 2;
			Users::where('id',Request::user()->id)->update($update);

			$response['status'] = "success";
			$response['status_message'] = "Valid ID successfully uploaded. Please wait for admin approval!";
		}
		return 	response()->json($response);

	}
	public function remove_id()
	{
		$ref 								= Request::input('ref');

		if($ref == 'front_id')
		{
			$update['front_id'] 			= '../../../assets/admin/img/noimage.png';
			$response['status_message'] 	= "Front ID is successfully remove. Please upload new Front ID!";
		}
		elseif($ref == 'back_id')
		{
			$update['back_id'] 				= '../../../assets/admin/img/noimage.png';
			$response['status_message'] 	= "Back ID is successfully remove. Please upload new Back ID!";
		}
		else
		{
			$update['selfie_id'] 			= '../../../assets/admin/img/noimage.png';
			$response['status_message'] 	= "Selfie with ID is successfully remove. Please upload new Selfie with ID!";
		}
		$update['verified']					= 0;
		Users::where('id',Request::user()->id)->update($update);

		return $response;
	}
	public function update_beneficiary()
	{
		$data									= Request::input();
		$user_id								= Request::user()->id;
		

		if(!isset($data["beneficiary_first_name"]))
		{
			$data["beneficiary_first_name"] 	= null;
		}
		if(!isset($data["beneficiary_middle_name"]))
		{
			$data["beneficiary_middle_name"] 	= null;
		}if(!isset($data["beneficiary_last_name"]))
		{
			$data["beneficiary_last_name"] 		= null;
		}
		if(!isset($data["beneficiary_contact"]))
		{
			$data["beneficiary_contact"] 		= null;
		}

		$rules["beneficiary_first_name"]    	= "required|regex:/^[a-zA-Z0-9 -.]*$/";
		$rules["beneficiary_middle_name"]    	= "required|regex:/^[a-zA-Z0-9 -.]*$/";
		$rules["beneficiary_last_name"]    		= "required|regex:/^[a-zA-Z0-9 -.]*$/";

		if(Tbl_beneficiary::where('user_id',$user_id)->count() > 0)
		{
			$rules2["beneficiary_contact"]			= "required|unique:users,contact|regex:/^[0-9]*$/|size:11";
		}
		else
		{
			$rules2["beneficiary_contact"]			= "required|unique:tbl_beneficiary,beneficiary_contact|unique:users,contact|regex:/^[0-9]*$/|size:11";
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

		$validator = Validator::make($data, $rules, $messages);
		$validator2 = Validator::make($data, $rules2, $messages2);

		if ($validator->fails() || $validator2->fails())
		{
			$return["status"]         			= "error";
			$return["status_code"]    			= 400;
			$return["status_message"] 			= [];

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
		else
		{
			DB::table('tbl_beneficiary')->updateOrInsert(
				[
					'user_id'										=> $user_id,
				],
				[
					'user_id'										=> $user_id,
					'beneficiary_name'								=> $data['beneficiary_first_name']." ".$data['beneficiary_middle_name']." ".$data['beneficiary_last_name'],
					'beneficiary_first_name'						=> $data['beneficiary_first_name'],
					'beneficiary_middle_name'						=> $data['beneficiary_middle_name'],
					'beneficiary_last_name'							=> $data['beneficiary_last_name'],
					'beneficiary_contact'							=> $data['beneficiary_contact'],
				]);

			$return["status"]         					= "success";
			$return["status_code"]    					= 200;
			$return["status_message"] 					= "Beneficiary information is successfully updated.";
		}

		return $return;
	}

	public function close_welcome_bonus_notif()
    {
		$slot = Tbl_slot::where("slot_id", Request::input('slot_id'))->first();
		if($slot->welcome_bonus_notif) {
			$update["welcome_bonus_notif"] = 0;
			Tbl_slot::where("slot_id", $slot->slot_id)->update($update);
		}
	}

	
}
