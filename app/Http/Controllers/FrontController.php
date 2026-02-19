<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Session;
use View;
use App\Globals\Cart;
use App\Models\Tbl_item;
use App\Models\Tbl_currency;

class FrontController extends Controller
{
    function __construct(Request $request)
    {
        $count = 0;

        $cart = Cart::get_cart();

        if($cart)
        {
            $count = count($cart);
        }


        $services   = Tbl_item::where('item_type','non_inventory')->where('archived',0)->where('item_category','services')->get();
        $property   = Tbl_item::where('item_type','non_inventory')->where('archived',0)->where('item_category','property')->get();
        $product    = Tbl_item::where('item_type','product')->where('archived',0)->get();
        

        $currency   = Tbl_currency::where('currency_buying',1)->first();

        View::share('currency', $currency ? $currency->currency_abbreviation : 'PHP');
        View::share('prefix', ($currency && $currency->currency_abbreviation == 'BTC') ? 8 : 2);
        

        View::share('services',$services);
        View::share('property',$property);
        View::share('product',$product);

        View::share('cart_count',$count);
        View::share('cart_key',Session::get('cart_key'));
    }
}
