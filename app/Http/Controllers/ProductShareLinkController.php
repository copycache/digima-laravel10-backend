<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Request;
use App\Models\Tbl_item;
use App\Models\Tbl_slot;
use App\Models\Tbl_mlm_plan;

use Crypt;

class ProductShareLinkController extends Controller
{
    public function check_referral()
    {
        $slot_no                                                = Request::input('slot_no');
        $product_id                                             = Crypt::decryptString(Request::input('product_id'));;
        $response['sponsor_details']                            = Tbl_slot::where('slot_no',$slot_no)->where('membership_inactive',0)->leftjoin('users','users.id','tbl_slot.slot_owner')->first() ?? 'none';
        $response['product_info']                               = Tbl_item::where('product_id',$product_id)->JoinInventory()->first() ?? 'none';
        $response['product_info']['quantity']                   = 1;
        if($response['sponsor_details']  == 'none' || $response['product_info'] == 'none')
        {
            $response['is_valid']                               = "invalid";
        }
        else
        {
            $get_plan_status		 								= Tbl_mlm_plan::where('mlm_plan_code','PRODUCT_DOWNLINE_DISCOUNT')->first()->mlm_plan_enable;
            $get_item_discount 										= null;
            $response['product_info']['discounted_price']           = $response['product_info']->item_price;

            if($get_plan_status == 1 && $response['sponsor_details'])
            {
                if($response['sponsor_details'])
                {
                    $get_item_discount	    						= Tbl_slot::where('slot_no',$slot_no)
                                                                    ->leftjoin('tbl_membership','tbl_membership.membership_id','tbl_slot.slot_membership')
                                                                    ->leftjoin('tbl_product_downline_discount','tbl_product_downline_discount.membership_id','tbl_slot.slot_membership')
                                                                    ->select('tbl_product_downline_discount.membership_id','item_id','discount','type')->get();
                }
            
                if($get_item_discount)
                {
                    foreach ($get_item_discount as $key1 => $discount) 
                    {
                        if($discount->type == 'percentage')
                        {
                            $response['product_info']['discounted_price']  				= abs(($discount->discount / 100) * $response['product_info']->item_price - $response['product_info']->item_price);
                        }
                        else
                        {
                            $response['product_info']['discounted_price']  				= $response['product_info']->item_price - $discount->discount;
                        }
                    }
                }
            }	

            $response['is_valid']                               = "valid";
        }
        return $response;

    }
    public function check_store_link()
    { 
        $slot_no                                                = Request::input('slot_no');
       
        $response['is_valid']                                   = 'invalid';

        if(isset($slot_no))
        {
            $get_sponsor_details                            = Tbl_slot::where('slot_no',$slot_no)->first() ?? null;
            
            if($get_sponsor_details)
            {
                if($get_sponsor_details->slot_membership) {
                    $response['is_valid']                       = 'valid';
                    $response['store_name']                     = Crypt::encryptString($get_sponsor_details->slot_no);
                    $response['slot_no']                        = Crypt::encryptString($get_sponsor_details->slot_no);
                    $response['slot_id']                        = $get_sponsor_details->slot_id;    
                } else {
                    $response['is_valid']                       = 'invalid';
                }
               
                if($get_sponsor_details->membership_inactive == 0)
                {
                    $response['store_name']                 = $get_sponsor_details->store_name;
                }
                else
                {
                    $response['store_name']                 = Tbl_slot::where('slot_id',$get_sponsor_details->slot_sponsor)->pluck('store_name')->first() ?? null;
                }
            }   
        }
        
        return $response;
    }
}
