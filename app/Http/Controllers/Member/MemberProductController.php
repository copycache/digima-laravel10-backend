<?php
namespace App\Http\Controllers\Member;

use App\Globals\Code;
use App\Globals\Item;
use App\Globals\Cashier;
use App\Globals\Branch;
use App\Globals\Product;
use App\Globals\Cart;
use Illuminate\Support\Facades\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

use App\Models\Tbl_slot;
use App\Models\Tbl_tree_sponsor;
use App\Models\Tbl_unilevel_points;
use App\Models\Tbl_item;
use App\Models\Tbl_dragonpay_settings;
use App\Models\Tbl_cashier_payment_method;
use App\Models\Tbl_wallet;
use App\Models\Tbl_signup_bonus_logs;
use App\Models\Tbl_orders_for_approval;
use App\Models\Tbl_currency;
use App\Models\Tbl_product_category;
use App\Models\Tbl_product_subcategory;
use App\Models\Tbl_branch;
use App\Models\Tbl_address;
use App\Models\Cart as CartModel;
use Crypt;


class MemberProductController extends MemberController
{
    public function get_front_cart()
    {
        $response = Cart::get_cart(Request::input('cart_key'));
        foreach ($response as $key => $value) 
        {
            $response[$key]['item_qty'] = $value['cart_item_quantity'];
        }
        Cart::delete_items(Request::input('cart_key'));
        return response()->json($response);
    }
    public function get_all_products()
    {
    	$response = Item::get_all_products(Request::input('slot_id'),Request::input());
    	return response()->json($response);
    }

    public function get_product()
    {
    	$response = Item::get_data(Request::input('item_id'), Request::input('slot_id'));
    	return response()->json($response);
    }

    public function get_cart_items()
    {
    	$response = Item::get_cart(Request::input(),Request::input('branch_id'));

        return response()->json($response);
    }

    public function checkout()
    {
        $response = Cashier::ecom_checkout(Request::input());

        return response()->json($response);
    }

    public function get_branch()
    {
        $response = Branch::get();

        return response()->json($response);
    }
    public function activate_product_code()
    {
        $data["pin"]            = Request::input("pin");
        $data["code"]           = Request::input("code");
        $data["slot_id"]        = Request::input("slot_id");
        $data["slot_owner"]     = Request::user()->id;
        
        $response = Product::activate_code($data);

        return response()->json($response);
    }

    public function get_location()
    {
        $response = DB::table('tbl_branch')->where('archived', '=', 0)->get();
        return response()->json($response);
    }
    public function get_delivery_charge()
    {
        $response['Direct'] = DB::table('tbl_delivery_charge')->where('method_name',"Direct")->first();
        $response['Indirect'] = DB::table('tbl_delivery_charge')->where('method_name',"Indirect")->first();
        return response()->json($response);
    }
    public function rate_item()
    {
        $item_rate      = Request::input('item_rate');
        $item_id        = Request::input('item_id');
        $user_id        = Request::user()->id;
        $item_review    = Request::input('item_review');
        $order_number   = Request::input('order_number');       
        $response       = Item::rate_item($item_rate,$item_id,$user_id,$item_review,$order_number);

        return Response()->json($response);
    }
    public function get_level_item()
    {
        $level = Request::input("level");
        $slot  = Tbl_slot::where("slot_owner",Request::user()->id)->where("slot_id",Request::input("current_slot_id"))->first();
        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();
        $query = Tbl_unilevel_points::whereDate("unilevel_points_date_created", ">=", $start)
            ->whereDate("unilevel_points_date_created", "<=", $end)
            ->where("unilevel_points_slot_id", $slot->slot_id)
            ->where("unilevel_points_cause_level", $level == -1 ? 0 : $level)
            ->where('unilevel_points_distribute', 0);

        // Add the specific condition for `unilevel_points_type` based on `$level`
        $type = $level > -1 ? 'UNILEVEL_GPV' : 'UNILEVEL_PPV';
        $query->where('unilevel_points_type', $type);

        // Paginate the results
        $return["items"] = $query->paginate(5);
        foreach ($return["items"] as $key => $value) 
        {
            $return["items"][$key]->buyer_name = Tbl_slot::where("slot_id",$value->unilevel_points_cause_id)->first()->slot_no;
            $points = Tbl_item::where("item_id",$value->unilevel_item_id)->first();
            if($points->item_pv != 0)
            {
                $return["items"][$key]->sum_points =  $value->unilevel_points_amount;
            }
            else 
            {
                $return["items"][$key]->sum_points   =  0;
            }
            
            $return["items"][$key]->item_desc  =  $points;
        }
        $return["level"] = $level;
        $return["total_points"] = $query->sum('unilevel_points_amount');

        return Response()->json($return);
    }

