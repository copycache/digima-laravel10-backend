<?php

namespace App\Http\Controllers;

use App\Globals\Audit_trail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SecretController extends Controller
{
    public function get()
    {
        return response()->json([
            'oauth' => DB::table('oauth_clients')->find(2),
            'maintenance' => DB::table('tbl_mlm_feature')->where('mlm_feature_name', 'website_maintenance')->first()
        ]);
    }

    public function logout()
    {
        $accessToken = Auth::user()->token();
        
        Audit_trail::audit(null, null, $accessToken->user_id, 'Logout');
        
        DB::table('oauth_refresh_tokens')
            ->where('access_token_id', $accessToken->id)
            ->update(['revoked' => true]);

        $accessToken->revoke();

        return response()->json(null, 204);
    }
}
