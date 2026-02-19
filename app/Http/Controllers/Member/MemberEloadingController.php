<?php
namespace App\Http\Controllers\Member;


use App\Models\Tbl_eloading_product;
use App\Models\Tbl_eloading_tab_settings;
use App\Globals\Eloading;

use Carbon\Carbon;
use Illuminate\Support\Facades\Request;


class MemberEloadingController extends MemberController
{
    public function get_wallet()
    {
        $slot_id = Request::input('slot_id');
        $user_id = Request::user()->id;
        $wallet  = Eloading::get_wallet($slot_id);
        return response()->json($wallet);

    }

    public function get_eload_settings()
    {
        $response = Eloading::get_settings();
        return response()->json($response);
    }

    public function get_product_list()
    {
        $response = Tbl_eloading_tab_settings::where('eloading_tab_active','!=',0)->get();

        foreach ($response as $key => $value) 
        {
            if($value->eloading_tab_name=='ELOAD')
            {
                $tab = "eload";
            }
            if($value->eloading_tab_name=='CALL CARDS')
            {
                $tab = "call_cards";   
            }
            if($value->eloading_tab_name=='GAMES')
            {
                $tab = "games";   
            }
            if($value->eloading_tab_name=='SATELLITE')
            {
                $tab = "sattelite";   
            }

            if($value->eloading_tab_name=='OTHERS')
            {
                $tab = "others";   
            }

            if($value->eloading_tab_name=='PORTAL')
            {
                $tab = "portal";   
            }
            $response[$key]['tab']          = $tab;
            $response[$key][$tab]           = Tbl_eloading_product::where('eloading_product_type',$value->eloading_tab_name)->get();
            $response[$key]["subscriber"]   = Tbl_eloading_product::where('eloading_product_type',$value->eloading_tab_name)->DistinctSubscriber()->get();
        }
        return Response()->json($response);
    }

    public static function eloading_submit()
    {
        $response = Eloading::eloading_submit(Request::all());
       
        return Response()->json($response);
    }

    public function search()
    {
        $response = Tbl_eloading_product::where('eloading_product_type',Request::input('title'));

        if(Request::input('search')!="")
        {
            $response = $response->Search(Request::input('search'));
        }
        if(Request::input('filter')!='all')
        {
            $response = $response->where('eloading_product_subscriber',Request::input('filter'));
        }



        $response = $response->get();

        return Response()->json($response);
    }
    

}