    public function search_product()
    {
        $search    = Request::input('search');
        $return = Tbl_item::where('archived', 0)
        ->where(function ($query) {
            $query->where('item_availability', 'ecommerce')
                ->orWhere('item_availability', 'all');
        });

        if($search)
        {
            $return = $return->where("item_sku", "like", "%". $search . "%");
        }

        $return = $return->get();
        return response()->json($return);
    }

    public function get_product_link()
    {
        $item_id                            = Request::input('item_id');
        $user_id                            = Request::user()->id;
        $slot_no                            = Tbl_slot::where('slot_owner',$user_id)->first()->slot_no;
        $return['item_info']                = Tbl_item::where('item_id',$item_id)->first();
        $encrypt_id = Crypt::encryptString($return['item_info']->product_id);
        $return['product_link']             = "/member/product/link/".$slot_no."/".$encrypt_id;

        return $return;
    }
    public function get_payment_method()
    {
        return Tbl_cashier_payment_method::where('cashier_payment_method_name','Wallet')->orWhere('cashier_payment_method_name','Dragonpay')->get();
    }
    public function dragonpay_ServiceCharged()
    {
        return Tbl_dragonpay_settings::first()->service_charged ?? 0;
    }
    public function get_voucher()
    {
        $slot_info                            = Request::input('slot_info');

        $voucher                              = Tbl_wallet::where('slot_id',$slot_info['slot_id'])->where('currency_id',13)->first()->wallet_amount ?? 0;

        $voucher_settings                     = Tbl_signup_bonus_logs::where('tbl_signup_bonus_logs.slot_id',$slot_info['slot_id'])
                                              ->leftjoin('tbl_membership','tbl_membership.membership_id','tbl_signup_bonus_logs.membership_id')
                                              ->first();

        if($voucher_settings)
        {
            if($voucher >= $voucher_settings->sign_up_voucher_use)
            {
                $return['voucher_deduct']        = (int)$voucher_settings->sign_up_voucher_use;
            }
            else
            {
                $return['voucher_deduct']        = (int)$voucher;
            }
            $return['min_spend']                 = (int)$voucher_settings->sign_up_minimum;
            $return['voucher_status']            = 1;
        }
        else
        {
            if($voucher > 0)
            {
                $voucher_settings               = Tbl_slot::where('slot_id',$slot_info['slot_id'])->leftjoin('tbl_membership','tbl_membership.membership_id','tbl_slot.slot_membership')->first();
                
                if($voucher_settings)
                {
                    if($voucher >= $voucher_settings->sign_up_voucher_use)
                    {
                        $return['voucher_deduct']        = (int)$voucher_settings->sign_up_voucher_use;
                    }
                    else
                    {
                        $return['voucher_deduct']        = (int)$voucher;
                    }
                    $return['min_spend']                 = (int)$voucher_settings->sign_up_minimum;
                    $return['voucher_status']            = 1;
                }
                else
                {
                    $return['voucher_status']            = 0;
                }

            }
            else
            {
                $return['voucher_status']                = 0;
            }

        }
       
        return $return;
    }
    public function record_item()
    {
        # code...json_encode($items);

        $data                                                  = Request::input();

        // dd($data);
        foreach($data['items'] as $key => $value)
        {
            $items[$key]['item_id'] 						   = $value['item_id'];
            $items[$key]['quantity'] 						   = $value['item_qty'];
            $items[$key]['discounted_price']  				   = $value['discounted_price'];
            $items[$key]['shipping_fee']  	    			   = $data['courier'] =='lalamove'? $value['shipping_fee_lalamove'] : $value['shipping_fee_ninja'];
            $items[$key]['total_per_item']  	    		   = $value['total_per_item'];  
        }

        $insert['slot_id']                                     = $data['slot']['slot_id'];         
        $insert['transaction_number']                          = "TWCS".time();         
        $insert['user_id']                                     = Request::user()->id;         
        $insert['address']                                     = $data['address'] ?? null;         
        $insert['branch_id']                                   = $data['branch_id'];         
        $insert['delivery_method']                             = $data['method'];         
        $insert['courier']                                     = $data['courier'];     
        $insert['default_min_spend']                           = $data['default_min_spend'] ?? 0;                 
        $insert['default_voucher_deduct']                      = $data['default_voucher_deduct'] ?? 0;                     
        $insert['default_voucher_status']                      = $data['default_voucher_status'];                     
        $insert['dragonpay_charged']                           = $data['dragonpay_charged'];                 
        $insert['email_address']                               = $data['email_address'];             
        $insert['grandtotal']                                  = $data['grandtotal'];         
        $insert['item_charged']                                = $data['item_charged'];         
        $insert['items']                                       = json_encode($items);     
        $insert['method_charge']                               = $data['method_charge'];             
        $insert['min_spend']                                   = $data['min_spend'] ?? 0;         
        $insert['overall_cashback']                            = $data['overall_cashback'];             
        $insert['payment_method']                              = $data['payment_method'];             
        $insert['shipping_fee']                                = $data['shipping_fee'];         
        $insert['sum']                                         = $data['sum']; 
        $insert['total_item_price']                            = $data['total_item_price'];             
        $insert['voucher_deduct']                              = $data['voucher_deduct'] ?? 0;             
        // $insert['item_fee']                                    = $data['item_fee'];     
        $insert['date_created']                                = Carbon::now();         
        $insert['admin_status']                                = 'pending';     
        
        Tbl_orders_for_approval::insert($insert);
    }
    public function check_pending_transaction()
    {
        $response['data']                                             = Tbl_orders_for_approval::where('user_id',Request::user()->id)->where('user_status',null)->where('date_purchased',null)->where('shop_status',0)
                                                                      ->leftJoin('tbl_branch','tbl_branch.branch_id','tbl_orders_for_approval.branch_id')
                                                                      ->first() ?? null;
        if($response['data'] != null)
        {
            // dd($response['data']['slot_id']);
            $currency_id                                              = Tbl_currency::where('currency_buying', 1)->pluck('currency_id')->first();
            $response['data']['available_wallet']                     = Tbl_wallet::where('slot_id',$response['data']['slot_id'])->where('currency_id',$currency_id)->first()->wallet_amount ?? 0;
            
            foreach (json_decode($response['data']['items']) as $key => $value) 
            {
                $response['item_details'][$key]                        = Tbl_item::where('item_id',$value->item_id)->first();   
                $response['item_details'][$key]['quantity']            = $value->quantity; 
                $response['item_details'][$key]['discounted_price']    = $value->discounted_price;         
                $response['item_details'][$key]['shipping_fee']        = $value->shipping_fee;     
                $response['item_details'][$key]['total_per_item']      = ($value->discounted_price * $value->quantity) + $value->shipping_fee;  

            }
        }
        $response['status']                                           = isset($response['data']) ? 1 : 0;
        return $response;
    }
    public function place_order()
    {
        $data                                                           = Request::input('item_list');
        dd($data);
    }
    public function check_wallet()
    {
        $data                                                           = Request::input();
        $currency_id                                                    = Tbl_currency::where('currency_buying', 1)->pluck('currency_id')->first();
        $wallet                                                         = Tbl_wallet::where('slot_id',$data['info']['slot_id'])->where('currency_id',$currency_id)->first()->wallet_amount ?? 0;
       
        return $wallet;
    }
    public function checkout_v2()
    {
        $response = Cashier::ecom_checkout_v2(Request::input());

        return response()->json($response);
    }
    public function cancel_order()
    {
        $update['user_status']                                          = 'order_cancelled';
        $update['date_purchased']                                       = Carbon::now();   
        Tbl_orders_for_approval::where('id',Request::input('id'))->update($update);

        return "";
    }
    public function continue_to_shop()
    {
        $update['shop_status']                                          = 1;   
        Tbl_orders_for_approval::where('id',Request::input('id'))->update($update);

        return "";
    }
    public static function get_category_list()
	{
		$response 						= Tbl_product_category::where('archive',0)->get();
		
		return $response;
	}
	public static function get_subcategory_list()
	{
        $category_id = Request::input('category_id');

        if($category_id != 'all')
        {
            $response 	= Tbl_product_subcategory::where('category_id',$category_id)->where('archive',0)->get();
        }

		return $response;
	}
    public static function getbranch_ecom()
    { 
        $branch         = Tbl_branch::where('archived', 0)->leftJoin('tbl_stockist_level', 'tbl_branch.stockist_level', '=', 'tbl_stockist_level.stockist_level_id')->get();

        return $branch;
    }
    public static function get_first_category()
	{
		$response 						= Tbl_product_category::where('archive',0)->pluck('id')->first();
		return $response;
	}

