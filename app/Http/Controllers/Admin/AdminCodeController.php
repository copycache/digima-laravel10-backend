<?php
namespace App\Http\Controllers\Admin;

use App\Globals\Code;
use App\Globals\Item;
use App\Globals\Audit_trail;
use Illuminate\Support\Facades\Request;

class AdminCodeController extends AdminController
{
    public function generate_codes() 
	{
		$branch_id			= Request::input('branch_id');
		$item_id			= Request::input('item_id');
		$quantity			= Request::input('quantity');
		$data['branch_id']  = $branch_id;
		$data['item_id']  	= $item_id;
		$data['quantity']  	= $quantity;
		$user               = Request::user()->id;
		$response 			= Code::generate($branch_id,$item_id,$quantity);
		$action = "Generate Code";
		Audit_trail::audit(null,serialize($data),$user,$action);
	    $update_inventory 	= Item::update_inventory($branch_id,$item_id,$quantity);

	    return response()->json($response);
	}

	public function get_codes() 
	{
		$branch_id 			= Request::input('branch_id');
		$filter 			= Request::input('filter');
		$item_id			= Request::input('item_id');
	    $response = Code::get($branch_id,$filter,$item_id);

	    return response()->json($response);
	}

	public function delete_code() 
	{
		$code_id 			= Request::input('code_id');
		$archived 			= Request::input('archived');
		$user 		    	= Request::input('user');
		$response 			= Code::delete($code_id,$archived,$user);

	    return response()->json($response);
	}

	public function get_random_code()
	{
		$user           = Request::input('user_id');
		$response 		= Code::get_random($user);

		return response()->json($response);
	}
	
}
