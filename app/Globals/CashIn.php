<?php
namespace App\Globals;

use DB;
use Carbon\Carbon;
use Request;
use App\Models\Tbl_cash_in_proofs;
use App\Models\Tbl_cash_in_method;
use App\Models\Tbl_cash_in_method_category;
use App\Models\Tbl_slot;
use App\Models\Users;
use App\Globals\Audit_trail;
use App\Globals\Log;
use App\Globals\Currency;
use App\Models\Tbl_currency;
use App\Models\Tbl_other_settings;
use App\Models\Tbl_dealer;
use App\Models\Tbl_retailer;
use App\Models\Tbl_user_process;


use Validator;
use Hash;
use Excel;
class CashIn
{
	public static function get_transactions($params = null, $slot_owner = null)
	{
		// dd($params['user']);
		if($params['user'] == 'admin1')
		{
			if($params['position'] == 'superadmin' || $params['position'] == 'admin1')
			{
				$data  							= Tbl_cash_in_proofs::method();
			}
			else
			{
				$data  							= Tbl_cash_in_proofs::where('cash_in_receivable', '<=', 2000)->method();
			}
		}
		else
		{
			$data  							= Tbl_cash_in_proofs::method();
		}
		//single member
		if($slot_owner)
		{
			$slot_info = Tbl_slot::where("slot_id", $slot_owner)->first();
			if($slot_info)
			{
				$data = $data->where("cash_in_slot_code", $slot_info->slot_no);
			}
		}
		
		//filter by cash in status
		if(isset($params["cash_in_status"]) && $params["cash_in_status"] != "all")
		{
			$data = $data->where("cash_in_status", $params["cash_in_status"]);
		}

		//filter by slot code or slot owner
		if(isset($params["cash_in_owner"]) && $params["cash_in_owner"] != null)
		{
			$owner = $params["cash_in_owner"];
			$data = $data->where(function($query) use ($owner)
				{
					$query->where("cash_in_slot_code", "like", "%".$owner."%")
						  ->orWhere("cash_in_member_name", "like", "%".$owner."%");
				});
		}

		//filter by method
		if(isset($params["cash_in_method_id"]) && $params["cash_in_method_id"] != "all")
		{
			$data = $data->where("tbl_cash_in_proofs.cash_in_method_id", $params["cash_in_method_id"]);
		}

		//filter by currency
		if(isset($params["cash_in_currency"]) && $params["cash_in_currency"] != "all")
		{
			$data = $data->where("cash_in_currency", $params["cash_in_currency"]);
		}

		//filter by cash in date from
		if(isset($params["cash_in_date_from"]) && $params["cash_in_date_from"] != "all")
		{
			$data = $data->whereDate("cash_in_date", ">=", $params["cash_in_date_from"]);
		}

		//filter by cash in date to
		if(isset($params["cash_in_date_to"]) && $params["cash_in_date_to"] != "all")
		{
			$data = $data->whereDate("cash_in_date", "<=", $params["cash_in_date_to"]);
		}


		$data = $data->orderBy('tbl_cash_in_method.cash_in_method_name','ASC')->get();

		
		return $data;
		
		
	}
	

	public static function get_method_list($category = null, $currency = null, $except_archive = null)
	{
		$check  = Currency::module_settings();
		
		$data = Tbl_cash_in_method::where("cash_in_method_id", "!=", 0);

		if($except_archive)
		{
			$data = $data->where("is_archived", 0);
		}	
		

		if($category && $category != "all")
		{
			$data = $data->where("cash_in_method_category", $category);
		}

		if($currency && $currency != "all")
		{
			$data = $data->where("cash_in_method_currency", $currency);
		}

		if($check['eloading'] == 1)
		{
			$data = $data->where("cash_in_method_currency","!=","LW");
		}

		return $data->get();
	}

	public static function get_method_category_list()
	{
		$data = Tbl_cash_in_method_category::where("cash_in_method_category_id", "!=", 0);
		
		return $data->get();
	}

