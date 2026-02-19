<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Tbl_live_streaming_settings;


use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MemberLiveStreamingController extends Controller
{
   public function get_live_data()
   {
        $response = Tbl_live_streaming_settings::where('archived',0)->where('live_status',1)->first();
        
        return $response;
   }
}
