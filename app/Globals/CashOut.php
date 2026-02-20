<?php
namespace App\Globals;

use DB;
use Carbon\Carbon;
use Request;
use App\Models\Tbl_cash_out_list;
use App\Models\Tbl_cash_out_method;
use App\Models\Tbl_cash_out_schedule;
use App\Models\Tbl_cash_out_settings;
use App\Models\Tbl_cash_out_settings_per_date;
use App\Models\Tbl_cash_out_settings_per_day;
use App\Models\Tbl_slot;
use App\Models\Tbl_other_settings;
use App\Models\Tbl_currency;
use App\Models\Tbl_service_charge;
use App\Models\Users;
use App\Globals\Log;
use App\Globals\CashIn;
use App\Globals\Audit_trail;
use App\Globals\User_process;
use App\Models\Tbl_wallet_log;

use Validator;
use Hash;
class CashOut
{
	public static function cashout_settings()
	{
		$date 	= date('d');
		$day  	= date('l', strtotime(date('Y-m-d')));

		$return = Tbl_cash_out_settings::first();
		
		if($return->cash_out_settings_per_day == 0)
		{
			$is_active = Tbl_cash_out_settings_per_day::where('cash_out_settings_day',$day)->value('day_archived');
		}
		else
		{
			$is_active = Tbl_cash_out_settings_per_date::where('cash_out_settings_date',$date)->value('date_archived');
		}
		return $is_active;

	}

	public static function get_settings()
	{
		$get = Tbl_cash_out_settings::first();

		$get->per_day  	= Tbl_cash_out_settings_per_day::get();
		$get->per_date  = Tbl_cash_out_settings_per_date::get();
		return $get;
	}

	public static function update_settings($data)
	{
		$user = Request::user()->id;
		$action = "Update Cashout Setting";
		$old_value['cashout_setting'] = Tbl_cash_out_settings::where('cash_out_settings_id',$data['cash_out_settings_id'])->first();
		$update_parent['cash_out_settings_per_day']  	=  $data['cash_out_settings_per_day'];
		$update_parent['cash_out_settings_per_date']  	=  $data['cash_out_settings_per_date'];
		$update_parent['kyc_required']  	=  $data['kyc_required'];
		
		Tbl_cash_out_settings::where('cash_out_settings_id',$data['cash_out_settings_id'])->update($update_parent);
		$new_value['cashout_setting'] = Tbl_cash_out_settings::where('cash_out_settings_id',$data['cash_out_settings_id'])->first();

		$old_value['per_day']   = Tbl_cash_out_settings_per_day::get();
		$old_value['per_date']   = Tbl_cash_out_settings_per_date::get();
		foreach ($data['per_day'] as $key => $value) 
		{
			Tbl_cash_out_settings_per_day::where('cash_out_settings_per_day_id',$value['cash_out_settings_per_day_id'])->update($value);
		}
		foreach ($data['per_date'] as $key => $value) 
		{
			Tbl_cash_out_settings_per_date::where('cash_out_settings_per_date_id',$value['cash_out_settings_per_date_id'])->update($value);
		}

		$new_value['per_day']   = Tbl_cash_out_settings_per_day::get();
		$new_value['per_date']   = Tbl_cash_out_settings_per_date::get();
		Audit_trail::audit(serialize($old_value),serialize($new_value),$user,$action);

		return Self::get_settings();
	}
	public static function get_method_list($category = null, $currency = null, $except_archive = null)
	{
		$data = Tbl_cash_out_method::where("cash_out_method_id", "!=", 0);

		if($except_archive)
		{
			$data = $data->where("is_archived", 0);
		}	

		if($category && $category != "all")
		{
			$data = $data->where("cash_out_method_category", $category);
		}

		if($currency && $currency != "all")
		{
			$data = $data->where("cash_out_method_currency", $currency);
		}

		return $data->get();
	}

	public static function add_new_method($params = null)
	{
		$user    	= Request::user()->id;
		if($params)
		{
			$rules["cash_out_method_thumbnail"] = "required";
			$rules["cash_out_method_name"] 		= "unique:tbl_cash_out_method|required";
			$rules["cash_out_proc"] 			= "unique:tbl_cash_out_method,cash_out_proc|required";

			$validator = Validator::make($params, $rules);

			if ($validator->fails()) 
	        {
	            $return["status"]         = "error"; 
				$return["status_code"]    = 400; 
				$return["status_message"] = [];

				$i = 0;
				$len = count($validator->errors()->getMessages());

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
				$new_id = Tbl_cash_out_method::insertGetId($params);
				$action = "Add Cashout Method";
				$new_value 	= Tbl_cash_out_method::where("cash_out_method_id", $new_id)->first();
				Audit_trail::audit(null,serialize($new_value),$user,$action);
				$return["status_message"] = "Method Succesfully Added!";
				$return["status_code"]    = 200; 
				$return["status"] = "success";
	        }
			
		}
		else
		{
			$return["status_message"] = "Oops! Something went wrong!";
			$return["status_code"]    = 500; 
			$return["status"] = "error";
		}

		return $return;
	}

	public static function update_method($params = null)
	{
		if($params)
		{
			$user    	= Request::user()->id;
			$action  	= "Update Cashout Method";
			$old_value 	= Tbl_cash_out_method::where("cash_out_method_id", $params["cash_out_method_id"])->first();
			
			Tbl_cash_out_method::where("cash_out_method_id", $params["cash_out_method_id"])->update($params);

			$new_value 	= Tbl_cash_out_method::where("cash_out_method_id", $params["cash_out_method_id"])->first();
			Audit_trail::audit(serialize($old_value),serialize($new_value),$user,$action);

			$return["status_message"] = "Method Succesfully Updated!";
			$return["status"] = "success";
		}
		else
		{
			$return["status_message"] = "Oops! Something went wrong!";
			$return["status"] = "error";
		}

		return $return;
	}

	public static function archive_method($id = null, $archive = null)
	{
		if($id)
		{
			if($archive == 1)
			{
				$action  	= "Archived Cashout Method";
			}
			else
			{
				$action  	= "Unarchived Cashout Method";
			}
			$user    	= Request::user()->id;
			
			$old_value 	= Tbl_cash_out_method::where("cash_out_method_id", $id)->first();

			Tbl_cash_out_method::where("cash_out_method_id", $id)->update(["is_archived"=>$archive]);

			$new_value 	= Tbl_cash_out_method::where("cash_out_method_id", $id)->first();
			Audit_trail::audit(serialize($old_value),serialize($new_value),$user,$action);
			$return["status_message"] = $archive == 1 ? "Method Succesfully Archived!" : "Method Succesfully Unarchived!";
			$return["status"] = "success";
		}
		else
		{
			$return["status_message"] = "Oops! Something went wrong!";
			$return["status"] = "error";
		}

		return $return;
	}

	public static function check_schedule($params)
	{
		if($params)
		{
			$last_payout = Tbl_cash_out_schedule::orderBy("schedule_date_to", "desc")->first();

			if($last_payout)
			{
				$date_from_input 	= date('m/d/Y', strtotime($params["schedule_date_from"]));
				$date_last_payout 	= date('m/d/Y', strtotime($last_payout->schedule_date_to));

				if($date_from_input > $date_last_payout)
				{
					$return["status"] = "success";
					$return["status_message"] =  "Date is available.";
				}
				else
				{
					$return["status"] = "error";
					$return["status_message"] =  "Starting date cannot be on or before the last payout date!";
				}
			}
			else
			{
				$return["status"] = "success";
				$return["status_message"] =  "Date is available.";
			}
		}
		else
		{
			$return["status"] = "error";
			$return["status_message"] =  "Params cannot be blank";
		}

		return $return;
	}

	public static function check_schedule_details($params)
	{
		if($params)
		{
			$details = Tbl_cash_out_list::whereDate("cash_out_date", ">=", $params["schedule_date_from"])->whereDate("cash_out_date", "<=", $params["schedule_date_to"]);
			$return["transactions"] = $details->count();
			$return["status"] = "success";
			$return["status_message"] =  "Success";
		}
		else
		{
			$return["status"] = "error";
			$return["status_message"] =  "Params cannot be blank";
		}

		return $return;
	}

