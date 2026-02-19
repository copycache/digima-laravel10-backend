<?php
namespace App\Http\Controllers\Admin;
use App\Globals\Slot;
use App\Globals\Member;
use App\Globals\Audit_trail;		
use App\Globals\MLM;
use App\Globals\Log;
use App\Models\Tbl_slot;
use App\Models\Tbl_slot_limit;
use App\Models\Tbl_mlm_plan;
use App\Models\Tbl_label;
use App\Models\Tbl_adjust_wallet_log;
use App\Models\Tbl_wallet_log;
use App\Models\Tbl_currency;
use Crypt;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
class AdminMemberController extends AdminController
{
    public function get()
	{
		$response = Member::get("member",Request::input('name'));
	    return response()->json($response, 200);
	}

	public function add()
	{
		$response = Member::add_member(request()->all());
		return $response;
	}

	public function add_slot()
	{
		$response = Slot::create_slot(request()->all());
		return $response;
	}

	public function place_slot()
	{
		$response = Slot::place_slot(request()->all());
		return $response;
	}

	public function get_auto_position()
	{
		$user     = Request::input('user'); 
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
			$action = "Auto Placement";
			Audit_trail::audit(null,serialize($response),$user,$action);
        }
		
		return $response;
	}

	public function get_slot_information()
	{
		$response = Slot::get_slot_information(Request::input('id'));

		if ($response->crypt)
		{
			try
			{
				$response->show_password = Crypt::decrypt($response->crypt);
			}
			catch (\Exception $e)
			{
				try
                {
                    $response->show_password = Crypt::decryptString($response->crypt);
                }
                catch (\Exception $e)
                {
                    $response->show_password = "";
                }

			}
		}
		else
		{
			$response->show_password = "";
		}

		$response->slot_sponsor_code = DB::table("tbl_slot")->where("slot_id","=",$response->slot_sponsor)->first() ? DB::table("tbl_slot")->where("slot_id","=",$response->slot_sponsor)->first()->slot_no : "--";
		$response->slot_placement_code = DB::table("tbl_slot")->where("slot_id","=",$response->slot_placement)->first() ? DB::table("tbl_slot")->where("slot_id","=",$response->slot_placement)->first()->slot_no : "--";

		return response()->json($response, 200);
	}

	public function submit_slot_information()
	{
		$response = Slot::submit_slot_information(Request::input());
		return response()->json($response, $response["status_code"]);
	}

	public function get_slot_details()
	{
		$response = Slot::get_slot_details(Request::input('id'));
		return response()->json($response, 200);
	}

	public function get_slot_earnings()
	{
		$response = Slot::get_slot_earnings(Request::input(), 10)->toArray();
		$response["total_earning"] = Slot::get_slot_total_earnings(Request::input("id"));
		return response()->json($response, 200);
	}

	public function get_slot_distributed()
	{
		$response = Slot::get_slot_distributed(Request::input(), 10)->toArray();
		$response["total_distributed"] = Slot::get_slot_total_distributed(Request::input("id"));
		return response()->json($response, 200);
	}

	public function get_slot_wallet()
	{
		$response = Slot::get_slot_wallet(Request::input(), 10)->toArray();
		$response["total_wallet"] = Slot::get_slot_total_wallet(Request::input("id"));
		return response()->json($response, 200);
	}

	public function get_slot_payout()
	{
		$response = Slot::get_slot_payout(Request::input(), 10)->toArray();
		$response["total_payout"] = Slot::get_slot_total_payout(Request::input("id"));
		return response()->json($response, 200);
	}

	public function get_slot_points()
	{
		$response = Slot::get_slot_points(Request::input(), 10)->toArray();
		$response["total_points"] = Slot::get_slot_total_points(Request::input("id"));
		return response()->json($response, 200);
	}

	public function get_slot_network()
	{
		$response = Slot::get_slot_network(Request::input(), 10)->toArray();
		return response()->json($response, 200);
	}

	public function get_slot_codevault()
	{
		$response = Slot::get_slot_codevault(Request::input(), 10)->toArray();
		return response()->json($response, 200);
	}
	public function initialize_recompute()
	{
		DB::table("tbl_wallet_log")->delete();
		DB::table("tbl_wallet")->update(['wallet_amount' => 0]);
		DB::table("tbl_points_log")->where("points_log_type","BINARY_RIGHT")->delete();
		DB::table("tbl_points_log")->where("points_log_type","BINARY_LEFT")->delete();
		DB::table("tbl_binary_points")->delete();
		DB::table("tbl_cash_out_list")->delete();
		DB::table("tbl_cash_out_schedule")->delete();
		DB::table("tbl_earning_log")->delete();
		DB::table("tbl_slot")->update(["slot_left_points"=>0]);
		DB::table("tbl_slot")->update(["slot_right_points"=>0]);
		DB::table("tbl_slot")->update(["slot_total_earnings"=>0]);
		DB::table("tbl_slot")->update(["slot_pairs_per_day_date"=>""]);
		DB::table("tbl_slot")->update(["slot_pairs_per_day"=>0]);
		DB::table("tbl_slot")->update(["meridiem" => ""]);

		$data["_slot_sponsor"] 		= Tbl_slot::owner()->orderBy('slot_date_created')->where("membership_inactive",0)->where("slot_sponsor","!=",0)->get();
		$data["_slot_placement"] 	= Tbl_slot::owner()->orderBy('slot_date_placed')->where("membership_inactive",0)->where("slot_sponsor","!=",0)->where("slot_placement", "!=", 0)->get();

		$action = "Recompute Data";
		Audit_trail::audit(null,null,Request::input('user'),$action);

		echo json_encode($data);
	}
	public function recompute_sponsor()
	{
		MLM::create_entry(request()->slot_id);
		echo json_encode(request()->slot_id);
	}
	public function recompute_placement()
	{
		MLM::placement_entry(request()->slot_id);
		echo json_encode(request()->slot_id);
	}
	public function slot_limit()
	{

		$response =Tbl_slot_limit::where("user_id",request()->id)->first();
		if(!$response)
		{
			$response["user_id"] 	  = 0;
			$response["active_slots"] = 0;
			$response["slot_limit"]	  = 0;
		}
		return $response;
	}
	public function update_slot_limit()
	{
		$response = Member::update_limit(request()->all());
		return $response;
	}

	public function import_excel()
	{
		// $stat           = "success";
		// $file         	= Request::file('file_data')->getRealPath();
		// $_data        	= Excel::selectSheetsByIndex(0)->load($file, function($reader){})->all();
		// $first        	= $_data[0]; 
		
		// dd($_data[0]);
		// if(isset($first['product_name'])&&isset($first['product_code']))
		// {  
		//     $count_success 		= 0;
		// 	$count_error = 0;
			
		// 	foreach($_data as $key_row =>$data)
		// 	{
		// 		$product_check    = Tbl_eloading_product::where('eloading_product_code',$data['product_code'])->count();
		// 		if($product_check!=0||$data['product_code']==null||$data['product_name']==null)
		// 		{
		// 			Self::excel_error($data,'Product code exist',$key_row);
		// 			$count_error++;
		// 		}
		// 		else
		// 		{
		// 			$insert['eloading_product_name']        =   $data['product_name'];
		// 			$insert['eloading_product_code']       	=   $data['product_code'];
		// 			$insert['eloading_product_description'] =   $data['product_description']; 
		// 			$insert['eloading_product_validity']    =   $data['product_validity'];
		// 			$insert['eloading_product_guide']    	=   $data['product_guide'];
		// 			$insert['eloading_product_subscriber']  =   $data['product_subscriber'];
		// 			$insert['eloading_product_type']        =   $data['product_type'];
					
		// 			Tbl_eloading_product::insert($insert);
					
		// 			$count_success++;
		// 		}
		// 	}
		// }
		// else
		// {
		// 	$stat         = "error";
		// }

		$return["message"]        		= "DONE IMPORTATION";
		// $return["status"]         		= $stat;
		$return["status_code"]    		= 404;
		// $return["count_success"]     	= $count_success;
		// $return["count_error"]     		= $count_error;
		
		return response()->json($return);
	
	}

	public function get_plan_list()
    {
        $plan   = Tbl_mlm_plan::where('mlm_plan_enable' , 1 )
        						->leftJoin('tbl_label','tbl_label.plan_code','=','tbl_mlm_plan.mlm_plan_code')
        						->get();

        return json_encode($plan);

    }

	public function adjust_wallet()
    {
		$data    = request()->all();
    	if($data['plan'] ?? null)
    	{
			if(($data['currency_id'] ?? null) == null || ($data['currency_id'] ?? '') == '')
			{
				$data['currency_id'] = Tbl_currency::where("currency_default",1)->first()->currency_id;
			}
			$currency_abb = Tbl_currency::where('currency_id',$data['currency_id'])->first()->currency_abbreviation;
			// $replace = trim(preg_replace('/ /', '_', $data['plan']));
			$plan_code     = Tbl_label::where('plan_name',$data['plan'])->first();
			if($plan_code)
			{
				// dd('hello');
				$entry         = Tbl_mlm_plan::where('mlm_plan_code',$plan_code->plan_code)->first()->mlm_plan_trigger;
    			$details       = "";
    			$level         = 1;
				
    			if(($data['amount'] ?? 0) != 0)
    			{
					//audit trail old value
					$old_value = Tbl_wallet_log::where("wallet_log_slot_id",$data['slot_id'])->where("currency_id",$data['currency_id'])->sum("wallet_log_amount");
					//

    				Log::insert_wallet($data['slot_id'],$data['amount'],$plan_code->plan_code,$data['currency_id']);
					Log::insert_earnings($data['slot_id'],$data['amount'],$plan_code->plan_code,$entry,$data['trigger'],$details,$level,$data['currency_id']);

					//audit trail old value
					$new_value = Tbl_wallet_log::where("wallet_log_slot_id",$data['slot_id'])->where("currency_id",$data['currency_id'])->sum("wallet_log_amount");
					//

					$action = "Adjust Wallet";
					Audit_trail::audit($old_value,$new_value,$data['user']['id'],$action);
					$insert["slot_id"]        		= $data['slot_id']; 
					$insert["adjusted_currency"]    = $currency_abb; 
					$insert["adjusted_detail"] 		= $data['plan'];
					$insert["adjusted_amount"] 		= $data['amount'];
					$insert["date_created"] 		= Carbon::now();

					Tbl_adjust_wallet_log::insert($insert);

					$return["status"]         = "success"; 
					$return["status_code"]    = 201; 
					$return["status_message"] = "Wallet Adjusted";
				}
				else 
				{
					$return["status"]         = "Error"; 
					$return["status_code"]    = 200; 
					$return["status_message"] = "Amount is Invalid";	
				}
			}
			else 
			{
				if($data['amount'] != 0)
    			{
					//audit trail old value
					$old_value = Tbl_wallet_log::where("wallet_log_slot_id",$data['slot_id'])->where("currency_id",$data['currency_id'])->sum("wallet_log_amount");
					//
					Log::insert_wallet($data['slot_id'],$data['amount'],$data['plan'],$data['currency_id']);
					//audit trail old value
					$new_value = Tbl_wallet_log::where("wallet_log_slot_id",$data['slot_id'])->where("currency_id",$data['currency_id'])->sum("wallet_log_amount");
					//
					$action = "Adjust Wallet";
					Audit_trail::audit($old_value,$new_value,$data['user']['id'],$action);

					$insert["slot_id"]        		= $data['slot_id']; 
					$insert["adjusted_currency"]    = $currency_abb; 
					$insert["adjusted_detail"] 		= $data['plan'];
					$insert["adjusted_amount"] 		= $data['amount'];
					$insert["date_created"] 		= Carbon::now();

					Tbl_adjust_wallet_log::insert($insert);

					$return["status"]         = "success"; 
					$return["status_code"]    = 201; 
					$return["status_message"] = "Wallet Adjusted";
				}
				else 
				{
					$return["status"]         = "Error"; 
					$return["status_code"]    = 200; 
					$return["status_message"] = "Amount is Invalid";	
				}
			}
    	}
    	else
    	{
    		$return["status"]         = "Error"; 
			$return["status_code"]    = 200; 
			$return["status_message"] = "No Plan Trigger";
    	}

    	

        return json_encode($return);

	}
	public function slot_info()
	{
		$response = Member::slot_info("member",Request::input('name'));
	    return response()->json($response, 200);
	}
	public function select_user()
	{
		$response = Member::select_user(Request::input('id'));
	    return response()->json($response, 200);
	}
	public function get_unplaced()
	{
		$response = Member::get_unplaced(Request::input('slot_code'));
	    return response()->json($response, 200);
	}
	public function slot_code_history()
	{
		$response = Member::slot_code_history(Request::input());
	    return response()->json($response, 200);
	}

	public function user_verification()
	{
		$response = Member::verify(Request::input());
		return response()->json($response);
	}
}