    /**
     * Simplified checkout - accepts minimal params and builds the full ecom_checkout payload.
     * Expects: { payment_method: "Wallet"|"GC_Wallet", address_id, slot_id, slot_owner }
     */
    public function simpleCheckout()
    {
        try {
            $payment_method = Request::input('payment_method', 'Wallet');
            $address_id     = Request::input('address_id');
            $slot_id        = Request::input('slot_id');
            $slot_owner     = Request::input('slot_owner');

            if (!$slot_id || !$slot_owner) {
                return response()->json(['status' => 'error', 'status_message' => 'slot_id and slot_owner are required.']);
            }

            // Get cart items for this slot_owner
            $cartItems = CartModel::where('slot_owner', $slot_owner)->get();
            if ($cartItems->isEmpty()) {
                return response()->json(['status' => 'error', 'status_message' => 'Cart is empty.']);
            }

            // Build items array for ecom_checkout
            $items = [];
            $grandtotal = 0;
            foreach ($cartItems as $cartItem) {
                $price = ($payment_method == 'GC_Wallet') 
                    ? ($cartItem->item_gc_price ?? $cartItem->item_price ?? 0)
                    : ($cartItem->discounted_price > 0 ? $cartItem->discounted_price : ($cartItem->item_price ?? 0));
                
                $items[] = [
                    'item_id'          => $cartItem->item_id,
                    'item_qty'         => $cartItem->item_qty ?? 1,
                    'discounted_price' => $price,
                    'item_gc_price'    => $cartItem->item_gc_price ?? 0,
                    'item_sku'         => $cartItem->item_sku ?? '',
                ];
                $grandtotal += $price * ($cartItem->item_qty ?? 1);
            }

            // Get address details for receiver info
            $receiver_name    = '';
            $receiver_email   = '';
            $receiver_contact = '';
            
            if ($address_id) {
                $address = Tbl_address::where('address_id', $address_id)->first();
                if ($address) {
                    $receiver_name    = $address->receiver_name ?? '';
                    $receiver_email   = $address->receiver_email ?? '';
                    $receiver_contact = $address->receiver_contact_number ?? '';
                }
            }

            // Fallback to user info if address receiver info is empty
            $user = Request::user();
            if (empty($receiver_name))    $receiver_name    = ($user->first_name ?? '') . ' ' . ($user->last_name ?? '');
            if (empty($receiver_email))   $receiver_email   = $user->email ?? '';
            if (empty($receiver_contact)) $receiver_contact = $user->contact_no ?? '';

            // Get default branch
            $branch_id = Tbl_branch::where('archived', 0)->pluck('branch_id')->first() ?? 1;

            // Build full checkout payload
            $data = [
                'method'                   => 'Indirect',   // delivery
                'method_charge'            => 0,
                'branch_id'                => $branch_id,
                'address'                  => $address_id,
                'slot'                     => [
                    'slot_id'    => $slot_id,
                    'slot_owner' => $slot_owner,
                ],
                'items'                    => $items,
                'payment_method'           => $payment_method,
                'grandtotal'               => $grandtotal,
                'shipping_fee'             => 0,
                'handling_fee'             => 0,
                'receiver_name'            => $receiver_name,
                'receiver_email'           => $receiver_email,
                'receiver_contact_number'  => $receiver_contact,
            ];

            $response = Cashier::ecom_checkout($data);

            // Clear cart on success
            if (isset($response['status']) && strtolower($response['status']) == 'success') {
                CartModel::where('slot_owner', $slot_owner)->delete();
            }

            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'status_message' => $e->getMessage()]);
        }
    }
}