	public static function get_processing_transaction($slot_owner)
	{
		$data = [];
		$transactions = Self::get_transactions(null, $slot_owner);
		foreach ($transactions as $key => $value) 
		{
			if($value->cash_out_status == "processing")
			{
				$data = Tbl_cash_out_schedule::whereDate("schedule_date_from", "<=", $value->cash_out_date)->whereDate("schedule_date_to", ">=", $value->cash_out_date)->first();
				return $data;
			}
		}
	}

	public static function get_transactions($params = null, $slot_owner = null,$paginate = null,$place = null)
	{
		// dd($slot_owner);
		$data  = Tbl_cash_out_list::method();
		//filter all_slot
		if($place == 'member_cashout')
		{
			$owner_id    = Tbl_slot::where("slot_id", $slot_owner)->Owner()->first()->slot_owner;
			$owner_slots = Tbl_slot::where("slot_owner",$owner_id)->pluck('slot_no');
			$slot        = Tbl_cash_out_list::whereIn('cash_out_slot_code',$owner_slots)->where("cash_out_type",'=',"all_slot")
						   ->where(function($slot){
							$slot->where("cash_out_status", "pending")
								->orWhere("cash_out_status", "processing");
						   })->first();
		
			if($slot)
			{
				$slot_owner = Tbl_slot::where("slot_no", $slot->cash_out_slot_code)->first()->slot_id;
			}
		}
		//single member
		if($slot_owner)
		{
			$slot_info = Tbl_slot::where("slot_id", $slot_owner)->first();
			if($slot_info)
			{
				$data = $data->where("cash_out_slot_code", $slot_info->slot_no);
			}
		}
		
		//filter by cash in status
		if(isset($params["cash_out_status"]) && $params["cash_out_status"] != "all")
		{
			if($params["cash_out_status"] == "pending/processing")
			{
				$data = $data->where(function ($query)
				{
					$query->where("cash_out_status", "pending")->orWhere("cash_out_status", "processing");
				});
			}
			else
			{
				$data = $data->where("cash_out_status", $params["cash_out_status"]);
			}
		}
		//filter by slot code or slot owner
		if(isset($params["cash_out_owner"]) && $params["cash_out_owner"] != null)
		{
			$owner = $params["cash_out_owner"];
			$data = $data->where(function($query) use ($owner)
				{
					$query->where("cash_out_slot_code", "like", "%".$owner."%")
						  ->orWhere("cash_out_member_name", "like", "%".$owner."%");
				});
		}
		//filter by method
		if(isset($params["cash_out_method_id"]) && $params["cash_out_method_id"] != "all")
		{
			$data = $data->where("tbl_cash_out_proofs.cash_out_method_id", $params["cash_out_method_id"]);
		}
		//filter by currency
		if(isset($params["cash_out_currency"]) && $params["cash_out_currency"] != "all")
		{
			$data = $data->where("cash_out_currency", $params["cash_out_currency"]);
		}
		//filter by cash in date from
		if(isset($params["cash_out_date_from"]) && $params["cash_out_date_from"] != "all")
		{
			$data = $data->whereDate("cash_out_date", ">=", $params["cash_out_date_from"]);
		}
		//filter by cash in date to
		if(isset($params["cash_out_date_to"]) && $params["cash_out_date_to"] != "all")
		{
			$data = $data->whereDate("cash_out_date", "<=", $params["cash_out_date_to"]);
		}
		if($paginate)
		{
			$data = $data->orderBy('cash_out_id', 'desc')->paginate(10);
		}
		if(!$paginate)
		{
			$data = $data->orderBy('cash_out_id', 'desc')->get();
		}
		
		return $data;
	}

