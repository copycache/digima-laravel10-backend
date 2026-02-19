<?php
namespace App\Http\Controllers\Admin;
use App\Globals\Audit_trail;
use App\Globals\Plan;
use App\Globals\Currency;
use App\Models\Tbl_currency;
use App\Models\Tbl_investment_package;
use App\Models\Tbl_membership;
use App\Models\Tbl_membership_upgrade_settings;
use App\Models\Tbl_investment_amount;
use App\Models\Tbl_mlm_plan;
use App\Models\Tbl_wallet_log;
use App\Models\Tbl_earning_log;
use App\Models\Tbl_label;

use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Request;
use App\Globals\Investment;
class AdminPlanController extends AdminController
{
    public function get() 
	{
		
		$plan     = Request::input("plan");
		$response = Plan::get($plan);
	    return response()->json($response, 200);
	}

    public function update() 
	{
		$plan     = Request::input("plan");
		$label    = Request::input("label");
		$data     = Request::input("data");
		$trigger  = Request::input("trigger");
		$response = Plan::update($plan,$label,$data,$trigger);
	    return response()->json($response, 200);
	}

	public function update_board()
	{
		$boardInfo = Request::input('boardInfo');
		$logic = $boardInfo['logic'];
		$levels = $boardInfo['levels'];
		$depth  = $boardInfo['depth'];
		$graduationBonus = Request::input('gradBonus');
		// if(!$levels)
		// {
		// 	$return["status_message"]  = ""; 
		// 	$return["status"]         = "success"; 
		// 	$return["status_code"]    = 201; 
		// }

		if($logic == 'fifo')
		{
			DB::table('tbl_mlm_plan')->where('mlm_plan_code', 'BOARD')->update(['mlm_plan_trigger' => 'Slot Creation']);
		}
		else
		{
			DB::table('tbl_mlm_plan')->where('mlm_plan_code', 'BOARD')->update(['mlm_plan_trigger' => 'Slot Placement']);
		}
		$response = Plan::update_board($levels,$depth,$graduationBonus);
		return response()->json($response);
	}

    public function update_status() 
	{
		$plan     = Request::input("plan");
		$send     = Request::input("send");
		$response = Plan::update_status($plan,$send);
	    return response()->json($response, 200);
	}
	public function currency_get()
	{
		$currency = Currency::settings_currency();
		return response()->json($currency, 200);
	}
	public function currency_update()
	{
		if(Request::input('param') == 'currency')
		{
			Currency::update_currency(Request::input('data'));
		}
		if(Request::input('param') == 'currency_conversion')
		{
			Currency::update_currency_conversion(Request::input('data'),Request::input('abbreviation'));
		}
		if(Request::input('param') == 'add_currency')
		{
			Currency::add_currency(Request::input('new_currency_name'),Request::input('new_currency_abbreviation'));
		}
	}
	public function investment_package_get()
	{
		if(Tbl_investment_package::count()==0)
		{
			$insert['investment_package_id'] 			= 1;	
			$insert['investment_package_days_bond'] 	= 1;		 
			$insert['investment_package_min_interest'] 	= 1;		 
			$insert['investment_package_max_interest'] 	= 1;		 
			$insert['investment_package_days_margin'] 	= 1;

			Tbl_investment_package::insert($insert);
		}
		$package = Tbl_investment_package::get();
		return response()->json($package, 200);
	}
	public function investment_package_submit()
	{
		$old_value  = Tbl_investment_package::get();
		foreach(Request::input() as $key => $package)
		{
			$data['investment_package_id'] 				= $package['investment_package_id'];
			$data['investment_package_days_bond'] 		= $package['investment_package_days_bond'];
			$data['investment_package_min_interest'] 	= $package['investment_package_min_interest'];
			$data['investment_package_max_interest'] 	= $package['investment_package_max_interest'];
			$data['investment_package_days_margin'] 	= $package['investment_package_days_margin'];
			$data['bind_membership'] 					= $package['bind_membership'];
			$data['archive'] 							= $package['archive'];
			$count = Tbl_investment_package::where('investment_package_id',$package['investment_package_id'])->count();
			if($count==0)
			{
				Tbl_investment_package::insert($data);
			}
			else
			{
				Tbl_investment_package::where('investment_package_id',$package['investment_package_id'])->update($data);
			}

		}
		$new_value  = Tbl_investment_package::get();
		$action     = "Package Submit";
		$user       = Request::user()->id;
		Audit_trail::audit(serialize($old_value),serialize($new_value),$user,$action);
		
		$response['status_message'] = "Package Successfully Updated!";
		return response()->json($response, 200);
	}

	public function load_board_settings()
	{
		$data = DB::table('tbl_mlm_board_settings')->get();
		foreach($data as $key => $value)
		{
			$return['board_depth'] = $value->board_depth;
			$return['graduation_bonus'][$key] = $value->graduation_bonus;
			$return['board_logic'] = $value->board_logic;
		}
		$return['board_levels'] = count($data);
		return $return;
	}

