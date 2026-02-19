<?php
namespace App\Http\Controllers\Admin;

use App\Globals\CashIn;
use App\Tbl_cash_in_method;
use App\Models\Tbl_currency;

use Illuminate\Support\Facades\Request;
use Hash;
class AdminCashInController extends AdminController
{
	public function get_currency()
	{
		
		$response = Tbl_currency::get();
	    return response()->json($response);
	}

	public function get_method_category_list() 
	{
	    $response = CashIn::get_method_category_list();

	    return response()->json($response);
	}

	public function add_new_method() 
	{

	    $response = CashIn::add_new_method(Request::input(),Request::user()->id);

	    return response()->json($response);
	}

	public function update_method() 
	{
	    $response = CashIn::update_method(Request::input(),Request::user()->id);

	    return response()->json($response);
	}

	public function archive_method() 
	{
	    $response = CashIn::archive_method(Request::input('cash_in_method_id'), Request::input('archive'),Request::user()->id);

	    return response()->json($response);
	}

	public function process_transaction()
	{
		$response = CashIn::process_transaction(Request::input(),Request::user()->id);

		return response()->json($response);
	}

	public function process_all_transaction()
	{
		$response = CashIn::process_all_transaction(Request::input(),Request::user()->id);

		return response()->json($response);
	}
}