	public static function record_cash_out($params,$type = "request")
	{
		$currency = Tbl_currency::where("currency_abbreviation", strtoupper($params["cash_out_method_currency"]))->first();
		$slot_info = Tbl_slot::owner()->wallet($currency->currency_id)->where("tbl_slot.slot_id", $params["slot_id"])->first();
		$check_if_exists  = Tbl_cash_out_list::where("cash_out_slot_code", $slot_info->slot_no)->whereIn("cash_out_status",["pending","processing"])->first();
		if(!$check_if_exists) {
			if($type == "request")
			{
				$i = 0;
				$additional_charges = [];
				$tin_enabled = Tbl_other_settings::where('key','tin_settings')->first() ? Tbl_other_settings::where('key','tin_settings')->first()->value : 0 ;
				// $_team_name = Request::user();
				// if($_team_name->team_name == null)
				// {
				// 	$return["status"] = "error";
				// 	$return["status_message"][$i] = "You Have no Team Name go to account settings and add a team name";
				// 	$i++;
				// }
				// else {
	
					if($tin_enabled == 1)
					{
						$edited = $params["edited"] == false ? $params["edited"] : true;
						$TIN = Request::user()->tin;
						if (!$TIN) 
						{
							$return["status"] = "warning";
							$return["ref"]    = "add"; 
							$return["status_message"][$i] = "Add Your Taxpayer Identification Number";
							$i++;
						}
						if($TIN != null && $edited == false)
						{
							$return["status"] = "warning";
							$return["ref"]    = "edit";
							$i++;
						}
					}
					if($i == 0)
					{
						$owner_id = Request::user()->id;
						$result   = User_process::check($owner_id);
						if($result == 0)
						{
			
							$messages = [
								'regex' 		=> 'Invalid Details!',
								'email' 		=> 'Email Address invalid',
								// 'numeric' 		=> 'Invalid contact number',
								'size' 			=> 'Invalid contact number',
							];
				
							// $rules["cash_out_primary_info"]    	= "required|regex:/([A-Za-z0-9 ])+/";
							// $rules["cash_out_email_address"]    = "required|email";
							// $rules["cash_out_contact_number"]   = "required|regex:/^[0-9]*$/|size:11";
							// $rules["cash_out_secondary_info"]   = "required|regex:/([A-Za-z0-9 #])+/";
							$rules["cash_out_primary_info"]    	= "regex:/([A-Za-z0-9 ])+/";
							$rules["cash_out_email_address"]    = "email";
							$rules["cash_out_contact_number"]   = "regex:/^[0-9]*$/|size:11";
							if($params["cash_out_method_category"] != 'remittance') {
								$rules["cash_out_secondary_info"]   = "regex:/([A-Za-z0-9 #])+/";
							}
							$validator = Validator::make($params, $rules, $messages);
							$i = 0;
							if($validator->fails()) 
							{
								$return["status"]         = "error"; 
								$return["status_code"]    = 400; 
								$return["status_message"] = [];
				
								
								$len = count($validator->errors()->getMessages());
				
								foreach ($validator->errors()->getMessages() as $key => $value) 
								{
									$return["status_message"][$i] = $value;
									$i++;
								}
							}
							else 
							{
								if(isset($params["slot_id"]))
								{	
									$currency = Tbl_currency::where("currency_abbreviation", strtoupper($params["cash_out_method_currency"]))->first();
									$method_data = Tbl_cash_out_method::where("cash_out_method_id",$params["cash_out_method_id"])->first();
									$slot_info = Tbl_slot::owner()->wallet($currency->currency_id)->where("tbl_slot.slot_id", $params["slot_id"])->first();
									// dd($slot_info,$params);
									if($slot_info)
									{
										if($slot_info->initial_payout == 1)
										{
											$minimum_encashment = $method_data->initial_payout;
											// $additional_charges['survey_charge'] = $method_data->survey_charge;
										}
										else
										{
											$minimum_encashment = $method_data->minimum_payout;
		
										}
										
										if($params['type'] == 'current_slot')
										{
											$amount = $slot_info->wallet_amount;
										}
										else 
										{
											$_slot_checker = Self::all_slot_checker($slot_info->slot_id,$params);
											$amount = $_slot_checker['total'];
											$_pass_slots =  $_slot_checker['slots'];
											// dd($amount);
										}
										
										if($params["total_due"] > $amount)
										{
											$return["status"] = "error";
											$return["status_message"][$i] = "You do not have enough wallet balance to pay for your total due.";
											$i++;
										}
										elseif($params["cash_out_amount"] <= 0)
										{
											$return["status"] = "error";
											$return["status_message"][$i] = "Requested amount cannot be 0 or less.";
											$i++;
										}
										elseif($params["cash_out_amount"] < $minimum_encashment)
										{
											$return["status"] = "error";
											$return["status_message"][$i] = "Requested amount should be equal or more than ". $minimum_encashment;
											$i++;
										}
										else
										{
											if($params['type'] == 'current_slot')
											{
												$check_pending    = Tbl_cash_out_list::where("cash_out_slot_code", $slot_info->slot_no)
																	 ->where(function($check_pending) {
																		$check_pending->where("cash_out_status", "pending")
																					  ->orWhere("cash_out_status", "processing");
																	 })->first();
												
																	// ->where("cash_out_status", "pending")->orwhere("cash_out_status", "processing")->first();
												$owner_slots 	  = Tbl_slot::where("slot_owner",$slot_info->slot_owner)->pluck('slot_no');
												$check_pending_2  = Tbl_cash_out_list::whereIn('cash_out_slot_code',$owner_slots)->where("cash_out_type","all_slot")->where("cash_out_status", "pending")->orWhere("cash_out_status", "processing")->first();
											}
											else 
											{
												$check_pending 		= null;
												$check_pending_2 	= null;
											}
											if($params['type'] == 'current_slot')
											{
												$check_value   = Self::double_check_computation($params["cash_out_method_id"],$params["cash_out_amount"]);
											}
											else 
											{
												$additional_charges['survey_charge'] = $method_data->survey_charge;
												$additional_charges['product_charge'] = $method_data->product_charge;
												$additional_charges['gc_charge'] = $method_data->gc_charge;
												$multiplier_fee     = Self::fix_multiplier($params["cash_out_amount"],$params['all_slot']);
												$check_value   = Self::double_check_computation($params["cash_out_method_id"],$params["cash_out_amount"],$multiplier_fee,$additional_charges,$params['all_slot']);										
											}
											// dd($multiplier_fee );
											// dd($check_value["expected_receivable"] , $params["expected_receivable"])
											// $check_pending = Tbl_cash_out_list::where("cash_out_slot_code", $slot_info->slot_no)->where("cash_out_status", "pending")->first();
											// $check_value   = Self::double_check_computation($params["cash_out_method_id"],$params["cash_out_amount"]);
											// dd( $check_value["cash_out_amount"] ,$params["cash_out_amount"], $check_value["cash_out_method_method_fee"] , $params["cash_out_method_method_fee"], $check_value["cash_out_method_withholding_tax"] , $params["cash_out_method_withholding_tax"] , $check_value["service_charge"] , $params["service_charge"], $check_value["expected_receivable"] , $params["expected_receivable"], $check_value["total_due"] , $params["total_due"] );
											if($check_value["cash_out_amount"] == $params["cash_out_amount"] &&  $check_value["cash_out_method_method_fee"] == $params["cash_out_method_method_fee"] && $check_value["cash_out_method_withholding_tax"] == $params["cash_out_method_withholding_tax"] && $check_value["service_charge"] == $params["service_charge"] && $check_value["expected_receivable"] == $params["expected_receivable"] && $check_value["total_due"] == $params["total_due"])
											{
												if(!$check_pending)
												{
													$transaction = Tbl_cash_out_method::where("cash_out_method_id", $params["cash_out_method_id"])->first();
													$currency_id = Tbl_currency::where("currency_abbreviation",$transaction->cash_out_method_currency)->first() ? Tbl_currency::where("currency_abbreviation",$transaction->cash_out_method_currency)->first()->currency_id : 0;
													$insert["cash_out_name"] 						= $slot_info->name;
													$insert["cash_out_slot_code"] 					= $slot_info->slot_no;
													$insert["cash_out_method_id"] 					= $params["cash_out_method_id"];
													$insert["cash_out_primary_info"] 				= $params["cash_out_primary_info"];
													$insert["cash_out_secondary_info"] 				= $params["cash_out_secondary_info"];
													$insert["cash_out_optional_info"] 				= $params["cash_out_optional_info"];
													$insert["cash_out_email_address"] 				= $params["cash_out_email_address"];
													$insert["cash_out_contact_number"] 				= $params["cash_out_contact_number"];
													$insert["cash_out_tin"] 						= $slot_info->tin;
													$insert["cash_out_currency"] 					= $params["cash_out_method_currency"];
													$insert["cash_out_amount_requested"] 			= $params["cash_out_amount"];
													$insert["cash_out_method_fee"] 					= $params["cash_out_method_method_fee"];
													$insert["cash_out_method_tax"] 					= $params["cash_out_method_withholding_tax"];
													$insert["cash_out_method_service_charge"] 		= $params["service_charge"];
													$insert["cash_out_net_payout"] 					= $params["expected_receivable"];
													$insert["cash_out_net_payout_actual"] 			= $params["total_due"];
													$insert["cash_out_original_amount_deducted"]	= $params["total_due"];
													$insert["cash_out_savings"]						= $params["savings_amount"];
													$insert["cash_out_method_message"] 				= null;
													$insert["cash_out_type"] 			        	= $params['type'];
													$insert["cash_out_date"] 						= Carbon::now('Asia/Manila');
													$insert["gc_charge"] 							= $params["gc_charge"];
													$insert["survey_charge"] 						= $params["survey_charge"] * Self::survey_multiplier($params["all_slot"]);
													$insert["product_charge"] 						= $params["product_charge"] * $multiplier_fee;
													$insert['txnid'] 								= "PAYOUT".time();
													$id = Tbl_cash_out_list::insertGetId($insert);
													// Log::insert_wallet($slot_info->slot_id, $params["total_due"] * -1 , 'CASH OUT', $currency_id, $id);
													
													// dd($params["total_due"]);
													if($params['type'] == 'current_slot')
													{
														Log::insert_wallet($slot_info->slot_id, $params["total_due"] * -1 , 'CASH OUT', $currency_id, $id);
													}
													else 
													{
														$diff = $params["total_due"];
														// $_pass_slots 
														foreach ($params["all_slot"] as $key => $slot) 
														{
															if(isset($slot['cash_out_amount'])){
																Log::insert_wallet($slot['slot_id'], $params["total_due"] * -1 , 'CASH OUT', $currency_id, $id);
															}
															// $last = $diff;
															// $diff = $diff - $slot['wallet_amount'];
															// if($diff > 0) 
															// {
															// 	if($slot->wallet_amount != 0)
															// 	{
															// 		Log::insert_wallet($slot['slot_id'], $slot['wallet_amount'] * -1 , 'CASH OUT', $currency_id, $id);
															// 	}
															// }
															// else 
															// {
															// 	Log::insert_wallet($slot['slot_id'], $last * -1 , 'CASH OUT', $currency_id, $id);
															// 	break;
															// }
														}
													}
													// $update['initial_payout'] = 0;
													// Tbl_slot::where('slot_id', $slot_info->slot_id)->update($update);
													$return["status"] = "success";
													$return["status_message"][$i] = "Requested Cash out added";
												}
												else 
												{
													$return["status"] = "error";
													$return["status_message"][$i] = "You have still request either pending or processing.";
												}
											}
											else 
											{	
												dd(1);
												$return["status"] = "error";
												$return["status_message"][$i] = "Oops! Something went wrong..";
											}
										}
									}
								}
								else
								{
									dd(2);								$return["status"] = "error";
									$return["status_message"][$i] = "Oops! Something went wrong.";
								}
							}
						}
						else 
						{
							dd(3);
							$return["status"]         = "error"; 
							$return["status_code"]    = 400; 
							$return["status_message"] = "Opss Something went Wrong!";
						} 
					}
				// }
				
			}
			else 
			{
				$currency = Tbl_currency::where("currency_abbreviation", strtoupper($params["cash_out_method_currency"]))->first();
				$slot_info = Tbl_slot::owner()->wallet($currency->currency_id)->where("tbl_slot.slot_id", $params["slot_id"])->first();
				if($slot_info)
				{
					$check_pending = Tbl_cash_out_list::where("cash_out_slot_code", $slot_info->slot_no)->where("cash_out_status", "pending")->first();
	
					if(!$check_pending)
					{
						$transaction = Tbl_cash_out_method::where("cash_out_method_id", $params["cash_out_method_id"])->first();
						$currency_id = Tbl_currency::where("currency_abbreviation",$transaction->cash_out_method_currency)->first() ? Tbl_currency::where("currency_abbreviation",$transaction->cash_out_method_currency)->first()->currency_id : 0;
						$insert["cash_out_name"] 					= $slot_info->name;
						$insert["cash_out_slot_code"] 				= $slot_info->slot_no;
						$insert["cash_out_method_id"] 				= $params["cash_out_method_id"];
						$insert["cash_out_primary_info"] 			= $params["cash_out_primary_info"];
						$insert["cash_out_secondary_info"] 			= $params["cash_out_secondary_info"];
						$insert["cash_out_optional_info"] 			= $params["cash_out_optional_info"];
						$insert["cash_out_email_address"] 			= $params["cash_out_email_address"];
						$insert["cash_out_contact_number"] 			= $params["cash_out_contact_number"];
						$insert["cash_out_tin"] 					= $slot_info->tin;
						$insert["cash_out_currency"] 				= $params["cash_out_method_currency"];
						$insert["cash_out_amount_requested"] 		= $params["cash_out_amount"];
						$insert["cash_out_method_fee"] 				= $params["cash_out_method_method_fee"];
						$insert["cash_out_method_tax"] 				= $params["cash_out_method_withholding_tax"];
						$insert["cash_out_method_service_charge"] 	= $params["service_charge"];
						$insert["cash_out_net_payout"] 				= $params["expected_receivable"];
						$insert["cash_out_net_payout_actual"] 		= $params["total_due"];
						$insert["cash_out_original_amount_deducted"]= $params["total_due"];
						$insert["cash_out_method_message"] 			= null;
						$insert["cash_out_date"] 					= Carbon::now('Asia/Manila');
						$id = Tbl_cash_out_list::insertGetId($insert);
						Log::insert_wallet($slot_info->slot_id, $params["total_due"] * -1 , 'CASH OUT', $currency_id, $id);
	
						$return["status"] = "success";
						$return["status_message"] = "Requested Cash out added";
					}
					
				}
			}
		} else {
			$return["status"]         = "existing"; 
			$return["status_code"]    = 400; 
			$return["status_message"] = "Sorry, we cannot proceed the request. There's ongoing cash-out request.";
		}
		
		

		return $return;
	}
	public static function survey_multiplier($all_slot)
	{
		$for_survey = 0;
			
			foreach ($all_slot as $key => $value) {
				if(isset($value['cash_out_amount']) && $value['initial_payout'] == 1){
					
					$for_survey++;
				} 
			}
		return $for_survey;
	}
	public static function all_slot_checker($slot_id,$params)
	{
		$multi=[];
        $ctr = 0;
        $multi['total'] = 0;
	    $data  =Tbl_cash_out_method::where("cash_out_method_id",$params["cash_out_method_id"])->first();
        $currency = Tbl_currency::where('currency_abbreviation',$data['cash_out_method_currency'])->first()->currency_id;

        $owner_id         = Tbl_slot::where("tbl_slot.slot_id",$slot_id)->Owner()->wallet($currency)->first();
		if($owner_id->initial_payout == 1) {

            if($owner_id->wallet_amount >= $data['initial_payout']) {

                $multi['total'] = $multi['total'] + $owner_id->wallet_amount;
                $multi['slots'][$ctr] = $owner_id;
                $ctr++;
            }
        }
        else {
            
            if($owner_id->wallet_amount >= $data['minimum_payout']) {

                $multi['total'] = $multi['total'] + $owner_id->wallet_amount;
                $multi['slots'][$ctr] = $owner_id;
                $ctr++;
            }
        }
        $slots = Tbl_slot::where("tbl_slot.slot_owner",$owner_id->slot_owner)->where("tbl_slot.slot_id",'!=',$owner_id->slot_id)->wallet($currency)->get();
        foreach ($slots as $key => $x) {
         
            if($x->initial_payout == 1) {

                if($x->wallet_amount < $data['initial_payout']) {
                    $slots->forget($key);
                }
                else {
                    
                    $multi['total'] = $multi['total'] + $x->wallet_amount;
                    $multi['slots'][$ctr] = $x;
                    $ctr++;
                }
                
            }
            else {
                
                if($x->wallet_amount < $data['minimum_payout']) {
                    $slots->forget($key);
                }
                else {
                    
                    $multi['total'] = $multi['total'] + $x->wallet_amount;
                    $multi['slots'][$ctr] = $x;
                    $ctr++;
                }
            }
		}
		return $multi;
	}
	public static function fix_multiplier($total_charge,$slots,$update_transaction = false)
	{
		$sum = 0;
		$ctr = 0;
		$multiplier = 0;
		// dd($slots)
		foreach ($slots as $key => $x) {
			$ctr = $ctr + 1;
			if(isset($x['cash_out_amount'])) {

				$multiplier++;
				// $sum = $sum + ($x->wallet_log_amount * -1);
			}
			// else {
				
			// 	$sum = $sum + $x->wallet_amount;
			// }
			// if($total_charge <= $sum) {
			// 	$multiplier = $ctr;
			// 	break;
			// }
		}
		return $multiplier;
	}
	public static function check_if_initial_payout($params){
		$initial = false;
		$currency = Tbl_currency::where("currency_abbreviation", strtoupper($params['currency']))->first();
		$slot_info = Tbl_slot::owner()->wallet($currency->currency_id)->where("tbl_slot.slot_id", $params['slot_id'])->first();
		// dd($slot_info);
		if($slot_info)
		{
			$initial = $slot_info->initial_payout == 1 ? true : false;
		}
		// dd($initial);
		return $initial;

	}
	public static function import_cash_out($data)
	{
		$payout_method = Tbl_cash_out_method::where('cash_out_method_name', "like", '%'.str_replace(' ','%',$data['method']). '%')->first();
		if($payout_method)
		{
			$slot = Tbl_slot::where('slot_no', $data['slot_no'])->first();
			$slot_owner = Users::where('id', $slot->slot_owner)->first();

			$pass['slot_id']							= $slot->slot_id;
			$pass["cash_out_method_id"] 				= $payout_method->method_id;
			$pass["cash_out_primary_info"] 				= $slot_owner->name;
			$pass["cash_out_secondary_info"] 			= $slot_owner->contact;
			$pass["cash_out_optional_info"]				= null;
			$pass["cash_out_email_address"] 			= $slot_owner->email;
			$pass["cash_out_contact_number"] 			= $slot_owner->contact;
			$pass["cash_out_currency"] 					= "USD";
			$pass["cash_out_amount"] 					= $data['total'];
			$pass["cash_out_method_method_fee"] 		= 0;
			$pass["cash_out_method_withholding_tax"] 	= $data['tax'];
			$pass["service_charge"] 					= $data['service_charge'];
			$pass["expected_receivable"] 				= $data["payout_amount"];
			$pass["cash_out_net_payout_actual"] 		= $data['total'];
			$pass["cash_out_original_amount_deducted"] 	= $data['total'];
			$pass["total_due"] 							= $data['total'];
			$pass["cash_out_method_currency"] 			= "USD";
			$pass["edited"] 			 				= true;

			$return = Self::record_cash_out($pass);
			return $return;
		}
		else
		{
			dd('payout method does not exist');
		}
	}

