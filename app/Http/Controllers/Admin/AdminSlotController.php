<?php
namespace App\Http\Controllers\Admin;

use App\Globals\Slot;
use App\Models\Tbl_stairstep_rank;
use App\Models\Tbl_membership;
use App\Models\Tbl_currency;
use App\Models\Tbl_livewell_rank;

use Illuminate\Support\Facades\Request;

class AdminSlotController extends AdminController
{
	public function get()
	{
		$response = Slot::get();
		return response()->json($response, 200);
	}

	public function get_full()
	{
		$response = Slot::get_full(Request::input(), 10);
		return response()->json($response, 200);
	}

	public function get_full_unilevel()
	{
		$response = Slot::get_full_unilevel(Request::input());
		return response()->json($response, 200);
	}
	public function get_unilevel_list()
	{
		$response = Slot::get_unilevel_list(Request::input());
		return response()->json($response, 200);
	}

	public function get_unplaced()
	{
		$response = Slot::get_unplaced(Request::input('name'));
		return response()->json($response, 200);
	}

	public function get_filters()
	{
		$response['ranks'] 		= Tbl_stairstep_rank::where("archive",0)->get();
		$response['membership']	= Tbl_membership::where("archive",0)->get();
		$response['livewell_rank']	= Tbl_livewell_rank::where("archive",0)->get();
		return response()->json($response);
	}

	public static function get_currency()
	{
		$response['currency_lists'] = Tbl_currency::where('archive', 0)->get();
		$response['default_currency'] = Tbl_currency::where('archive', 0)->where('currency_default','=',1)->first();
		return $response;
	}
}
