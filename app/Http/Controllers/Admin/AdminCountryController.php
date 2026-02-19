<?php
namespace App\Http\Controllers\Admin;

use App\Globals\Country;

use Illuminate\Support\Facades\Request;

class AdminCountryController extends AdminController
{
	public function get()
	{	
		$response = Country::get();
		return response()->json($response, 200);
	}
}