	public static function process_payout($data)
    {
		$checkpending = null;
		if($data["cashout_type"] == "request")
		{
			$cut_off    = Tbl_cash_out_list::where('cash_out_status','=','pending')->orderby("cash_out_date", "ASC")->first();
			$method = "buffer";
		}
		else 
		{
			$cut_off = Tbl_cash_out_schedule::orderby("schedule_date_to", "desc")->first();
			$checkpending    = Tbl_cash_out_list::where('cash_out_status','=','pending')->orderby("cash_out_date", "ASC")->first();
			$method = Tbl_cash_out_method::where("cash_out_method_name","like",'%cheque%')->where("is_archived", 0)->first();
			if($method)
			{
				if($method["cash_out_method_charge_to"] != "inclusive")
				{
					$return["status"] = "error";
					$return["status_message"] = "Your Cheque method is not  Inclusive.";
					return $return;
				}
			}
		}
		if($method)
		{
			//to avoid using two type of payout
			if(!$checkpending)
			{
				// $cut_off_range = 15;
				$cut_off_start_after_last = 1;
				$cashout_method = Request::input('cashout_method_id');
				$user           = Request::user()->id;
				if($cut_off)
				{
					// update all transactions for latest schedule;
					// $prev_transaction   = Self::get_transactions_for_payout($cut_off->schedule_date_from, $cut_off->schedule_date_to);
					// Self::update_latest_schedule($prev_transaction);
					// create new schedule;
					if($data["cashout_type"] == "request")
					{
						$start_date = date('Y-m-d H:i:s', strtotime($cut_off->cash_out_date));
					}
					else 
					{
						$start_date = date('Y-m-d H:i:s', strtotime($cut_off->schedule_date_to . ' + '.$cut_off_start_after_last.' seconds'));
					}
					$end_date   = Carbon::now()->format('Y-m-d H:i:s');
					Self::create_new_schedule($start_date, $end_date,$cashout_method,$data["cashout_type"]);
					
					$new_value = Tbl_cash_out_schedule::where('schedule_date_from',$start_date)->where('schedule_date_to',$end_date)->first();
					$action = "Create New Schedule";
					Audit_trail::audit(null,serialize($new_value),$user,$action);
		
		
					$return["status"] 		  = "success";
					$return["status_message"] = "Successfully created a new cut-off schedule and scheduled previous one for processing";
				}
				else
				{
					$start_date = Carbon::now()->subYear();
					// $start_date = date('Y-m-d H:i:s', strtotime($cut_off->schedule_date_to . ' + '.$cut_off_start_after_last.' seconds'));
					$end_date   = Carbon::now();
					
					Self::create_new_schedule($start_date,$end_date,$cashout_method,$data["cashout_type"]);
		
					$new_value = Tbl_cash_out_schedule::where('schedule_date_from',$start_date)->where('schedule_date_to',$end_date)->first();
					$action = "Create New Schedule";
					Audit_trail::audit(null,serialize($new_value),$user,$action);
		
					$return["status"] = "success";
					$return["status_message"] = "Successfully created a cut-off schedule.";
				}
			}
			else
			{
				$return["status"] = "error";
				$return["status_message"] = "There is pending request, process it first before proceeding to this payout";
			}
		}
		else 
		{
			$return["status"] = "error";
			$return["status_message"] = "There is no Cheque method.";
		}
        return $return;
    }
    
