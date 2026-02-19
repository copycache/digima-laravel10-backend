<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Globals\Audit_trail;

class SecretController extends Controller
{
    public function get()
    {
		$return['oauth'] = DB::table('oauth_clients')->where('id', 2)->first();
		$return['maintenance'] = DB::table('tbl_mlm_feature')->where('mlm_feature_name', 'website_maintenance')->first();
    	return response()->json($return);
    }

    public function logout() 
    {
		$accessToken = Auth::user()->token();
		$action = 'Logout';
        $old_value = null;
        $new_value = null;
		Audit_trail::audit($old_value,$new_value,$accessToken->user_id,$action);
	    DB::table('oauth_refresh_tokens')
	        ->where('access_token_id', $accessToken->id)
	        ->update([
	            'revoked' => true
	        ]);
		
		$accessToken->revoke();
		
	    return response()->json(null, 204);
	}
}
