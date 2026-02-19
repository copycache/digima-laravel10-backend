<?php
namespace App\Http\Controllers\Member;


use App\Models\Tbl_slot;
use App\Globals\Slot;

use Carbon\Carbon;
use Illuminate\Support\Facades\Request;


class MemberSponsorController extends MemberController
{
    public function get_sponsor_list()
    {
        $slot_id    = Request::input('slot_id');
        $response['d_sponsor']   = Tbl_slot::Owner()->where('slot_sponsor',$slot_id)->where('slot_sponsor_member',0)->get();
        $response['l_sponsor']     = Tbl_slot::Owner()->where('slot_sponsor',$slot_id)->get();

        foreach ($response['l_sponsor'] as $key => $value) 
        {
           $response['l_sponsor'][$key]['sponsor_username'] = Tbl_slot::where('slot_id', $value['slot_sponsor'])->pluck('slot_no')->first();
        }
        return response()->json($response);

    }

    public function activate_slot()
    {

        $data["pin"]           = Request::input("pin");
        $data["code"]          = Request::input("code");
        $data["slot_sponsor"]  = Request::input("slot_sponsored");
        $data["slot_owner"]    = Request::input("id");

        

        $response              = Slot::create_slot($data);
        return $response;
    }
    
    

}