    public static function update_latest_schedule($update  ,$sched_id)
    {
		// dd($update);
		$user      = Request::user()->id;
		$action    = "Update Schedule";
		$old_value = Tbl_cash_out_schedule::where("schedule_id", $sched_id)->first();
		Tbl_cash_out_schedule::where("schedule_id", $sched_id)->update($update);
		$new_value = Tbl_cash_out_schedule::where("schedule_id", $sched_id)->first();
		
		Audit_trail::audit(serialize($old_value),serialize($new_value),$user,$action);
        // Tbl_cash_out_schedule::where("schedule_date_from", $update['schedule_date_from'])->where("schedule_date_to", $update['schedule_date_to'])->update($update);

        // Tbl_cash_out_list::where("cash_out_date", ">=", $update['schedule_date_from'])->where("cash_out_date", "<=", $update['schedule_date_to'])->update(["cash_out_status" => "processing"]);
    }

    public static function create_new_schedule($dt_fr, $dt_to, $method_id,$type = "request")
    {
		$insert["schedule_status"]          = "processing";
		$insert["schedule_date_from"]       = $dt_fr;
		$insert["schedule_date_to"]         = $dt_to;
		$insert["schedule_method_id"]		= $method_id;
		if($type == "members_wallet")
		{
			Self::insert_transactions_for_payout();
		}
		$totals = Self::get_transactions_for_payout($dt_fr, $dt_to,$method_id);

		$insert["total_payout_amount"]      = $totals["total_payout_amount"];
		$insert["total_payout_charge"]      = $totals["total_payout_charge"];
		$insert["total_payout_receivable"]  = $totals["total_payout_receivable"];
		$insert["total_payout_required"]    = $totals["total_payout_required"];
		$insert["date_created"]    			= Carbon::now();
		
		$get_id = Tbl_cash_out_schedule::insertGetId($insert);
		if($method_id != 0)
		{
			Tbl_cash_out_list::where("cash_out_date", ">=", $dt_fr)
								->where("cash_out_date", "<=", $dt_to)
								->where('cash_out_method_id',$method_id)
								->where('cash_out_status','=','pending')
								->where('schedule_id',null)
								->update(["cash_out_status" => "processing","schedule_id" => $get_id]);
		}
		else
		{
			Tbl_cash_out_list::where("cash_out_date", ">=", $dt_fr)
								->where("cash_out_date", "<=", $dt_to)
								->where('cash_out_status','=','pending')
								->where('schedule_id',null)
								->update(["cash_out_status" => "processing","schedule_id" => $get_id]);
		}
    }

    public static function get_transactions_for_payout($dt_fr, $dt_to, $method_id,$sched_id = 0)
    {
		if($dt_fr && $dt_to)
		{
			// dd($dt_fr, $dt_to, $method_id);
			if($sched_id == 0)
			{
				if($method_id != 0)
				{
					$transactions = Tbl_cash_out_list::where("cash_out_date", ">=", $dt_fr)
														->where("cash_out_date", "<=", $dt_to)
														->where('cash_out_method_id',$method_id)
														->where('schedule_id',null)
														->where('cash_out_status','=','pending')->get();
					//dd($transactions);
				}
				else 
				{
					$transactions = Tbl_cash_out_list::where("cash_out_date", ">=", $dt_fr)
														->where("cash_out_date", "<=", $dt_to)
														->where('cash_out_status','=','pending')
														->where('schedule_id',null)
														->get();
					// dd($transactions);
				}

			}
			else 
			{
				if($method_id != 0)
				{
					$transactions = Tbl_cash_out_list::where("cash_out_date", ">=", $dt_fr)
														->where("cash_out_date", "<=", $dt_to)
														->where('cash_out_method_id',$method_id)
														->where('schedule_id',$sched_id)
														->get();
					//dd($transactions);
				}
				else 
				{
					$transactions = Tbl_cash_out_list::where("cash_out_date", ">=", $dt_fr)
														->where("cash_out_date", "<=", $dt_to)
														->where('schedule_id',$sched_id)
														->get();
					// dd($transactions);
				}
			}
			
			

			$data["total_payout_amount"]            = 0;
			$data["total_payout_receivable"]        = 0;
			$data["total_payout_charge"]            = 0;
			$data["total_payout_required"]          = 0;
			$data["schedule_date_from"]          	= $dt_fr;
			$data["schedule_date_to"]          		= $dt_to;

			if($transactions)
			{
				for ($i=0; $i <= count($transactions) - 1; $i++) 
				{ 
					if($transactions[$i]["cash_out_amount_requested"] > 0)
					{
						$data["total_payout_amount"]			    += $transactions[$i]["cash_out_amount_requested"];
						$data["total_payout_required"]        		+= $transactions[$i]["cash_out_net_payout_actual"];
						$data["total_payout_receivable"]          	+= $transactions[$i]["cash_out_net_payout"];
						
						$tax            = $transactions[$i]["cash_out_method_tax"];
						$service        = $transactions[$i]["cash_out_method_service_charge"];
						$fee            = $transactions[$i]["cash_out_method_fee"];

						$total_charge   = $tax + $service + $fee;

						$data["total_payout_charge"] += $total_charge;
					}
				}
			}
		}
		return $data;
    }

    public static function get_schedules($params)
	{
		$date_from = isset($params['from']) ? $params['from'] : null;
		$date_to = isset($params['to']) ? $params['to'] : null;
		$date_from == "null" ? $date_from = null : $date_from = $date_from;
		$date_to == "null" ? $date_to = null : $date_to = $date_to;
		$list = Tbl_cash_out_schedule::leftJoin('tbl_cash_out_method','tbl_cash_out_method.cash_out_method_id','=','tbl_cash_out_schedule.schedule_method_id')
										->where('tbl_cash_out_schedule.is_archived',0)
										->orderBy('schedule_date_from','DESC');

		if($date_from)
		{
			$list = $list->where('schedule_date_from', ">=", $date_from);
		}
		if($date_to)
		{
			$list = $list->where('schedule_date_to', "<=", $date_to);
		}
		$total_payout_amount = $list->sum('total_payout_amount');
		$total_payout_charge = $list->sum('total_payout_charge');
		$total_payout_required = $list->sum('total_payout_required');
		$total_payout_receivable = $list->sum('total_payout_receivable');
		$list = $list->paginate(10);
		foreach ($list as $key => $value) 
		{
			if($value->schedule_method_id != 0)
			{
				$list[$key]["transactions"] = Tbl_cash_out_list::where("schedule_id", "=", $value->schedule_id)->where('cash_out_method_id',$value->schedule_method_id)->get();
			}
			else 
			{
				$list[$key]["transactions"] = Tbl_cash_out_list::where("schedule_id", "=", $value->schedule_id)->get();
			}
		}
		$return['list'] = $list;
		$return['total_payout_amount'] = $total_payout_amount;
		$return['total_payout_charge'] = $total_payout_charge;
		$return['total_payout_required'] = $total_payout_required;
		$return['total_payout_receivable'] = $total_payout_receivable;
		return $return;
	}