	public function update_membership_upgrade()
	{
		$data = Request::input('data');
		$settings = Request::input('settings');
		$id       = Tbl_membership_upgrade_settings::first()->membership_upgrade_settings_id;
		foreach($data as $key => $value)
		{
			$update['required_directs'] 		= $value['required_directs'];
			$update['required_downlines'] 		= $value['required_downlines'];
			$update['required_upgrade_points'] 	= $value['required_upgrade_points'];
			$update['given_upgrade_points'] 	= $value['given_upgrade_points'];
			DB::table('tbl_membership')->where('membership_id', $value['membership_id'])->update($update);
		}
		// dd($settings);
		$update2["membership_upgrade_settings_method"] = $settings['membership_upgrade_settings_method'];
		// $update2["membership_upgrade_settings_flushout"] = $settings['membership_upgrade_settings_flushout'];
		Tbl_membership_upgrade_settings::where("membership_upgrade_settings_id",$id)->update($update2);
		$return = Plan::update_status("MEMBERSHIP_UPGRADE",1);

		return response()->json($return);
	}
	public function update_sign_up_bonus()
	{
		$plan 	  = Request::input("plan"); 
		$label    = Request::input("label");
		Plan::update_label($plan,$label);
		$data = Request::input('data');
		foreach($data as $key => $value)
		{
			$new_value  = Tbl_membership::where('membership_id', $value['membership_id'])->first();
			$update['sign_up_bonus'] 		= $value['sign_up_bonus'];
			$update['sign_up_minimum'] 		= $value['sign_up_minimum'];
			$update['sign_up_voucher_use'] 	= $value['sign_up_voucher_use'];

			DB::table('tbl_membership')->where('membership_id', $value['membership_id'])->update($update);

			$old_value  = Tbl_membership::where('membership_id', $value['membership_id'])->first();
			$action     = "Update Sign Up Bonus";
			$user       = Request::user()->id;
			Audit_trail::audit(serialize($old_value),serialize($new_value),$user,$action);
		}
		$return = Plan::update_status("SIGN_UP_BONUS",1);

		return response()->json($return);
	}
	public function update_personal_cashback()
	{
		$data = Request::input('data');
		foreach($data as $key => $value)
		{
			$new_value  = Tbl_membership::where('membership_id', $value['membership_id'])->first();
			$update['cashback_percent'] = $value['cashback_percent'];

			DB::table('tbl_membership')->where('membership_id', $value['membership_id'])->update($update);

			$old_value  = Tbl_membership::where('membership_id', $value['membership_id'])->first();
			$action     = "Update Personal Cashback";
			$user       = Request::user()->id;
			Audit_trail::audit(serialize($old_value),serialize($new_value),$user,$action);
		}
		$return = Plan::update_status("PERSONAL_CASHBACK",1);

		return response()->json($return);
	}
	public function get_investment_amount()
	{
		$response = Tbl_investment_amount::first();
		return $response;
	}
	public function update_investment_amount()
	{
		$min_amount							= Request::input('min_amount');
		$max_amount							= Request::input('max_amount');
		
		if($min_amount >= 1 && $max_amount != 0)
		{
			if($max_amount >= $min_amount)
			{
				$update['min_amount']		= $min_amount;
				$update['max_amount']		= $max_amount;

				Tbl_investment_amount::where('id',1)->update($update);

				$return['status'] 			= 'Success';
				$return['status_message']	= 'Investment Amount updated sucessfully';
			}
			else
			{
				$return['status'] 			= 'Error';
				$return['status_message']	= 'Minimun amount must be less than to Maximum Amount';
			}
				
		}
		else
		{
			$return['status'] 				= 'Error';
			$return['status_message']		= 'Please input correct value';

		}
		return $return;
	}
	public function update_retailer_commission()
	{

		$label								 = Request::input('label');
		$commission							 = Request::input('commission');
		$get_old_label						 = Tbl_label::where('plan_code', 'RETAILER_COMMISSION')->first();
		$update_plan["mlm_plan_enable"]		 = 1;

		Plan::update_label('RETAILER_COMMISSION',$label);
		Tbl_mlm_plan::where("mlm_plan_code",'RETAILER_COMMISSION')->update($update_plan);
		Tbl_wallet_log::where('wallet_log_details',$get_old_label->plan_name)->update(['wallet_log_details' => $label]);

		foreach ($commission as $key => $value) 
		{
			Tbl_membership::where('membership_id',$value['membership_id'])->update(['retailer_commission' => $value['retailer_commission']]);
		}

		$return['status'] 					= 'Success';
		$return['status_message']			= 'Retailer Commission updated sucessfully';
		return $return;

	}
	public function update_share_link_v2()
	{
		$data													= Request::input('data');
		$label													= Request::input('label');
		$old_label 												= Tbl_label::where('plan_code','SHARE_LINK_V2')->first()->plan_name;

		Tbl_earning_log::where('earning_log_plan_type',$old_label)->update(['earning_log_plan_type' => $label]);
		Tbl_wallet_log::where('wallet_log_details',$old_label)->update(['wallet_log_details' => $label]);
		Tbl_label::where('plan_code','SHARE_LINK_V2')->update(['plan_name' => $label]);

		foreach ($data as $key => $value) 
		{
			$update['share_link_maximum_income'] 				= $value['share_link_maximum_income'];
			$update['share_link_maximum_register_per_day'] 		= $value['share_link_maximum_register_per_day'];
			$update['share_link_income_per_registration'] 		= $value['share_link_income_per_registration'];
			
			Tbl_membership::where('membership_id',$value['membership_id'])->update($update);
		}

		$return['status'] 					= 'Success';
		$return['status_message']			= 'Plan Updated';
		return $return;
	}
}
