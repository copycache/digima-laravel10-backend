<?php

namespace App\Http\Controllers;

use App\Globals\Cart;
use App\Models\Tbl_currency;
use App\Models\Tbl_item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class FrontController extends Controller
{
    function __construct(Request $request)
    {
        $cart = Cart::get_cart();
        $count = $cart ? count($cart) : 0;

        $items = Tbl_item::whereIn('item_type', ['non_inventory', 'product'])
            ->where('archived', 0)
            ->get();

        $currency = Tbl_currency::where('currency_buying', 1)->first();

        View::share([
            'currency' => $currency?->currency_abbreviation ?? 'PHP',
            'prefix' => ($currency && $currency->currency_abbreviation == 'BTC') ? 8 : 2,
            'services' => $items->where('item_category', 'services'),
            'property' => $items->where('item_category', 'property'),
            'product' => $items->where('item_type', 'product'),
            'cart_count' => $count,
            'cart_key' => session('cart_key')
        ]);
    }
}