	public static function add_new_method($params = null,$user)
	{
		if($params)
		{
			$rules["cash_in_method_thumbnail"] = "required";
			// $rules["crypto_thumbnail"] = "required";
			$rules["cash_in_method_name"] = "unique:tbl_cash_in_method|required";

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
				$new_id    = Tbl_cash_in_method::insertGetId($params);
				$new_value = Tbl_cash_in_method::where('cash_in_method_id',$new_id)->first();
				$action    = 'Add Cashin Method';
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

	public static function update_method($params = null,$user)
	{
		if($params)
		{
			$old_value = Tbl_cash_in_method::where('cash_in_method_id',$params["cash_in_method_id"])->first();

			Tbl_cash_in_method::where("cash_in_method_id", $params["cash_in_method_id"])->update($params);

			$new_value = Tbl_cash_in_method::where('cash_in_method_id',$params["cash_in_method_id"])->first();

			$action    = 'Update Cashin Method';
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

	public static function archive_method($id = null, $archive = null,$user)
	{
		if($id)
		{
			$old_value = Tbl_cash_in_method::where('cash_in_method_id',$id)->first();

			Tbl_cash_in_method::where("cash_in_method_id", $id)->update(["is_archived"=>$archive]);

			$new_value = Tbl_cash_in_method::where('cash_in_method_id',$id)->first();
			if($archive == 1)
			{
				$action    = 'Archive Cashin Method';
			}
			else 
			{
				$action    = 'Unarchive Cashin Method';
			}
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

	public static function record_cash_in($params)
	{
		$currency = Tbl_currency::where("currency_abbreviation", strtoupper($params["cash_in_method_currency"]))->first();
		$slot_info = Tbl_slot::owner()->wallet($currency->currency_id)->where("tbl_slot.slot_id", $params["slot_id"])->first();
		$check_if_exists  = Tbl_cash_in_proofs::where("cash_in_slot_code", $slot_info->slot_no)->whereIn("cash_in_status",["pending","processing"])->first();
		
		if(!$check_if_exists) {
			if(isset($params["slot_id"]))
			{
				$slot_info = Tbl_slot::owner()->where("tbl_slot.slot_id", $params["slot_id"])->first();

				if($slot_info)
				{
					$insert["cash_in_slot_code"] 	= $slot_info->slot_no;
					$insert["cash_in_member_name"] 	= $slot_info->name;
					$insert["cash_in_method_id"] 	= $params["cash_in_method_id"];
					$insert["cash_in_currency"] 	= $params["cash_in_method_currency"]; 
					$insert["cash_in_charge"] 		= $params["total_due"] - $params["cash_in_amount"];
					$insert["cash_in_receivable"] 	= $params["cash_in_wallet"] == "LW" ? $params['expected_receivable'] : $params["cash_in_amount"];
					$insert["cash_in_payable"] 		= $params["total_due"];
					$insert["cash_in_proof"] 		= $params["cash_in_proof"];
					$insert["cash_in_wallet"] 		= $params["cash_in_wallet"];
					$insert["cash_in_date"] 		= Carbon::now('Asia/Manila');
					Tbl_cash_in_proofs::insert($insert);

					$return["status"] = "success";
					$return["status_message"] = "Successfully placed your Cash-In request.";
				}
			}
			else
			{
				$return["status"] = "error";
				$return["status_message"] = "Oops! Something went wrong.";
			}
		}
		else {
			$return["status"]         = "existing"; 
			$return["status_code"]    = 400; 
			$return["status_message"] = "Sorry, we cannot proceed the request. There's ongoing cash-in request.";
		}

		
		
		return $return;
	}

	public static function process_transaction($params = null,$user)
	{
		if($params)
		{
			$check = Tbl_cash_in_proofs::slot()->where("cash_in_proof_id", $params["proof_id"])->first();
			if($check)
			{	
				if($params['process'] == "approved_adjustment")
				{
					$update['cash_in_charge'] 		= $params['data']['cash_in_charge'];
					$update['cash_in_receivable'] 	= $params['data']['cash_in_receivable'];
					$update['cash_in_payable'] 		= $params['data']['cash_in_payable'];
					$update['cash_in_message'] 		= isset($params['data']['cash_in_message']) ? $params['data']['cash_in_message'] : null;
					Tbl_cash_in_proofs::slot()->where("cash_in_proof_id", $params["proof_id"])->update($update);
					
					$params['process']   = "approved";
					$params['message']  = $update['cash_in_message'];
				}

				$transaction = Tbl_cash_in_proofs::slot()->where("cash_in_proof_id", $params["proof_id"])->where('cash_in_status','pending')->first();


				if($transaction)
				{
					$user_process_level                                          = 1;
					$user_process_proceed                                        = 1;
					$user_info_process_id                                        = Request::user()->id ?? null;
			
					if($user_info_process_id != null)
					{
						/* PREVENTS MULTIPLE PROCESS AT ONE TIME */
						Tbl_user_process::where("user_id",$user_info_process_id)->delete();
			
						$insert_user_process["level_process"]                    = $user_process_level;
						$insert_user_process["user_id"]                          = $user_info_process_id;
						Tbl_user_process::where("user_id",$user_info_process_id)->where("level_process",$user_process_level)->insert($insert_user_process);
			
						while($user_process_level <= 4)
						{
							$user_process_level++;
							$insert_user_process["level_process"]                = $user_process_level;
							$insert_user_process["user_id"]                      = $user_info_process_id;
							Tbl_user_process::where("user_id",$user_info_process_id)->where("level_process",$user_process_level)->insert($insert_user_process);
			
							$count_process_before = Tbl_user_process::where("user_id",$user_info_process_id)->where("level_process", ($user_process_level - 1) )->count();
			
							if($count_process_before != 1)
							{
								$user_process_proceed = 0;
								break;
							}
						}
						Tbl_user_process::where("user_id",$user_info_process_id)->delete();
					}
					if($user_process_proceed == 1)
					{ 
						if($params["process"] != "rejected")
						{
							$currency_id    = Tbl_currency::where('currency_abbreviation',$transaction->cash_in_wallet)->value('currency_id');
							Log::insert_wallet($transaction->slot_id, $transaction->cash_in_receivable,"CASH IN", $currency_id, $transaction->cash_in_proof_id);
							
		
							/* DEALERS BONUS */
							$proceed_to_retail = Tbl_other_settings::where("key","retailer")->first() ? Tbl_other_settings::where("key","retailer")->first()->value : 0;
							$dealers_bonus     = Tbl_other_settings::where("key","dealers_bonus")->first() ? Tbl_other_settings::where("key","dealers_bonus")->first()->value : 0;
							
							if($proceed_to_retail == 1 && $dealers_bonus != 0)
							{
								$slot = Tbl_slot::where("slot_id",$transaction->slot_id)->first();
								if($slot)
								{
									if($slot->is_retailer == 1)
									{
										$retailer_slot  = Tbl_retailer::where("slot_id",$slot->slot_id)->first();
										if($retailer_slot)
										{
											$dealer_slot = Tbl_slot::where("slot_id",$retailer_slot->dealer_slot_id)->first();
											if($dealer_slot)
											{
												$details 			   = "";
												$dealer_bonus_computed = $transaction->cash_in_receivable * ($dealers_bonus/100);
		
												Log::insert_wallet($dealer_slot->slot_id,$dealer_bonus_computed,"DEALERS_BONUS");
												Log::insert_earnings($dealer_slot->slot_id,$dealer_bonus_computed,"DEALERS_BONUS","CASH IN",$retailer_slot->slot_id,$details);
											}
										}
									}
								}
							}
						}
						
						$data = Tbl_cash_in_proofs::where("cash_in_proof_id", $transaction->cash_in_proof_id)->update(["cash_in_status" => $params['process'], "cash_in_message" => $params['message']]);
						$return["status"] 		  = "success";
						$return["status_message"] = "Successfully ".$params["process"]." transaction!";
					}
					else
					{
						$response['status_code']       = 500;
						$response['status_message']    = "Please wait to processed first request! <br> Try again later.";
					}
				}
				else
				{
					$return["status"] 		  = "error";
					$return["status_message"] = "No pending transaction at this moment.";
				}
				$old_value = Tbl_cash_in_proofs::slot()->where("cash_in_proof_id", $params["proof_id"])->first();
				if($params["process"] != "rejected")
				{
					$action    = 'Processed Transaction';
				}
				else 
				{
					$action    = 'Rejected Transaction';
				}
				
				Audit_trail::audit(serialize($check),serialize($old_value),$user,$action);
			}
			else
			{
				$return["status"] 		  = "error";
				$return["status_message"] = "Transaction not found.";
			}
		}
		else
		{
			$return["status"] 		  = "error";
			$return["status_message"] = "Parameters cannot be blank";
		}

		return $return;
	}

	public static function process_all_transaction($params = null,$user)
	{

		if($params)
		{
			foreach ($params["proof_id"] as $key => $value) {
				$transaction = Tbl_cash_in_proofs::slot()->where("cash_in_proof_id", $value)->where('cash_in_status','pending')->first();
				if($transaction)
				{
					$user_process_level                                          = 1;
					$user_process_proceed                                        = 1;
					$user_info_process_id                                        = Request::user()->id ?? null;
			
					if($user_info_process_id != null)
					{
						/* PREVENTS MULTIPLE PROCESS AT ONE TIME */
						Tbl_user_process::where("user_id",$user_info_process_id)->delete();
			
						$insert_user_process["level_process"]                    = $user_process_level;
						$insert_user_process["user_id"]                          = $user_info_process_id;
						Tbl_user_process::where("user_id",$user_info_process_id)->where("level_process",$user_process_level)->insert($insert_user_process);
			
						while($user_process_level <= 4)
						{
							$user_process_level++;
							$insert_user_process["level_process"]                = $user_process_level;
							$insert_user_process["user_id"]                      = $user_info_process_id;
							Tbl_user_process::where("user_id",$user_info_process_id)->where("level_process",$user_process_level)->insert($insert_user_process);
			
							$count_process_before = Tbl_user_process::where("user_id",$user_info_process_id)->where("level_process", ($user_process_level - 1) )->count();
			
							if($count_process_before != 1)
							{
								$user_process_proceed = 0;
								break;
							}
						}
						Tbl_user_process::where("user_id",$user_info_process_id)->delete();
					}
					if($user_process_proceed == 1)
					{ 
						if($params["process"] == "approved")
						{
							$currency_id    = Tbl_currency::where('currency_abbreviation',$transaction->cash_in_wallet)->value('currency_id');
							Log::insert_wallet($transaction->slot_id, $transaction->cash_in_receivable,"CASH IN", $currency_id, $transaction->cash_in_proof_id);
						

							/* DEALERS BONUS */
							$proceed_to_retail = Tbl_other_settings::where("key","retailer")->first() ? Tbl_other_settings::where("key","retailer")->first()->value : 0;
							$dealers_bonus     = Tbl_other_settings::where("key","dealers_bonus")->first() ? Tbl_other_settings::where("key","dealers_bonus")->first()->value : 0;
							
							if($proceed_to_retail == 1 && $dealers_bonus != 0)
							{
								$slot = Tbl_slot::where("slot_id",$transaction->slot_id)->first();
								if($slot)
								{
									if($slot->is_retailer == 1)
									{
										$retailer_slot  = Tbl_retailer::where("slot_id",$slot->slot_id)->first();
										if($retailer_slot)
										{
											$dealer_slot = Tbl_slot::where("slot_id",$retailer_slot->dealer_slot_id)->first();
											if($dealer_slot)
											{
												$details 			   = "";
												$dealer_bonus_computed = $transaction->cash_in_receivable * ($dealers_bonus/100);
												
												Log::insert_wallet($dealer_slot->slot_id,$dealer_bonus_computed,"DEALERS_BONUS");
												Log::insert_earnings($dealer_slot->slot_id,$dealer_bonus_computed,"DEALERS_BONUS","CASH IN",$retailer_slot->slot_id,$details);
											}
										}
									}
								}
							}
						}
						$data = Tbl_cash_in_proofs::where("cash_in_proof_id", $value)->update(["cash_in_status" => $params['process']]);
						$return["status"] 		  = "success";
						$return["status_message"] = "Successfully ".$params["process"]." all pending transactions!";
					}
					else
					{
						$response['status_code']       = 500;
						$response['status_message']    = "Please wait to processed first request! <br> Try again later.";
					}
				}
				else
				{
					$return["status"] 		  = "error";
					$return["status_message"] = "No pending transaction at this moment.";
				}
			}
			$action    = 'Process All Transaction';
			Audit_trail::audit(null,null,$user,$action);
		}
		else
		{
			$return["status"] 		  = "error";
			$return["status_message"] = "Parameters cannot be blank";
		}

		return $return;
	}
}