	public static function update_transaction($params)
	{
		// dd($params);
		$transaction = Tbl_cash_out_list::where("cash_out_id", $params["transaction"]);
		$first =  Tbl_cash_out_list::slot()->where("cash_out_id", $params["transaction"])->first();
		
		if($first)
		{
			$update["cash_out_method_message"] 					= $params["message"];
			$update["sender_name"] 								= $params["sender"];
			$update["control_number"] 							= $params["control_no"];
			$update["receipt_thumbnail"] 						= $params["thumbnail"];
			// $update["cash_out_amount_requested"] 				= $params["amount"];

			
			if($params["amount"] <= 0)
			{
				$update["cash_out_method_fee"] 						= 0;
				$update["cash_out_method_service_charge"] 			= 0;
				$update["cash_out_net_payout"] 						= 0;
				$update["cash_out_net_payout_actual"] 				= 0;
				$update["cash_out_method_tax"] 						= 0;
				$update["cash_out_amount_requested"] 				= 0;
			}
			else
			{
				$update["cash_out_amount_requested"] 				= $params["amount"];

				$fees 			= Tbl_cash_out_method::where("cash_out_method_id", $first->cash_out_method_id)->first();
				$tax 			= ($fees->cash_out_method_withholding_tax/100) * $params["amount"];
				// $service        = ($params["amount"]-$tax) * ($fees->cash_out_method_service_charge/100);
				$service        = $fees->cash_out_method_service_charge_type == 'percentage' ? (($params["amount"]-$tax) * ($fees->cash_out_method_service_charge/100)) : $fees->cash_out_method_service_charge;

				$update["cash_out_method_fee"] 						= $fees->cash_out_method_method_fee;
				$update["cash_out_method_service_charge"] 			= $service;
				$update["cash_out_method_tax"] 						= $tax;
				

				$total_charge                                       = $fees->cash_out_method_method_fee +  $tax + $service + $fees->product_charge + $fees->gc_charge;
				$fees->survey										= $first->initial_payout == 1 ?  $fees->survey_charge : 0;
				$total_charge  										= $total_charge + $fees->survey;
				$savings_percentage = $fees->savings_percentage;
				if($fees->cash_out_method_charge_to == "inclusive")
				{
					// dd($params,$total_charge);
					$update["cash_out_net_payout_actual"]           = $params["amount"];
					$expected_receivable 	                = $params["amount"] - $total_charge;
				}
				else
				{
					$update["cash_out_net_payout_actual"]           = $params["amount"] + $total_charge;
					$expected_receivable 	                = $params["amount"];
				}

				$savings 					= ($savings_percentage * $expected_receivable) / 100;
				$expected_receivable		= $expected_receivable - $savings;
				$update["cash_out_net_payout"]	= $expected_receivable;
				$update["cash_out_savings"]	= $savings;

			}
			if($update["cash_out_net_payout_actual"] <= $first->cash_out_original_amount_deducted)
			{
				$transaction 										= $transaction->update($update);
				$second =  Tbl_cash_out_list::slot()->where("cash_out_id", $params["transaction"])->first();
				$user   = Request::user()->id;
				$action = "Update Payout List";
				Audit_trail::audit(serialize($first),serialize($second),$user,$action);
				$schedule 											= Tbl_cash_out_schedule::where("schedule_id", $params["schedule"])->first();
				if($schedule)
				{
					$update_payouts = Self::get_transactions_for_payout($schedule->schedule_date_from, $schedule->schedule_date_to, $first->cash_out_method_id ,$params["schedule"]);
					Self::update_latest_schedule($update_payouts,$params["schedule"]);
				}
			}
			else 
			{
				$transaction 										= $transaction->first();
			}
		}

		return "Success";
	}

	// public static function process_transaction($id, $sched_id)
	// {
	// 	$cash_status = Tbl_cash_out_schedule::where("schedule_id", $sched_id)->first()->schedule_status;
	// 	$type_of_method = Tbl_cash_out_schedule::where("tbl_cash_out_schedule.schedule_id",$sched_id)->leftJoin("tbl_cash_out_method","tbl_cash_out_method.cash_out_method_id","=","tbl_cash_out_schedule.schedule_method_id")->first()->cash_out_method_charge_to;
	// 	if($cash_status == "processing")
	// 	{
	// 		foreach ($id as $key => $value) 
	// 		{
	// 			$transaction = Tbl_cash_out_list::slot()->where("cash_out_id", $value)
	// 											->leftJoin("tbl_cash_out_method","tbl_cash_out_method.cash_out_method_id","tbl_cash_out_list.cash_out_method_id")
	// 											->first();
	// 			if($transaction->cash_out_status == "processing")
	// 			{
	// 				$currency_id = Tbl_currency::where("currency_abbreviation",$transaction['cash_out_method_currency'])->first() ? Tbl_currency::where("currency_abbreviation",$transaction['cash_out_method_currency'])->first()->currency_id : 0;
	// 				if($transaction->cash_out_original_amount_deducted != $transaction->cash_out_net_payout_actual)
	// 				{
	// 					if($transaction->cash_out_net_payout_actual == 0)
	// 					{
	// 						$update_list['cash_out_status'] = "REJECTED";
	// 						$status_plan                    = "REJECTED";
	// 						$refund                         = $transaction->cash_out_original_amount_deducted;
	// 					}
	// 					else
	// 					{
	// 						if($type_of_method == "exclusive")
	// 						{
	// 							$refund = ($transaction->cash_out_original_amount_deducted - $transaction->cash_out_net_payout_actual);
	// 						}
	// 						else 
	// 						{
	// 							$refund = ($transaction->cash_out_original_amount_deducted - $transaction->cash_out_net_payout_actual);
	// 						}
	// 						$update_list['cash_out_status'] 					= "processed";
	// 						$status_plan                    					= "REJECTED";
	// 					}
	
	// 					Tbl_cash_out_list::where("cash_out_id", $value)->update($update_list);
	// 					Log::insert_wallet($transaction->slot_id, $refund, $status_plan, $currency_id, $transaction->cash_out_id);
	// 				}
	// 				else
	// 				{
	// 					Tbl_cash_out_list::where("cash_out_id", $value)->update(["cash_out_status" => "processed"]);
	// 				}
	// 			}
	// 		}

	// 		$return["status"] = "success";
	// 		$return["status_message"] = "Successfully processed transactions!";
	// 		$old_value = Tbl_cash_out_schedule::where("schedule_id", $sched_id)->first();
	// 		Tbl_cash_out_schedule::where("schedule_id", $sched_id)->update(["schedule_status" => "processed"]);
	// 		$new_value = Tbl_cash_out_schedule::where("schedule_id", $sched_id)->first();
	// 		$action    = "Process Payout";
	// 		$user      = Request::user()->id;
	// 		Audit_trail::audit(serialize($old_value),serialize($new_value),$user,$action);
	// 	}
	// 	else 
	// 	{
	// 		$return["status"] = "warning";
	// 		$return["status_message"] = "Something Wrong when processing this transactions!";
	// 	}

	// 	return $return;
	// }

	public static function get_actual_schedule_transactions($filter)
	{
		// $schedule = Tbl_cash_out_schedule::where("schedule_id", $sched_id)->first();
		$search      	= Request::input('search');
		$list['list'] = Tbl_cash_out_list::Method()->Slot()->where("schedule_id", $filter['id']);
		if($search != '' || $search != null )
		{
			$list['list']->where("cash_out_slot_code", "like", "%". $search . "%")->orWhere("cash_out_name", "like", "%". $search . "%");
		}
		$list['list'] = $list['list']->paginate(15);
		foreach ($list['list'] as $key => $value) {

			$slot = Tbl_slot::where("slot_no" ,$value->cash_out_slot_code)->first();
			$value->team_name = Users::where("id",$slot->slot_owner)->first()->team_name;
		}
		$list['total'] = Tbl_cash_out_list::Method()->where("schedule_id", $filter['id'])->sum('cash_out_net_payout');
		return $list;
	}

