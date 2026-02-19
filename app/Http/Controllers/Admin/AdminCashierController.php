<?php
namespace App\Http\Controllers\Admin;

use App\Globals\Cashier;
use App\Globals\Audit_trail;
use App\Models\Tbl_cashier_bonus;
use App\Models\Tbl_cashier_bonus_settings;

use Illuminate\Support\Facades\Request;
use Hash;
use Illuminate\Support\Facades\DB;
class AdminCashierController extends AdminController
{
    public function add_cashier() 
	{
	    $response = Cashier::add(Request::input());

	    return response()->json($response);
	}

	public function get_cashierList()
	{
		$branch_id = Request::input('id');
		$filter = Request::input('filter');

		$response = Cashier::getList($branch_id, $filter);

		return response()->json($response, 200);
	}

	public function edit_cashier()
	{
		$response = Cashier::get_data(Request::input("id"));
		return response()->json($response, 200);
	}
	public function edit_password()
	{
		$response = Cashier::get_data_password(Request::input("id"));
		return response()->json($response, 200);
	}

	public function edit_cashier_submit()
	{
		$response = Cashier::cashier_update(Request::input());
		return response()->json($response);
	}

	public function archive()
	{
		$response = Cashier::archive(Request::input("id"));
		return response()->json($response, 200);
	}

	public function edit()
	{
		$response = Cashier::edit(Request::input());
		return response()->json($response,200);
	}

	public function search()
	{	
		$response = Cashier::search(Request::input());
		return response()->json($response, 200);
	}

	public function add_location()
	{
		$response = Cashier::add_location(Request::input());

		return response()->json($response);
	}

	public function get_location()
	{
		$response = Cashier::get_location();
		return response()->json($response, 200);
	}

	public function archive_location()
	{
		$data = Request::input('location');
		$response = Cashier::archive_location($data);

		return response()->json($response);
	}

	public function get_payment_method()
	{
		$response = DB::table('tbl_cashier_payment_method')->get();

		return response()->json($response);
	}

	public function add_payment_method()
	{
		$insert['cashier_payment_method_name'] = Request::input('name');
		$insert['cashier_payment_method_status'] = 1;
		DB::table('tbl_cashier_payment_method')->insert($insert);

		$response["status"]         = "Success"; 
		$response["status_code"]    = 200; 
		$response["status_message"] = "Saved Successfully!";
		return response()->json($response);
	}

	public function set_payment_method()
	{
		$payment = Request::input('payment');
		foreach($payment as $key => $value)
		{
			$update['cashier_payment_method_status'] = $value['cashier_payment_method_status'];
			$old['value'][$key] = DB::table('tbl_cashier_payment_method')->where('cashier_payment_method_id', $value['cashier_payment_method_id'])->first();
			DB::table('tbl_cashier_payment_method')->where('cashier_payment_method_id', $value['cashier_payment_method_id'])->update($update);
			$new['value'][$key] = DB::table('tbl_cashier_payment_method')->where('cashier_payment_method_id', $value['cashier_payment_method_id'])->first();

		}
			$action    = "Set Payment Method";
			$user      =  Request::user()->id;

			Audit_trail::audit(serialize($old),serialize($new),$user,$action);

		$response["status"]         = "Success"; 
		$response["status_code"]    = 200; 
		$response["status_message"] = "Saved Successfully!";
		return response()->json($response);
	}

	public function edit_receipt_info()
	{
		$data = Request::input();
		$check = DB::table('tbl_receipt_details')->first();

		if($check)
		{
			$update['title'] 				= $data['title']		;
			$update['tin']					= $data['tin'];
			$update['details']				= $data['details'];
			$update['disclaimer'] 			= $data['disclaimer'];
			$update['claim_code'] 			= $data['claim_code'];
			$update['payment_type'] 		= $data['payment_type'];

			DB::table('tbl_receipt_details')->update($update);
		}
		else
		{
			$insert['title'] 				= $data['title']		;
			$insert['tin']					= $data['tin'];
			$insert['details']				= $data['details'];
			$insert['disclaimer'] 			= $data['disclaimer'];
			$insert['claim_code'] 			= $data['claim_code'];
			$insert['payment_type'] 		= $data['payment_type'];

			DB::table('tbl_receipt_details')->insert($insert);
		}

		$response["status"]         = "Success"; 
		$response["status_code"]    = 200; 
		$response["status_message"] = "Saved Successfully!";

		return response()->json($response);
	}

	public function load_receipt_info()
	{
		$return = DB::table('tbl_receipt_details')->first();

		return response()->json($return);
	}

	public function load_cashier_bonus()
	{
		$check = Tbl_cashier_bonus_settings::first();
		if(!$check)
		{
			$insert["cashier_bonus_enable"] = 0;
			Tbl_cashier_bonus_settings::insert($insert);
		}
		$settings = Tbl_cashier_bonus_settings::first();
		$response["cashier_bonus_settings"] = $settings;
		$check2 = Tbl_cashier_bonus::where("archive",0)->first();
		if(!$check2)
		{
			$insert2["cashier_bonus_buy_amount"] = 5000;
			$insert2["cashier_bonus_given_amount"] = 100;
			Tbl_cashier_bonus::insert($insert2);
		}
		$settings2 = Tbl_cashier_bonus::where('archive',0)->get();
		$response["cashier_bonus"] = $settings2;
		return response()->json($response);
	}

	public function manage_cashier_bonus()
	{
		$data  = Request::input();
		$update["cashier_bonus_enable"] = $data["cashier_bonus_settings"]["cashier_bonus_enable"];
		Tbl_cashier_bonus_settings::where("cashier_bonus_settings_id", $data["cashier_bonus_settings"]["cashier_bonus_settings_id"])->update($update);
		// dd($data["cashier_bonus"]);
		foreach ($data["cashier_bonus"] as $key => $value) 
		{
			$param["cashier_bonus_buy_amount"]         = $value["cashier_bonus_buy_amount"];
			$param["cashier_bonus_given_amount"]             = $value["cashier_bonus_given_amount"];
			if ($value["cashier_bonus_id"]) 
			{
				$param["archive"] = $value["archive"];
				Tbl_cashier_bonus::where("cashier_bonus_id", $value["cashier_bonus_id"])->update($param);
			}
			else
			{
				if($param["cashier_bonus_buy_amount"] != 0 || $param["cashier_bonus_given_amount"] !=0 || $param["cashier_bonus_buy_amount"] != null || $param["cashier_bonus_given_amount"] != null)
				{
					Tbl_cashier_bonus::insert($param);
				}
			}
		}
	}

	
}
