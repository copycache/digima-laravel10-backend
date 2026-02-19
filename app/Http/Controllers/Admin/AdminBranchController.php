<?php
namespace App\Http\Controllers\Admin;

use App\Globals\Branch;
use App\Globals\Audit_trail;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\DB;
class AdminBranchController extends AdminController
{
    public function add_branch() 
	{
	    $response = Branch::add(Request::input());
		
	    return response()->json($response, $response["status_code"]);
	}

	public function get_branch()
	{
		$response = Branch::get(Request::input('status'));

		return response()->json($response, 200);
	}

	public function data()
	{
		$response = Branch::get_data(Request::input("id"));
		return response()->json($response, 200);
	}

	public function archive()
	{
		$response = Branch::archive(Request::input("id"));
		return response()->json($response, 200);
	}
	public function restore()
	{
		$response = Branch::restore(Request::input("id"));
		return response()->json($response, 200);
	}

	public function edit()
	{
		$response = Branch::edit(Request::input());
		return response()->json($response);
	}

	public function search()
	{	
		$response = Branch::search(Request::input());
		return response()->json($response, 200);
	}

	public function get_stockist()
	{	
		$response = Branch::get_stockist();
		return response()->json($response, 200);
	}

	public function add_stockist_level()
	{	
		$data 		= Request::input('stockist');
		$response = Branch::add_stockist($data);
		return response()->json($response);
	}

	public function archive_stockist_level()
	{
		
		$data 		= Request::input('level_name');
		$response 	= Branch::archive_stockist($data);

		return response()->json($response);
	}

	public function get_access_list()
	{
		$branch_id = Request::input('branch_id');

		$response = Branch::get_access_list($branch_id);

		return response()->json($response);

	}

	public function access_list_submit()
	{
		$data = Request::input('access_list');

		$response = Branch::access_list_submit($data);

		return response()->json($response);

	}

	public function save_company_info()
	{
		$check = DB::table('tbl_company_details')->first();
		$user  = Request::user()->id;
		if($check)
		{
			$update['company_name']		= Request::input('company_name');
			$update['street']			= Request::input('street');
			$update['city']				= Request::input('city');
			$update['state']			= Request::input('state');
			$update['contact_number']	= Request::input('contact_number');
			$update['contact_email']	= Request::input('contact_email');
			$action = "Edit Company Info";
			$old_value = DB::table('tbl_company_details')->where('company_details_id', $check->company_details_id)->first();
			DB::table('tbl_company_details')->where('company_details_id', $check->company_details_id)->update($update);
			$new_value = DB::table('tbl_company_details')->where('company_details_id', $check->company_details_id)->first();
			Audit_trail::audit(serialize($old_value),serialize($new_value),$user,$action);
		}
		else
		{
			$insert['company_name']		= Request::input('company_name');
			$insert['street']			= Request::input('street');
			$insert['city']				= Request::input('city');
			$insert['state']			= Request::input('state');
			$insert['contact_number']	= Request::input('contact_number');
			$insert['contact_email']	= Request::input('contact_email');
			$action = "Create Company Info";
			$company_id = DB::table('tbl_company_details')->insertGetId($insert);
			$new_value = DB::table('tbl_company_details')->where('company_details_id',$company_id)->first();
			Audit_trail::audit(null,serialize($new_value),$user,$action);
		}
		
		$return['status_code'] = 200;
		$return['status_message'] = 'success';

		return response()->json($return);

		
	}

	public function load_company_info()
	{
		$return = DB::table('tbl_company_details')->first();

		return response()->json($return);
	}
}
