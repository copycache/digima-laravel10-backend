<?php

namespace App\Http\Controllers\Member;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Carbon\Carbon;
use App\Models\Tbl_other_settings;
use App\Models\Tbl_currency;
use App\Models\Tbl_earning_log;
use App\Models\Users;
use App\Models\Tbl_announcement;

class MemberLeaderBoardController extends MemberController
{
    public function load_settings()
	{
		$reponse["announcement"]       = Tbl_other_settings::where("key","announcement")->first()->value;
		$reponse["bday_corner"]        = Tbl_other_settings::where("key","bday_corner")->first()->value;
		$reponse["top_earners"]        = Tbl_other_settings::where("key","top_earners")->first()->value;
		return Response()->json($reponse);
	}
    public function load_topearner()
    {
        $first_date             = date('Y-m-d',strtotime('first day of this month'));
        $today                  = date('Y-m-d',strtotime('today'));
        $currency_id            = Tbl_currency::where('currency_default',1)->first()->currency_id;
        
        $response               = Tbl_earning_log::where('earning_log_currency_id',$currency_id)
                                ->whereDate('earning_log_date_created','>=',$first_date)->whereDate('earning_log_date_created','<=',$today)
		                        ->leftjoin('tbl_slot','tbl_slot.slot_id','=','tbl_earning_log.earning_log_slot_id')->leftjoin('users','users.id','=','tbl_slot.slot_owner')->where('type','member')->where('users.top_earner_status',1)
                                ->select( DB::raw('sum(earning_log_amount) as sum_earn') , 'users.name','users.email','users.contact','users.profile_picture')->groupby('users.id','users.name','users.email','users.contact','users.profile_picture')
                                ->get();
	
		$response               = collect($response)->sortBy('sum_earn')->reverse()->toArray();
		$response               = array_slice($response, 0, 20);

		if(empty($response))
		{
			$response           = null;
		}
		else
		{
			$response           = $response;
		}
		return $response;
    }
    public function load_birthday_list()
    {
        $birthday_list                                      = Users::where('type','member')->get();
        $i                                                  = 0;
        foreach ($birthday_list as $key => $value) 
        {
            $birthdate                                      = explode(",",$value->birthdate);
            if($birthdate[0] == Carbon::now()->format('F'))
            {
                if($birthdate[1] == Carbon::now()->format('d'))
                {
                    $response[$i]['name']                       = $value->name;
                    $response[$i]['profile_picture']            = $value->profile_picture;
                    $response[$i]['age']                        = Carbon::now()->format('Y') - $birthdate[2];
                    $i++;
                }
            }
        }
        return $response ?? null;
    }
    public function load_announcement()
    {
        return  Tbl_announcement::where('status',1)->where('archived',0)->get();
    }
}