	public static function get_method_list_raw()
	{
		$data = Tbl_cash_out_method::where("cash_out_method_id", "!=", 0)
									->where("is_archived", 0)
									->select('cash_out_method_id','cash_out_method_name');
		$method['method'] = $data->get();

		return $method;
	}
	public static function update_message($params)
	{
		
		$transaction = Tbl_cash_out_list::where("cash_out_id", $params["transaction"]);
		
		$first =  Tbl_cash_out_list::slot()->where("cash_out_id", $params["transaction"])->first();
		// dd($first);
		if($first)
		{
			$update["cash_out_method_message"] 			= $params["message"];
			$update["sender_name"] 						= $params["sender"];
			$update["control_number"] 					= $params["control_no"];

			DB::table('tbl_cash_out_list')->where('cash_out_id',$first->cash_out_id)->update($update);

		}

		$second =  Tbl_cash_out_list::slot()->where("cash_out_id", $params["transaction"])->first();
		$user   = Request::user()->id;
		$action = "Update Payout Message";
		Audit_trail::audit(serialize($first),serialize($second),$user,$action); 


		return "Success";
	}
	public static function insert_transactions_for_payout()
	{
		$method = Tbl_cash_out_method::where("cash_out_method_name","like",'%cheque%')->where("cash_out_method_charge_to","inclusive")->where("is_archived",0)->first();
		$currency_id = Tbl_currency::where("currency_abbreviation",$method->cash_out_method_currency)->first()->currency_id;
		if($method)
		{
			$fix = $method->cash_out_method_method_fee;
			$percent = $method->cash_out_method_withholding_tax;
			$service = $method->cash_out_method_service_charge;
			$service_type = $method->cash_out_method_service_charge_type;
			$_slot = Tbl_slot::Owner()->Wallet($currency_id)->where("wallet_amount",">=",$method->minimum_payout)->get();
			foreach ($_slot as $key => $slot) 
			{
				$cashout_amount = $slot->wallet_amount;
				$totals = Self::cheque_computation($fix,$percent,$service,$cashout_amount,$service_type);
				$params["cash_out_primary_info"] = $slot->name;
				$params["cash_out_email_address"] = $slot->email;
				$params["cash_out_contact_number"] = $slot->contact;
				$params["cash_out_secondary_info"] = null;
				$params["cash_out_optional_info"] = null;
				$params["slot_id"] = $slot->slot_id;
				$params["cash_out_amount"] = $slot->wallet_amount;
				$params["total_due"] = $totals["total_due"];
				$params["expected_receivable"] = $totals["expected_receivable"];
				$params["service_charge"] = $method->cash_out_method_service_charge;
				$params["cash_out_method_method_fee"] = $method->cash_out_method_method_fee;
				$params["cash_out_method_withholding_tax"] = $method->cash_out_method_withholding_tax;
				$params["cash_out_method_id"] = $method->cash_out_method_id;
				$params["cash_out_method_currency"] = $method->cash_out_method_currency;
				Self::record_cash_out($params,$type = "members_wallet");
			}
		}
	}

