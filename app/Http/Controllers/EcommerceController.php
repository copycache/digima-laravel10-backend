<?php

namespace App\Http\Controllers;

use App\Globals\Cart;
use App\Models\Tbl_item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EcommerceController extends FrontController
{
    public function index()
    {
        $data["Page"]       = "Home";
        $items = Tbl_item::whereIn('item_type', ['non_inventory', 'product'])
                         ->where('archived', 0)
                         ->get();

        $data['services']      = $items->where('item_category', 'services');
        $data['property']      = $items->where('item_category', 'property');
        $data['other_product'] = $items->where('item_category', 'other_product');
        $data['product']       = $items->where('item_type', 'product');

        return view ("ecommerce.pages.home", $data);
    }

    public function products($type)
    {
        $search_key = match($type) {
            'services' => 'SERVICES',
            'property' => 'REAL STATE',
            'product' => 'GAMES',
            'search' => request('search_key'),
            default => abort(404, 'DO NOT TRY TO EDIT THE LINK!')
        };

        $query = Tbl_item::where('archived', 0);
        
        if ($type === 'search') {
            $items = $query->Search($search_key)->where('item_type', '!=', 'membership_kit')->get();
        } elseif ($type === 'product') {
            $items = $query->where('item_type', 'product')->get();
        } else {
            $items = $query->where('item_type', 'non_inventory')->where('item_category', $type)->get();
        }

        return view('ecommerce.pages.product', [
            'Page' => 'Product Page',
            '_item' => $items,
            'type' => $type,
            'header' => "Showing <b>" . count($items) . "</b> results related to <b>'" . strtoupper($search_key) . "'</b>"
        ]);
    }
    
    public function product_view($item_id)
    {
        return view('ecommerce.pages.product-view', [
            'Page' => 'Product View',
            'item' => Tbl_item::where(['item_id' => $item_id, 'archived' => 0])->first(),
            'product' => Tbl_item::where(['item_type' => 'product', 'archived' => 0])->get()
        ]);
    }

    public function cart_item(Request $request)
    {
        $item_id = $request->input('item_id');
        $quantity = $request->input('quantity');

        if ($item_id && $quantity) {
            Cart::add_cart($request);
        }

        $cart = Cart::get_cart();
        $sub_total = 0;

        foreach ($cart as $key => $item) {
            $cart[$key]['item_quantity'] = $item['cart_item_quantity'];
            $cart[$key]['item_total'] = $item['item_price'] * $item['cart_item_quantity'];
            $sub_total += $cart[$key]['item_total'];
        }

        return view('ecommerce.popups.my-cart', [
            'Page' => 'Cart Modal',
            'sub_total' => $sub_total,
            'cart' => $cart
        ]);
    }

    public function cart_item_remove(Request $request)
    {
        Cart::remove_item($request->input('item_id'));

        $cart = Cart::get_cart();
        $sub_total = 0;

        foreach ($cart as $key => $item) {
            $cart[$key]['item_quantity'] = $item['cart_item_quantity'];
            $cart[$key]['item_total'] = $item['item_price'] * $item['cart_item_quantity'];
            $sub_total += $cart[$key]['item_total'];
        }

        return $sub_total;
    }

    public function cart_item_chage_quantity(Request $request)
    {
        Cart::add_cart($request->all());
    }

    public function product_request_qoute(Request $request)
    {
        if ($request->isMethod('post')) {
            $input = $request->all();
            
            if (isset($input['name'], $input['email'], $input['phone'], $input['message'])) {
                DB::table('tbl_qoute_request')->insert([
                    'qoute_request_name' => $input['name'],
                    'qoute_request_email' => $input['email'],
                    'qoute_request_phone' => $input['phone'],
                    'qoute_request_message' => $input['message'],
                    'qoute_request_item_id' => $input['item_id']
                ]);
                return 'SUCCESS';
            }
            return 'ERROR';
        }

        return view('ecommerce.popups.request-qoute', ['Page' => 'Request a Qoute']);
    }

    public function product_request_qoute_success()
    {
        return view('ecommerce.popups.request-qoute-success', ['Page' => 'Request a Qoute Success']);
    }
}
