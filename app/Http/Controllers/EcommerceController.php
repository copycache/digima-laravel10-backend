<?php
/*
|--------------------------------------------------------------------------
| CONTROLLER COLLABORATOR
|--------------------------------------------------------------------------
|
| JAMES OMOSORA
|
*/
namespace App\Http\Controllers;
use App\Http\Controllers\FrontController;
use Carbon\Carbon;
use App\Globals\Digima;
use App\Models\Tbl_slot;
use App\Models\Tbl_item;
use App\Models\Tbl_earning_log;
use App\Models\Users;
use App\Models\Tbl_cart;
use Illuminate\Support\Facades\Request;
use Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

use App\Globals\Cart;

class EcommerceController extends FrontController
{
    public function index()
    {
        $data["Page"]       = "Home";
        $data['services']   = Tbl_item::where('item_type','non_inventory')->where('archived',0)->where('item_category','services')->get();
        $data['property']   = Tbl_item::where('item_type','non_inventory')->where('archived',0)->where('item_category','property')->get();
        $data['other_product']   = Tbl_item::where('item_type','non_inventory')->where('archived',0)->where('item_category','other_product')->get();
        $data['product']    = Tbl_item::where('item_type','product')->where('archived',0)->get();

        return view ("ecommerce.pages.home", $data);
    }

    public function products($type)
    {
        $data["Page"]        = "Product Page";
        if($type == 'services'):
            $search_key      = "SERVICES";
            $data['_item']   = Tbl_item::where('item_type','non_inventory')->where('archived',0)->where('item_category','services')->get();
        elseif($type == 'property'):
            $search_key      = "REAL STATE";
            $data['_item']   = Tbl_item::where('item_type','non_inventory')->where('archived',0)->where('item_category','property')->get();
        elseif($type == 'product'):
            $search_key      = "GAMES";
            $data['_item']    = Tbl_item::where('item_type','product')->where('archived',0)->get();
        elseif($type == 'search'):
            $search_key      = Request('search_key');
            $data['_item']   = Tbl_item::Search($search_key)->where('item_type','!=','membership_kit')->where('archived',0)->get();
        else:
            dd("DO NOT TRY TO EDIT THE LINK!");
        endif;
        $data['type']       = $type;
        $data['header']     = "Showing <b>".count($data['_item'])."</b> results related to <b>'".strtoupper($search_key)."'</b>";

        
        return view ("ecommerce.pages.product",$data);
    }
    
    public function product_view($item_id)
    {
        $data["Page"]       = "Product View";
        $data['item']       = Tbl_item::where('item_id',$item_id)->where('archived',0)->first();
        $data['product']    = Tbl_item::where('item_type','product')->where('archived',0)->get();
        return view ("ecommerce.pages.product-view", $data);
    }

    //POPUPS
    public function cart_item()
    {
        $data["Page"]   = "Cart Modal";
        $request        = Request();
        $item_id        = Request('item_id');
        $quantity       = Request('quantity');
        $sub_total      = 0;
        if($item_id && $quantity)
        {
            Cart::add_cart($request);
        }

        $cart = Cart::get_cart();

        foreach($cart as $key=>$carts)
        {
            $cart[$key]['item_quantity']    = $carts['cart_item_quantity'];
            $cart[$key]['item_total']       = $carts['item_price'] * $carts['cart_item_quantity'];
            $sub_total                      = $sub_total + $cart[$key]['item_total'];
        }
        $data['sub_total'] = $sub_total;
        $data['cart']      = $cart;


        return view ("ecommerce.popups.my-cart", $data);
    }

    public function cart_item_remove()
    {
        Cart::remove_item(Request::input('item_id'));

        $cart = Cart::get_cart();
        $sub_total = 0;
        foreach($cart as $key=>$carts)
        {
            $cart[$key]['item_quantity']    = $carts['cart_item_quantity'];
            $cart[$key]['item_total']       = $carts['item_price'] * $carts['cart_item_quantity'];
            $sub_total                      = $sub_total + $cart[$key]['item_total'];
        }
        $data['sub_total'] = $sub_total;
        $data['cart']      = $cart;

        return $sub_total;
    }

    public function cart_item_chage_quantity()
    {
        $request    = Request::all();
        Cart::add_cart($request);
    }

    public function product_request_qoute()
    {
        $data["Page"]       = "Request a Qoute";
        if(Request::isMethod('post'))
        {
            $input = Request::all();
            if(isset($input['name']) || isset($input['email']) || isset($input['phone']) || isset($input['message']))
            {
                $insert['qoute_request_name']     = $input['name'];
                $insert['qoute_request_email']    = $input['email'];
                $insert['qoute_request_phone']    = $input['phone'];
                $insert['qoute_request_message']  = $input['message'];
                $insert['qoute_request_item_id']  = $input['item_id'];
                DB::table('tbl_qoute_request')->insert($insert);

                $view  = "SUCCESS";
            }
            else
            {
                $view  = "ERROR";
            }
        }
        else
        {
            $view =  view ("ecommerce.popups.request-qoute", $data);
        }
        return $view;
    }

    public function product_request_qoute_success()
    {
        $data["Page"] = "Request a Qoute Success";
        return view ("ecommerce.popups.request-qoute-success", $data);
    }
    //END POPUPS
}
