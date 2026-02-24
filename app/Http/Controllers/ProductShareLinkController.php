<?php

namespace App\Http\Controllers;

use App\Models\Tbl_item;
use App\Models\Tbl_mlm_plan;
use App\Models\Tbl_slot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class ProductShareLinkController extends Controller
{
    public function check_referral(Request $request)
    {
        $slot_no = $request->input('slot_no');
        $product_id = Crypt::decryptString($request->input('product_id'));
        
        $sponsor_details = Tbl_slot::where(['slot_no' => $slot_no, 'membership_inactive' => 0])
            ->leftJoin('users', 'users.id', 'tbl_slot.slot_owner')
            ->first() ?? 'none';
        
        $product_info = Tbl_item::where('product_id', $product_id)->JoinInventory()->first() ?? 'none';
        
        if ($sponsor_details == 'none' || $product_info == 'none') {
            return ['is_valid' => 'invalid', 'sponsor_details' => $sponsor_details, 'product_info' => $product_info];
        }

        $product_info['quantity'] = 1;
        $product_info['discounted_price'] = $product_info->item_price;

        $plan_enabled = Tbl_mlm_plan::where('mlm_plan_code', 'PRODUCT_DOWNLINE_DISCOUNT')->value('mlm_plan_enable');
        
        if ($plan_enabled && $sponsor_details) {
            $discounts = Tbl_slot::where('slot_no', $slot_no)
                ->leftJoin('tbl_membership', 'tbl_membership.membership_id', 'tbl_slot.slot_membership')
                ->leftJoin('tbl_product_downline_discount', 'tbl_product_downline_discount.membership_id', 'tbl_slot.slot_membership')
                ->select('tbl_product_downline_discount.membership_id', 'item_id', 'discount', 'type')
                ->get();

            foreach ($discounts as $discount) {
                $product_info['discounted_price'] = $discount->type == 'percentage'
                    ? abs(($discount->discount / 100) * $product_info->item_price - $product_info->item_price)
                    : $product_info->item_price - $discount->discount;
            }
        }

        return [
            'is_valid' => 'valid',
            'sponsor_details' => $sponsor_details,
            'product_info' => $product_info
        ];
    }
    public function check_store_link(Request $request)
    {
        $slot_no = $request->input('slot_no');
        $response = ['is_valid' => 'invalid'];

        if (!$slot_no) {
            return $response;
        }

        $sponsor = Tbl_slot::where('slot_no', $slot_no)->first();
        
        if (!$sponsor) {
            return $response;
        }

        if ($sponsor->slot_membership) {
            $response['is_valid'] = 'valid';
            $response['store_name'] = Crypt::encryptString($sponsor->slot_no);
            $response['slot_no'] = Crypt::encryptString($sponsor->slot_no);
            $response['slot_id'] = $sponsor->slot_id;
        }

        $response['store_name'] = $sponsor->membership_inactive == 0
            ? $sponsor->store_name
            : Tbl_slot::where('slot_id', $sponsor->slot_sponsor)->value('store_name');

        return $response;
    }
}
