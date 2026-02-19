<?php
namespace App\Http\Controllers\Admin;

use App\Models\Tbl_membership;
use App\Models\Tbl_currency;
use App\Models\Tbl_other_settings;
use App\Models\Tbl_income_limit_settings;

use App\Globals\Seed;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\DB;
class AdminSettingsController extends AdminController
{
	public function seed()
	{
		Seed::other_settings_seed();
	}

	public function codevault()
	{
		$data["show_slot_code"]       = Tbl_other_settings::where("key","show_slot_code")->first()->value;
		$data["show_product_code"]	  = Tbl_other_settings::where("key","show_product_code")->first()->value;
		$data["membership_kit_upgrade"]	 = Tbl_other_settings::where("key","membership_kit_upgrade")->first()->value;

		return Response()->json($data);
	}

	public function codevault_update()
	{
		Tbl_other_settings::where('key',"show_slot_code")->update(["value"=>Request::input("show_slot_code")]);
		Tbl_other_settings::where('key',"show_product_code")->update(["value"=>Request::input("show_product_code")]);
		Tbl_other_settings::where('key',"membership_kit_upgrade")->update(["value"=>Request::input("membership_kit_upgrade")]);


		$data["show_slot_code"]      = Tbl_other_settings::where("key","show_slot_code")->first()->value;
		$data["show_product_code"]	 = Tbl_other_settings::where("key","show_product_code")->first()->value;
		$data["membership_kit_upgrade"]	 = Tbl_other_settings::where("key","membership_kit_upgrade")->first()->value;

		return Response()->json($data);
	}

	public function retailer()
	{
		$data["retailer"]        = Tbl_other_settings::where("key","retailer")->first()->value;
		$data["max_retailer"]	 = Tbl_other_settings::where("key","max_retailer")->first()->value;
		$data["dealers_bonus"]	 = Tbl_other_settings::where("key","dealers_bonus")->first()->value;

		return Response()->json($data);
	}

	public function retailer_update()
	{
		Tbl_other_settings::where('key',"retailer")->update(["value"=>Request::input("retailer")]);
		Tbl_other_settings::where('key',"max_retailer")->update(["value"=>Request::input("max_retailer")]);
		Tbl_other_settings::where('key',"dealers_bonus")->update(["value"=>Request::input("dealers_bonus")]);


		$data["retailer"]        = Tbl_other_settings::where("key","retailer")->first()->value;
		$data["max_retailer"]	 = Tbl_other_settings::where("key","max_retailer")->first()->value;
		$data["dealers_bonus"]	 = Tbl_other_settings::where("key","dealers_bonus")->first()->value;

		return Response()->json($data);
	}

	public function codeactivate()
	{
		$data["register_on_slot"]    = Tbl_other_settings::where("key","register_on_slot")->first()->value;
		$data["register_your_slot"]	 = Tbl_other_settings::where("key","register_your_slot")->first()->value;
		$data["product_activate"]	 = Tbl_other_settings::where("key","product_activate")->first()->value;
		$data["name_on_dropdown"]	 = Tbl_other_settings::where("key","name_on_dropdown")->first()->value;

		return Response()->json($data);
	}

	public function codeactivate_update()
	{
		Tbl_other_settings::where('key',"register_on_slot")->update(["value"=>Request::input("register_on_slot")]);
		Tbl_other_settings::where('key',"register_your_slot")->update(["value"=>Request::input("register_your_slot")]);
		Tbl_other_settings::where('key',"product_activate")->update(["value"=>Request::input("product_activate")]);
		Tbl_other_settings::where('key',"name_on_dropdown")->update(["value"=>Request::input("name_on_dropdown")]);

		$data["register_on_slot"]    = Tbl_other_settings::where("key","register_on_slot")->first()->value;
		$data["register_your_slot"]	 = Tbl_other_settings::where("key","register_your_slot")->first()->value;
		$data["product_activate"]	 = Tbl_other_settings::where("key","product_activate")->first()->value;
		$data["name_on_dropdown"]    = Tbl_other_settings::where("key","name_on_dropdown")->first()->value;

		return Response()->json($data);
	}