	public static function 	cheque_computation($fix = 0, $percent = 0, $service = 0, $cashout_amount = 0,$service_type = '')
	{
		$total_charge = 0;
		if($percent > 0)
		{
			$tax_charge   = (($percent/100)*$cashout_amount);
			$total_charge = $total_charge + $tax_charge;
		}
		if($service > 0)
		{
			$service_charge =  $service_type == 'percentage' ? (($service/100)*($cashout_amount - $total_charge)) : $service;
			// $service_charge =  (($service/100)*($cashout_amount - $total_charge));
			$total_charge = $total_charge + $service_charge;
		}
		if($fix > 0)
		{
			$total_charge = $total_charge + $fix;
		}
		// if($data_focus.cash_out_method_charge_to == 'inclusive')
		// {
		$response["total_due"]  = $cashout_amount;
		$response["expected_receivable"] = $cashout_amount - $total_charge;
		// }
		// else
		// {
		// 	$total_due              = $cashout_amount + $total_charge;
		// 	$expected_receivable 	= $cashout_amount;
		// }
		return $response;
	}
	public static function process_transactions($sched_id,$type)
	{
		// dd(1231);
		$transactions_count_check = Tbl_cash_out_list::where("schedule_id", $sched_id)->where("cash_out_status","processing")->count();
		if($transactions_count_check > 0)
		{
			// dd(2313);
			if($type == 'reject')
			{
				$update = Tbl_cash_out_list::where("schedule_id", $sched_id)->where("cash_out_status","processing")->first();
				$params["transaction"] = $update->cash_out_id;
				$params["schedule"] = $sched_id;
				$params["amount"] = 0;
				$params["message"] = $update->cash_out_method_message;
				$params["sender"] = $update->sender_name;
				$params["control_no"] = $update->control_number;
				$params["thumbnail"] = $update->receipt_thumbnail;
				Self::update_transaction($params);
				$transaction = Tbl_cash_out_list::where("schedule_id", $sched_id)->where("cash_out_status","processing")->first();
				$transaction->slot_id = Tbl_slot::where("slot_no",$transaction->cash_out_slot_code)->first()->slot_id;
			}
			else 
			{
				$transaction = Tbl_cash_out_list::where("schedule_id", $sched_id)->where("cash_out_status","processing")->first();
				$transaction->slot_id = Tbl_slot::where("slot_no",$transaction->cash_out_slot_code)->first()->slot_id;	
			}
			$currency_id = Tbl_currency::where("currency_abbreviation",$transaction->cash_out_currency)->pluck('currency_id')->first() ?? 0;
			$saving_currency = Tbl_currency::where("currency_abbreviation", "SW")->first()->currency_id;
			$check_negative = Tbl_slot::where("slot_no",$transaction->cash_out_slot_code)->Wallet($currency_id)->first();
			if($check_negative['wallet_amount'] >= 0)
			{
				// dd($transaction->cash_out_original_amount_deducted != $transaction->cash_out_net_payout_actual);
				if($transaction->cash_out_original_amount_deducted != $transaction->cash_out_net_payout_actual)
				{
					if($transaction->cash_out_net_payout_actual == 0)
					{
				        $update_list['cash_out_remarks']= "PROCESSED";
						$update_list['cash_out_status'] = "REJECTED";
						$status_plan                    = "REJECTED";
						$refund                         = $transaction->cash_out_original_amount_deducted;
					}
					else
					{
                        $refund = ($transaction->cash_out_original_amount_deducted - $transaction->cash_out_net_payout_actual);
				        $update_list['cash_out_remarks']				    = "PROCESSED";
						$update_list['cash_out_status'] 					= "processed";
						$status_plan                    					= "REJECTED";
						Log::insert_wallet($transaction->slot_id, $transaction->cash_out_savings, "Savings", $saving_currency, $transaction->cash_out_id);
						
					}
					// dd($transaction);
					Tbl_cash_out_list::where("cash_out_id", $transaction->cash_out_id)->update($update_list);

					if($transaction->cash_out_type == null || $transaction->cash_out_type == 'current_slot')
					{
						Log::insert_wallet($transaction->slot_id, $refund, $status_plan, $currency_id, $transaction->cash_out_id);
					}
					else 
					{
						$slot_owner  = Tbl_slot::Owner()->where('slot_id',$transaction->slot_id)->first()->id;
						$slots       = Tbl_slot::where('slot_owner',$slot_owner)->pluck('slot_id');
						$owner_slots = Tbl_wallet_log::whereIn('wallet_log_slot_id',$slots)->where('wallet_log_details','CASH OUT')->where('transaction_id',$transaction->cash_out_id)->orderBy('wallet_log_id','DESC')->get();
						foreach ($owner_slots as $key => $owner_slot) 
						{
							$last = $refund;
							$refund = $refund - ($owner_slot->wallet_log_amount * -1);
							if($refund > 0) 
							{
								// dd($owner_slot,$refund,($owner_slot->wallet_log_amount * -1));
								Log::insert_wallet($owner_slot->wallet_log_slot_id, $owner_slot->wallet_log_amount * -1, $status_plan, $currency_id, $transaction->cash_out_id);
							}
							else 
							{
								Log::insert_wallet($owner_slot->wallet_log_slot_id, $last, $status_plan, $currency_id, $transaction->cash_out_id);
								break;
							}

						}
					}
				}
				else
				{

					/*GC CHARGED*/
					$get_gc_id		= Tbl_currency::where('currency_abbreviation','GC')->pluck('currency_id')->first();
					$get_gc_charge  = Tbl_cash_out_list::where('cash_out_id',$transaction->cash_out_id)->pluck('gc_charge')->first();
					Log::insert_wallet($transaction->slot_id, $get_gc_charge, "GC Charged", $get_gc_id, $transaction->cash_out_id);
					/*-------*/

					Tbl_cash_out_list::where("cash_out_id", $transaction->cash_out_id)->update(["cash_out_status" => "processed"]);
					Log::insert_wallet($transaction->slot_id, $transaction->cash_out_savings, "Savings", $saving_currency, $transaction->cash_out_id);
					$get_payout_log = DB::table('tbl_wallet_log')->where('transaction_id',$transaction->cash_out_id)->get();
					foreach ($get_payout_log as $key => $value) {
						$check_initial_payout = Tbl_slot::where('slot_id', $value->wallet_log_slot_id)->first()->initial_payout;
						if($check_initial_payout == 1)
						{
							$update['initial_payout'] = 0;
							Tbl_slot::where('slot_id', $value->wallet_log_slot_id)->update($update);
						}
					}
				}
			}
			else 
			{
				$update_list["cash_out_method_fee"] 			= 0;
				$update_list["cash_out_method_service_charge"] 	= 0;
				$update_list["cash_out_net_payout"] 			= 0;
				$update_list["cash_out_net_payout_actual"] 		= 0;
				$update_list["cash_out_method_tax"] 			= 0;
				$update_list["cash_out_amount_requested"] 		= 0;
				$update_list['cash_out_status'] 				= "REJECTED";
				$update_list['cash_out_remarks']				= "REJECTED";
				$status_plan                    				= "REJECTED";
				$refund                         				= $transaction->cash_out_original_amount_deducted;
				Tbl_cash_out_list::where("cash_out_id", $transaction->cash_out_id)->update($update_list);
				$schedule 	 = Tbl_cash_out_schedule::where("schedule_id",$sched_id)->first();
				if($schedule)
				{
					$update_payouts = Self::get_transactions_for_payout($schedule->schedule_date_from, $schedule->schedule_date_to, $transaction->cash_out_method_id ,$sched_id);
					Self::update_latest_schedule($update_payouts,$sched_id);
				}
				if($transaction->cash_out_type == null || $transaction->cash_out_type == 'current_slot')
				{
					Log::insert_wallet($transaction->slot_id, $refund, $status_plan, $currency_id, $transaction->cash_out_id);
				}
				else 
				{
					$slot_owner  = Tbl_slot::Owner()->where('slot_id',$transaction->slot_id)->first()->id;
					$slots       = Tbl_slot::where('slot_owner',$slot_owner)->pluck('slot_id');
					$owner_slots = Tbl_wallet_log::whereIn('wallet_log_slot_id',$slots)->where('wallet_log_details','CASH OUT')->where('transaction_id',$transaction->cash_out_id)->orderBy('wallet_log_id','DESC')->get();
					foreach ($owner_slots as $key => $owner_slot) 
					{
						$last = $refund;
						$refund = $refund - ($owner_slot->wallet_log_amount * -1);
						if($refund > 0) 
						{
							Log::insert_wallet($owner_slot->wallet_log_slot_id, $owner_slot->wallet_log_amount * -1, $status_plan, $currency_id, $transaction->cash_out_id);
						}
						else 
						{
							Log::insert_wallet($owner_slot->wallet_log_slot_id, $last, $status_plan, $currency_id, $transaction->cash_out_id);
							break;
						}

					}
				}
			}
			$array = ['REJECTED','processed'];
			$return["processed_transactions"] = Tbl_cash_out_list::where("schedule_id", $sched_id)->whereIn("cash_out_status",$array)->count();
			$return["processing_transactions"] = Tbl_cash_out_list::where("schedule_id", $sched_id)->count();
		}
		else 
		{
			$return["status"] = "success";
			$return["status_message"] = "Successfully processed transactions!";
			$old_value = Tbl_cash_out_schedule::where("schedule_id", $sched_id)->first();
			if($type == 'reject') {
				Tbl_cash_out_schedule::where("schedule_id", $sched_id)->update(["schedule_status" => "rejected"]);
			}
			else {
				Tbl_cash_out_schedule::where("schedule_id", $sched_id)->update(["schedule_status" => "processed"]);
			}
			$new_value = Tbl_cash_out_schedule::where("schedule_id", $sched_id)->first();
			$action    = "Process Payout";
			$user      = Request::user()->id;
			Audit_trail::audit(serialize($old_value),serialize($new_value),$user,$action);
		}
		return $return;
	}
	public static function check_negatives($sched_id)
	{
		$return = null;
		$check_negatives = Tbl_cash_out_list::where("schedule_id",$sched_id)
		->leftJoin("tbl_slot","tbl_slot.slot_no","=","tbl_cash_out_list.cash_out_slot_code")
		->leftJoin("tbl_wallet","tbl_wallet.slot_id","=","tbl_slot.slot_id")
		->where("currency_id","=", 1)
		->where("wallet_amount","<",0)
		->select("tbl_slot.slot_id")
		->first();
		if($check_negatives)
		{
			$return["status"] = "error";
			$return["status_message"] = "You Cannot Process Slot That Have Negative Wallet";
		}
		return $return;
	}
	public static function double_check_computation($method_id,$cash_out_amount,$multiplier_fee,$charges,$all_slot)
	{
		$method_data = Tbl_cash_out_method::where("cash_out_method_id",$method_id)->first();
		$total_charge = 0;
		$service_charge = 0;
		$tax_charge = 0;
		$savings_percentage = $method_data->savings_percentage;
		if($method_data->cash_out_method_withholding_tax > 0)
		{
			$tax_charge   = (($method_data->cash_out_method_withholding_tax/100)*$cash_out_amount);
			$total_charge = $total_charge + $tax_charge;
		}
		if($method_data->cash_out_method_service_charge > 0)
		{
			$service_charge =  $method_data->cash_out_method_service_charge_type == 'percentage' ? (($method_data->cash_out_method_service_charge/100)*($cash_out_amount - $total_charge)) : $method_data->cash_out_method_service_charge;
			
			// $service_charge =  (($method_data->cash_out_method_service_charge/100)*($cash_out_amount - $total_charge));
			$total_charge = $total_charge + $service_charge;
		}
		$total_charge = $total_charge + ($charges['gc_charge']/100)*$cash_out_amount;
		$total_charge = ($charges['product_charge'] * $multiplier_fee) + $total_charge; 

		if(isset($charges['survey_charge'])){
			
			$total_charge = $total_charge + ($charges['survey_charge'] * Self::survey_multiplier($all_slot));
			
		}
		if($method_data->cash_out_method_method_fee > 0)
		{

			if($multiplier_fee == null)
			{
				$total_charge = $total_charge + $method_data->cash_out_method_method_fee;
			}
			else 
			{
				$method_data->cash_out_method_method_fee = $method_data->cash_out_method_method_fee;
				$total_charge = $total_charge + $method_data->cash_out_method_method_fee ;				
			}
		}
		if($method_data->cash_out_method_charge_to == 'inclusive')
		{
			$total_due              = $cash_out_amount;
			$expected_receivable 	= $cash_out_amount - $total_charge;
		}
		else
		{
			$total_due              = $cash_out_amount + $total_charge;
			$expected_receivable 	= $cash_out_amount;
		}

		$savings 					= ($savings_percentage * $expected_receivable) / 100;
		$expected_receivable		= $expected_receivable - $savings;

		$params["cash_out_amount"]                 = $cash_out_amount;
		$params["cash_out_method_method_fee"]      = $method_data->cash_out_method_method_fee;
		$params["cash_out_method_withholding_tax"] = $tax_charge;
		$params["service_charge"]				   = $service_charge;
		$params["expected_receivable"]             = $expected_receivable;
		$params["savings"]             			   = $savings;
		$params["total_due"] 					   = $total_due;
		
		return $params;
	}

}