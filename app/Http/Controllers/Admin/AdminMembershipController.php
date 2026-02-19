<?php
namespace App\Http\Controllers\Admin;
use App\Globals\Membership;

use Illuminate\Support\Facades\Request;
class AdminMembershipController extends AdminController
{
    public function get() 
	{
		$response = Membership::get();
	    return response()->json($response, 200);
	}
	
    public function get_manage_settings() 
	{
		$response = Membership::get_manage_settings();
	    return response()->json($response, 200);
	}

	public function submit()
	{
		$response = Membership::submit(Request::input());
		return response()->json($response, $response["status_code"]);
	}
}