	public function slot()
	{
		$data["allow_slot_transfer"]    = Tbl_other_settings::where("key","allow_slot_transfer")->first()->value;
		$data["slot_transfer"]		    = Tbl_other_settings::where("key","slot_transfer")->first()->value;
		$data["default_slot_limit"]     = Tbl_other_settings::where("key","default_slot_limit")->first()->value;
		$data["default_added_days"]     = Tbl_other_settings::where("key","default_added_days")->first()->value;

		return Response()->json($data);
	}

	public function slot_update()
	{
		Tbl_other_settings::where('key',"allow_slot_transfer")->update(["value"=>Request::input("allow_slot_transfer")]);
		Tbl_other_settings::where('key',"slot_transfer")->update(["value"=>Request::input("slot_transfer")]);
		Tbl_other_settings::where('key',"default_slot_limit")->update(["value"=>Request::input("default_slot_limit")]);
		Tbl_other_settings::where('key',"default_added_days")->update(["value"=>Request::input("default_added_days")]);


		$data["allow_slot_transfer"]    = Tbl_other_settings::where("key","allow_slot_transfer")->first()->value;
		$data["slot_transfer"]		    = Tbl_other_settings::where("key","slot_transfer")->first()->value;
		$data["default_slot_limit"]     = Tbl_other_settings::where("key","default_slot_limit")->first()->value;
		$data["default_added_days"]     = Tbl_other_settings::where("key","default_added_days")->first()->value;

		return Response()->json($data);
	}

	public function registration()
	{
		$data["register_google"]        = Tbl_other_settings::where("key","register_google")->first()->value;
		$data["register_facebook"]		= Tbl_other_settings::where("key","register_facebook")->first()->value;
		$data["allow_duplicated_name"]  = Tbl_other_settings::where("key","allow_duplicated_name")->first()->value;

		return Response()->json($data);
	}

	public function registration_update()
	{
		Tbl_other_settings::where('key',"register_google")->update(["value"=>Request::input("register_google")]);
		Tbl_other_settings::where('key',"register_facebook")->update(["value"=>Request::input("register_facebook")]);
		Tbl_other_settings::where('key',"allow_duplicated_name")->update(["value"=>Request::input("allow_duplicated_name")]);


		$data["register_google"]        = Tbl_other_settings::where("key","register_google")->first()->value;
		$data["register_facebook"]		= Tbl_other_settings::where("key","register_facebook")->first()->value;
		$data["allow_duplicated_name"]  = Tbl_other_settings::where("key","allow_duplicated_name")->first()->value;

		return Response()->json($data);
	}

	public function load_shipping_info()
	{
		$check = DB::table('tbl_shipping_fee_matrix')->first();
		if(!$check)
		{
			$insert["shipping_fee_increment"] = 1;
			$insert["shipping_fee_increment_amount"] = 0;
			$insert["shipping_fee_matrix_start_amount"] = 0;
			DB::table('tbl_shipping_fee_matrix')->insert($insert);
		}
		$data = DB::table('tbl_shipping_fee_matrix')->first();
		return Response()->json($data);
	}

	public function manage_shipping_fee()
	{
		$id = Request::input("shipping_fee_matrix_id");
        $update["shipping_fee_increment"] = Request::input("shipping_fee_increment");
        $update["shipping_fee_increment_amount"] = Request::input("shipping_fee_increment_amount");
        $update["shipping_fee_matrix_start_amount"] = Request::input("shipping_fee_matrix_start_amount");
		DB::table('tbl_shipping_fee_matrix')->where("shipping_fee_matrix_id",$id)->update($update);
		// return Response()->json($data);
	}
	public function lockdown_settings()
	{
		$data["lockdown_grace_period"]        = Tbl_other_settings::where("key","lockdown_grace_period")->first()->value;

		return Response()->json($data);
	}

