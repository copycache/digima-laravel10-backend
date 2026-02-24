<?php
namespace App\Http\Controllers\Member;

use App\Globals\Eloading;
use App\Models\Tbl_eloading_product;
use App\Models\Tbl_eloading_tab_settings;
use Carbon\Carbon;
use Illuminate\Support\Facades\Request;

class MemberEloadingController extends MemberController
{
    public function get_wallet()
    {
        $slot_id = Request::input('slot_id');
        $user_id = Request::user()->id;
        $wallet = Eloading::get_wallet($slot_id);
        return response()->json($wallet);
    }

    public function get_eload_settings()
    {
        $response = Eloading::get_settings();
        return response()->json($response);
    }

    public function get_product_list()
    {
        $response = Tbl_eloading_tab_settings::where('eloading_tab_active', '!=', 0)->get();

        $active_tab_names = $response->pluck('eloading_tab_name')->unique()->filter();
        $products_all = Tbl_eloading_product::whereIn('eloading_product_type', $active_tab_names)->get()->groupBy('eloading_product_type');

        foreach ($response as $key => $value) {
            $tab_name = $value->eloading_tab_name;
            $tab = 'others';
            if ($tab_name == 'ELOAD')
                $tab = 'eload';
            else if ($tab_name == 'CALL CARDS')
                $tab = 'call_cards';
            else if ($tab_name == 'GAMES')
                $tab = 'games';
            else if ($tab_name == 'SATELLITE')
                $tab = 'sattelite';
            else if ($tab_name == 'PORTAL')
                $tab = 'portal';

            $response[$key]['tab'] = $tab;
            $tab_products = $products_all->get($tab_name, collect());
            $response[$key][$tab] = $tab_products;
            $response[$key]['subscriber'] = $tab_products->unique('eloading_product_subscriber')->values();
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
        $response = Tbl_eloading_product::where('eloading_product_type', Request::input('title'));

        if (Request::input('search') != '') {
            $response = $response->Search(Request::input('search'));
        }
        if (Request::input('filter') != 'all') {
            $response = $response->where('eloading_product_subscriber', Request::input('filter'));
        }

        $response = $response->get();

        return Response()->json($response);
    }
}
