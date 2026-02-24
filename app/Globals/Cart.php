<?php
namespace App\Globals;

use DB;
use Session;
use Carbon\Carbon;
use App\Models\Tbl_cart;

class Cart
{

    public static function get_unique_id()
    {
        if(Session::get('cart_key'))
        {
            $return = Session::get('cart_key');
        }
        else
        {
            $generated = substr(str_shuffle("1234567890"), 0,8);
            Session::put('cart_key',$generated);
            $return = Session::get('cart_key');
        }
        return $return;
    }
    public static function add_cart($data)
    {
        $add['cart_key']            = Self::get_unique_id();
        $add['cart_item_id']        = $data['item_id'];
        $add['cart_item_quantity']  = $data['quantity'];
        $check = Tbl_cart::where('cart_key',$add['cart_key'])->where('cart_item_id',$add['cart_item_id'])->where('cart_status',0);
        if($check->count()==0)
        {
            $add['cart_created']        = Carbon::now();
            Tbl_cart::insert($add);
        }
        else
        {
            $add['cart_updated']        = Carbon::now();
            $check->update($add);
        }
    }

    public static function get_cart($cart_key = null)
    {
        if($cart_key == null)
        {
            $cart_key = Self::get_unique_id();
        }
        return Tbl_cart::where('cart_key',$cart_key)->where('cart_status',0)->join('tbl_item','tbl_item.item_id','=','tbl_cart.cart_item_id')->get();
    }
    public static function remove_item($item_id)
    {
        $cart_key                   = Self::get_unique_id();
        $update['cart_status']      = 1;
        Tbl_cart::where('cart_key',$cart_key)->where('cart_item_id',$item_id)->where('cart_status',0)->update($update);
    }

    public static function delete_items($cart_key)
    {
        Tbl_cart::where('cart_key',$cart_key)->delete();
    }
}