	public function lockdown_settings_update()
	{
		Tbl_other_settings::where('key',"lockdown_grace_period")->update(["value"=>Request::input("lockdown_grace_period")]);

		$data["lockdown_grace_period"]  = Tbl_other_settings::where("key","lockdown_grace_period")->first()->value;
		return Response()->json($data);
	}

	public function load_breakdown_items()
	{
		$data["breakdown_gc"]        = Tbl_other_settings::where("key","breakdown_gc")->first()->value;
		$data["breakdown_left_and_right"]        = Tbl_other_settings::where("key","breakdown_left_and_right")->first()->value;
		// dd($data);
		return Response()->json($data);
	}

	public function update_breakdown_items()
	{
		// dd(Request::input());
		Tbl_other_settings::where('key',"breakdown_gc")->update(["value"=>Request::input("breakdown_gc")]);
		Tbl_other_settings::where('key',"breakdown_left_and_right")->update(["value"=>Request::input("breakdown_left_and_right")]);

		$data["breakdown_gc"]  = Tbl_other_settings::where("key","breakdown_gc")->first()->value;
		$data["breakdown_left_and_right"]  = Tbl_other_settings::where("key","breakdown_left_and_right")->first()->value;
		return Response()->json($data);
	}

	public function load_tin_settings()
	{
		$data["tin_settings"]        = Tbl_other_settings::where("key","tin_settings")->first()->value;
		return Response()->json($data);
	}

	public function update_tin_settings()
	{
		// dd(Request::input());
		Tbl_other_settings::where('key',"tin_settings")->update(["value"=>Request::input("tin_settings")]);

		$data["tin_settings"]  = Tbl_other_settings::where("key","tin_settings")->first()->value;
		return Response()->json($data);
	}

	public function load_income_limit_settings()
	{
		//Checking Data
		$check = Tbl_income_limit_settings::first();

		if(!$check) {

			$insert['income_limit_status'] 	= 'disable';
			$insert['income_limit'] 		= 0;
			$insert['income_limit_cycle'] 	= 'daily';
			Tbl_income_limit_settings::insert($insert);
		}

		//Response
		$reponse = Tbl_income_limit_settings::first();
		return Response()->json($reponse);
	}

	public function update_income_limit_settings()
	{
		$data = Request::input();
		//Update Data

		$check = Tbl_income_limit_settings::first();

		$update['income_limit_status'] 	= $data['income_limit_status'] ?? null;
		$update['income_limit'] 		= $data['income_limit'] ?? null;
		$update['income_limit_cycle'] 	= $data['income_limit_cycle'] ?? null;
		Tbl_income_limit_settings::where('income_limit_id',$data['income_limit_id'] ?? null)->update($update);
	}

	public function load_leaderboard()
	{
		//Checking Data
		$reponse["announcement"]       = Tbl_other_settings::where("key","announcement")->first()->value;
		$reponse["bday_corner"]        = Tbl_other_settings::where("key","bday_corner")->first()->value;
		$reponse["top_earners"]        = Tbl_other_settings::where("key","top_earners")->first()->value;
		return Response()->json($reponse);
	}

	public function update_leaderboard()
	{
		Tbl_other_settings::where('key',"announcement")->update(["value"=>Request::input("announcement")]);
		Tbl_other_settings::where('key',"bday_corner")->update(["value"=>Request::input("bday_corner")]);
		Tbl_other_settings::where('key',"top_earners")->update(["value"=>Request::input("top_earners")]);
		
		$reponse["announcement"]       = Tbl_other_settings::where("key","announcement")->first()->value;
		$reponse["bday_corner"]        = Tbl_other_settings::where("key","bday_corner")->first()->value;
		$reponse["top_earners"] 	   = Tbl_other_settings::where("key","top_earners")->first()->value;
		return Response()->json($reponse);
	}


		
}
