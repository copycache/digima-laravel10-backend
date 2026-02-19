<?php
namespace App\Http\Controllers\Admin;

use App\Models\Tbl_dropshipping_list;
use App\Models\Tbl_orders;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Globals\Audit_trail;
use App\Models\Tbl_item;
use App\Models\Tbl_delivery_charge;
use App\Models\Tbl_currency;
use App\Globals\Cashier;
use App\Globals\Log;
use App\Models\Tbl_cashier;
use App\Models\Tbl_receipt;
use App\Models\Tbl_cashier_payment_method;
use App\Models\Tbl_dragonpay_transaction;
use App\Models\Tbl_orders_for_approval;
use App\Models\Users;
use App\Models\Tbl_cod_list;

use App\Globals\MLM;
use App\Models\Tbl_wallet_log;

class AdminOrderController extends AdminController
{
    public function get() 
    {
        $data = Request::input();
        $query = DB::table('tbl_orders');
        if(isset($data['from']))
        {
            $query = $query->whereDate('order_date_created', '>=', $data['from']);
        }

        if(isset($data['to']))
        {
            $query = $query->whereDate('order_date_created', '<=', $data['to']);
        }

        if (isset($data["search"]) && $data["search"])
        {
            $array = $query->where("buyer_name", 'LIKE', '%' . $data["search"] . '%')
            ->orWhere('buyer_slot_code','LIKE','%' . $data["search"] . '%');;
        }
        if(!isset($data['status']) || $data['status'] == 'all')
        {
            $array = $query->orderBy('tbl_orders.order_date_created','DESC')->paginate(15);
        }
        else
        {
            $array = $query->where('order_status', $data['status'])->orderBy('tbl_orders.order_date_created','DESC')->paginate(15);
        }

        if(!isset($data['payment']) || $data['payment'] == 'All')
        {
            $array = $query->orderBy('tbl_orders.order_date_created','DESC')->paginate(15);
        }
        else
        {
            $array = $query->where("payment_method", $data['payment'])->orderBy('tbl_orders.order_date_created','DESC')->paginate(15);
        }

        foreach($query->get() as $order) {
            if($order->order_from == "cashier" && $order->order_status == "completed") {
                self::change_order_status_for_cashier($order->order_id);
            }
        }
       
        foreach($array as $key => $value)
        {
            
            $array[$key]->payment_method = Tbl_cashier_payment_method::where('cashier_payment_method_id',$value->payment_method)->pluck('cashier_payment_method_name')->first();
            $array[$key]->items = DB::table('tbl_orders_rel_item')->where('rel_order_id', $value->order_id)->join('tbl_item', 'tbl_item.item_id', '=', 'tbl_orders_rel_item.item_id')->get();
            $array[$key]->receipt = DB::table('tbl_receipt')->where('receipt_order_id', $value->order_id)->first();
           
            if($value->buyer_slot_id) {
                $array[$key]->user_info = DB::table('tbl_slot')->where('slot_id', $value->buyer_slot_id)->join('users','users.id','tbl_slot.slot_owner')->first();
                $array[$key]->sponsor_info = DB::table('tbl_slot')->where('slot_id',$array[$key]->user_info->slot_sponsor)->join('users','users.id','tbl_slot.slot_owner')->first();
            } else {
                $array[$key]->sponsor_info = DB::table('tbl_slot')->where('slot_id', $value->buyer_sponsor_id)->join('users','users.id','tbl_slot.slot_owner')->first();
            }
        }
       
    
        return response()->json($array, 200);
    }
    
    public function select() 
    {
        $order_id = Request::input('order_id');
        $order = DB::table('tbl_orders')->where('order_id', $order_id)->first();
        $cashier = DB::table('tbl_cashier')->join('users', 'tbl_cashier.cashier_user_id', '=', 'users.id')->where('tbl_cashier.cashier_id', $order->cashier_id)->first();
        $customer = DB::table('tbl_slot')->join('users', 'tbl_slot.slot_owner', '=', 'users.id')->where('tbl_slot.slot_id', $order->buyer_slot_id)->first();
        if($order)
        {
            $ordered_item = json_decode($order->items);
            foreach($ordered_item as $key => $value)
            {
                $item[$key] = Tbl_item::where('item_id', $value->item_id)->first();
                $item[$key]->quantity = $value->quantity;
                $item[$key]->order_id = $order->order_id;
            }
        }
        $response['item'] = $item;
        $response['order'] = $order;
        $response['cashier'] = $cashier;
        $response['customer'] = $customer;
        return response()->json($response, 200);
    }

    public function status()
    {
        $order_id = Request::input('order_id');
        $status = Request::input('status');
        if($status == 'delivered')
        {
            $old_value =  DB::table('tbl_orders')->where('order_id', $order_id)->first();
            $update['order_date_delivered'] = Carbon::now();
            $update['order_status']         = $status;
            $update['date_status_changed'] = Carbon::now();
            DB::table('tbl_orders')->where('order_id', $order_id)->update($update);
            $new_value = DB::table('tbl_orders')->where('order_id', $order_id)->first();
        }
        else if($status == 'completed')
        {
            $check_if_exist = Tbl_cod_list::where('order_id', $order_id)->where('status',0)->first();
            $check_if_exist_dropshipping = Tbl_dropshipping_list::where('order_id', $order_id)->where('status',0)->first();
            if($check_if_exist)
            {
                $item_for_pv = json_decode($check_if_exist->ordered_item);
                foreach($item_for_pv as $key => $value)
                {
                    for($x = 0; $x < $value->quantity; $x++)
                    {
                        MLM::purchase($check_if_exist->slot_id,$value->item_id);
                    }
                }

                MLM::purchase_item($check_if_exist->ordered_item,$check_if_exist->slot_id,$check_if_exist->subtotal);

                $update_cod['status']         = 1;
                $update_cod['date_completed'] = Carbon::now();

                Tbl_cod_list::where('order_id', $order_id)->update($update_cod);
            } 
            if($check_if_exist_dropshipping) {
                $order = Tbl_orders::where('order_id', $check_if_exist_dropshipping->order_id)->first();
             
                MLM::dropshipping_purchase_item($check_if_exist_dropshipping->ordered_item,$order->buyer_sponsor_id,$check_if_exist_dropshipping->subtotal,$order_id);

                $update_cod['status']         = 1;
                $update_cod['date_completed'] = Carbon::now();

                Tbl_dropshipping_list::where('order_id', $order_id)->update($update_cod);
            }

            $old_value =  DB::table('tbl_orders')->where('order_id', $order_id)->first();
            $update['order_status']         = $status;
            $update['order_date_completed'] = Carbon::now();
            $update['date_status_changed'] = Carbon::now();
            DB::table('tbl_orders')->where('order_id', $order_id)->update($update);
            $new_value = DB::table('tbl_orders')->where('order_id', $order_id)->first();
        } else if ($status == 'cancelled') {
            $old_value =  DB::table('tbl_orders')->where('order_id', $order_id)->first();
            $update['order_status'] = $status;
            $update['date_status_changed'] = Carbon::now();
            DB::table('tbl_orders')->where('order_id', $order_id)->update($update);
            $new_value = DB::table('tbl_orders')->where('order_id', $order_id)->first();
        } else if ($status == 'refunded') {
            $old_value = DB::table('tbl_orders')->where('order_id', $order_id)->first();
            $update['order_status'] = $status;
            $update['date_status_changed'] = Carbon::now();
            DB::table('tbl_orders')->where('order_id', $order_id)->update($update);
            $new_value = DB::table('tbl_orders')->where('order_id', $order_id)->first();
            if($old_value) {
                $response = Self::refund_wallet_amount($old_value);
                if($response["status"] == 'error') {
                    return $response;
                }
            }
        } else {
            $old_value =  DB::table('tbl_orders')->where('order_id', $order_id)->first();
            $update['order_status'] = $status;
            $update['date_status_changed'] = Carbon::now();
            DB::table('tbl_orders')->where('order_id', $order_id)->update($update);
            $new_value = DB::table('tbl_orders')->where('order_id', $order_id)->first();
        }
        $action = $status;
        $user = Request::user()->id;
        $response["status"] = "success";
        Audit_trail::audit(serialize($old_value),serialize($new_value),$user,$action);    
        return $response; 
    }

    public function charge_table()
    {
        $response = DB::table('tbl_delivery_charge')->get();
        //dd($data);
        return $response;
    }

    public function currency_default()
    {
        $response = DB::table('tbl_currency')->where('currency_default', 1)->get();
        return $response;
    }
    public function edit_delivery_charge()
    {
        $user          = Request::user()->id;
        $method_id     = Request::input('method_id');
        $method_charge = Request::input('method_charge');
        $method_discount = Request::input('method_discount');
        if($method_id)
        {
            $action    = "Edit Delivery Charge";
            //audit_trail
            $old_value =   DB::table('tbl_delivery_charge')->where("method_id", $method_id)->first();
            //
            DB::table('tbl_delivery_charge')
            ->where("method_id", $method_id)
            ->update([
                'method_charge' => $method_charge,
                'method_discount' => $method_discount
            ]);
            //audit_trail
            $new_value =   DB::table('tbl_delivery_charge')->where("method_id", $method_id)->first();
            //
            Audit_trail::audit(serialize($old_value),serialize($new_value),$user,$action);
            $return["status_message"] = "Method Succesfully Updated!";
            $return["status"] = "success";
        }
        else
        {
            $return["status_message"] = "Oops! Something went wrong!";
            $return["status"] = "error";
        }

        return $return;

    }
    public function save_orders_method()
    {
        $user          = Request::user()->id;
        $data = Request::input();
        $action    = "Save Order Method";
        //audit_trail
        $old_value =   DB::table('tbl_delivery_charge')->get();
        //
        foreach ($data as $key => $value) 
        {
            DB::table('tbl_delivery_charge')->where("method_id", $value["method_id"])->update(["enable" => $value["enable"]]);
        }
        //audit_trail
        $new_value =   DB::table('tbl_delivery_charge')->get();
        //
        Audit_trail::audit(serialize($old_value),serialize($new_value),$user,$action);
        $return["status_message"] = "Method Succesfully Updated!";
        $return["status"] = "success";

        return $return;
    }
    public function select_claim_code()
    {
        $receipt_id = Request::input('receipt_id');
        $response   = Self::select_claim_codes($receipt_id);
        return response()->json($response);
    } 
    public function update_claim_code()
    {
        $receipt_id = Request::input('receipt_id');
        $claim_code = Request::input('claim_code');
        $processor  = Request::input('processor_name');
        $status     = Request::input('status');
        $response   = Self::select_claim_codes($receipt_id, $claim_code, $processor, $status);
        return response()->json($response);
    }

    public static function get_claim_code_list()
	{
        $data           = Request::input();
		$claim_codes 	= Tbl_receipt::where('retailer', 1)->where('claim_code', '!=', 'none');

		if($data)
		{
			$claim_codes = $claim_codes->where("claim_code", "like", "%". $data['claim_code_search'] . "%")->get();
		}
		else
		{
			$claim_codes->get();
		}

		return $claim_codes;
	}

	public static function select_claim_codes($receipt_id, $claim_code = null, $processor = null, $status = null)
	{
		if(isset($claim_code))
		{
			$check_receipt 	= Tbl_receipt::where('receipt_id', $receipt_id)->first();

			if($check_receipt)
			{
                // if($status == 'claim')
                // {
                $order = DB::table('tbl_orders')->where('order_id', $check_receipt->receipt_order_id)->first();

                if($order->order_status == "completed" || $status == 'unclaim') {
                    $update['claimed']              = $status == 'claim' ? 1 : 0;
                    $update['processor_name']       = $status == 'claim' ? $processor : null;
                    $update['unclaimed_status']      = $status == 'claim' ? : 1;
    
                    Tbl_receipt::where('receipt_id', $receipt_id)->update($update);
    
                    $update2['order_status']        = $status == 'claim' ? "claimed" : "completed";
                    $update2['date_status_changed'] = $status == 'claim' ? Carbon::now() : null;
                    DB::table('tbl_orders')->where('order_id', $check_receipt->receipt_order_id)->update($update2);
    
                    $return["status"]               = "success";
                    $return["status_code"]          = 200;
                    $return["status_message"]       = $status == 'claim' ? "Used Successfully!" : "Status set to Unclaimed";
                } else {
                    $return["status"]         = "error";
                    $return["status_code"]    = 400;
                    $return["status_message"] = "The order status of this code is not yet completed.";
                }
                // }
                // else
                // {
                //     $update['claimed']              = 1;
                //     $update['processor_name']       = $processor;
    
                //     Tbl_receipt::where('receipt_id', $receipt_id)->update($update);
    
                //     $update2['order_status']        = "claimed";
                //     $update2['date_status_changed'] = Carbon::now();
                //     DB::table('tbl_orders')->where('order_id', $check_receipt->receipt_order_id)->update($update2);
    
                //     $return["status"]               = "success";
                //     $return["status_code"]          = 200;
                //     $return["status_message"]       = "Used Successfully!";
                // }
			}
			else
			{
				$return["status"]         = "error";
				$return["status_code"]    = 400;
				$return["status_message"] = "Claim code either used or invalid!";
			}
		}
		else
		{
			$return 	= Tbl_receipt::where('receipt_id', $receipt_id)->first();

			$items 		= json_decode($return->items);

			foreach ($items as $key => $value)
			{
				$item[$key]    	= Tbl_item::where('item_id', $value->item_id)->select('item_sku')->first();
				$item[$key]->quantity	= $value->quantity;
			}


			$return['items'] 	= $item;
		}

		return $return;
	}
    public static function updateOrderInfo()
    {                                       
        $info                                               = Request::input('info');               
        $order_id                                         = Request::input('order_id');     
        if($info == 'courier')
        {
           $update['courier']                               = Request::input('courier'); 

            $return["status"]                               = "success";
            $return["status_code"]                          = 200;
            $return["status_message"]                       = "Courier is Updated Successfully";               
        }
        else
        {
            $update['transaction_number']                   = Request::input('transaction_number');

            $return["status"]                               = "success";
            $return["status_code"]                          = 200;
            $return["status_message"]                       = "Tracking Number is Updated Successfully";                                 
        }

        Tbl_receipt::where('receipt_order_id',$order_id)->update($update);

        return $return;
    }
    public static function get_dragonpay_orders($filters = null, $export = 0)
    {
        if($export == 0)
        {
            $filters                                    = Request::input();
        }
        else
        {
            $filters                                    = $filters;
            if($filters['search'] == "null")
            {
                $filters['search']                      = null;
            } 
            if($filters['status'] == "null")
            {
                $filters['status']                      = null;
            } 
            if($filters['date_from'] == "null")
            {
                $filters['date_from']                   = null;
            } 
            if($filters['date_to'] == "null")
            {
                $filters['date_to']                     = null;
            } 
        }
        $query                                          = Tbl_dragonpay_transaction::leftjoin('tbl_slot','tbl_slot.slot_id','tbl_dragonpay_transaction.buyer_slot_id')->leftjoin('users','users.id','tbl_slot.slot_owner') 
                                                        ->select("tbl_dragonpay_transaction.*",'tbl_slot.*','users.id','users.name');

        if($filters['search'] != null)              
        { 
            $search                                     = $filters['search'];
            $query                                      = $query->where(function($query) use ($search)
                                                        {
                                                            $query->where("users.name", "like", "%".$search."%")
                                                                  ->orWhere("tbl_slot.slot_no", "like", "%".$search."%");
                                                        });
        }
        if($filters['status'] != "all")
        {              
			$query                                      = $query->where('dragonpay_status',$filters['status']);
        }               
        if(isset($filters["date_from"]))                
		{               
			$query                                      = $query->whereDate("tbl_dragonpay_transaction.created_at", ">=", $filters["date_from"]);
		}               
		if(isset($filters["date_to"]))              
		{               
			$query                                      = $query->whereDate("tbl_dragonpay_transaction.created_at", "<=", $filters["date_to"]);
		}               

        $return                                         = $export == 0 ? $query->paginate(15) : $query->get();

        foreach($return as $key => $value)
        {
            $return[$key]->items                        = json_decode($value->ordered_item);
            if($value->dragonpay_status == "S")
            {
                $return[$key]->dragonpay_status         = "Success";  
            }
            if($value->dragonpay_status == "P")
            {
                $return[$key]->dragonpay_status         = "Pending";  
            }
            if($value->dragonpay_status == "F")
            {
                $return[$key]->dragonpay_status         = "Failed";  
            }
            if($value->dragonpay_status == null)
            {
                $return[$key]->dragonpay_status         = "---";  
            }
            foreach ($return[$key]->items as $key2 => $value2) 
            {
                $value2->item_sku                       = Tbl_item::where('item_id',$value2->item_id)->pluck('item_sku')->first();
            }
        }
        return $return;
        
    }
    public function get_for_approvals($filters = null, $export = 0)
    {
        if($export == 0)
        {
            $filters                                    = Request::input();
        }
        else
        {
            $filters                                    = $filters;
            if($filters['search'] == "null")
            {
                $filters['search']                      = null;
            } 
            if($filters['status'] == "null")
            {
                $filters['status']                      = null;
            } 
            if($filters['date_from'] == "null")
            {
                $filters['date_from']                   = null;
            } 
            if($filters['date_to'] == "null")
            {
                $filters['date_to']                     = null;
            } 
        }
        $query                                          = Tbl_orders_for_approval::leftjoin('tbl_slot','tbl_slot.slot_id','tbl_orders_for_approval.slot_id')->leftjoin('users','users.id','tbl_slot.slot_owner') 
                                                        ->select("tbl_orders_for_approval.*", "tbl_orders_for_approval.id as trans_id",'tbl_slot.*','users.id','users.name');

        if($filters['search'] != null)              
        { 
            $search                                     = $filters['search'];
            $query                                      = $query->where(function($query) use ($search)
                                                        {
                                                            $query->where("users.name", "like", "%".$search."%")
                                                                  ->orWhere("tbl_slot.slot_no", "like", "%".$search."%")
                                                                  ->orWhere('tbl_orders_for_approval.transaction_number', "like", "%".$search."%");
                                                        });
        }
        if($filters['status'] != "all")
        {              
			$query                                      = $query->where('admin_status',$filters['status']);
        }               
        if(isset($filters["date_from"]))                
		{               
			$query                                      = $query->whereDate("tbl_orders_for_approval.date_created", ">=", $filters["date_from"]);
		}               
		if(isset($filters["date_to"]))              
		{               
			$query                                      = $query->whereDate("tbl_orders_for_approval.date_created", "<=", $filters["date_to"]);
		}               

        $return                                         = $export == 0 ? $query->orderBy('tbl_orders_for_approval.date_created','DESC')->paginate(15) : $query->get();

        foreach($return as $key => $value)
        {
            // dd($value);
            $return[$key]['item_list'] = json_decode($value->items);
            $return[$key]['grandtotal'] = $value->grandtotal - $value->other_discount;

            foreach ($return[$key]['item_list'] as $key2 => $value2) {
                
               $value2->item_sku    =  Tbl_item::where('item_id',$value2->item_id)->pluck('item_sku')->first();
            }
            // foreach (json_decode($value->items) as $key2 => $value2) 
            // {
            //     $value2->item_list[$key2]                       = Tbl_item::where('item_id',$value2->item_id)->first();
            // }
        }
        return $return;
        
    }
    public function view_information()
    {
        $id                             = Request::input('id');
        $return                         = Tbl_orders_for_approval::where('id',$id)->first();

        $return->name                   = Users::where('id',$return['user_id'])->pluck('name')->first();
        // $return->grandtotal_adjusted    = $return['grandtotal'] - $return['other_discount'];
        $return->grandtotal_adjusted    = $return['grandtotal'] + $return['shipping_fee'];
        return $return;
    }
    public function update_transaction()
    {
        $id                             = Request::input('id');
        $status                         = Request::input('status');

        $update['admin_status']         = $status;
        $update['date_approved']        = Carbon::now();
        // $update['other_discount']       = Request::input('adjustment');
        $update['shipping_fee']         = Request::input('shipping_fee');
        $update['remarks']              = Request::input('remarks');


        Tbl_orders_for_approval::where('id',$id)->update($update);

        return "";
    }

    public static function change_order_status_for_cashier($order_id) {
        Tbl_orders::where("order_id", $order_id)
        ->update(["order_status" => "claimed"]);
    }

    public static function refund_wallet_amount($order) {
        if($order->payment_method == 3 || $order->payment_method == 4) {
            if($order->payment_method == 3) {
                $currency_id = Tbl_currency::where('currency_id', 4)->first();
            }
            else if($order->payment_method == 4) {
                $currency_id = Tbl_currency::where('currency_buying', 1)->first();
            }
            $check_if_already_refund = Tbl_wallet_log::where('wallet_log_slot_id', $order->buyer_slot_id)->where('wallet_log_details', 'Shop/Purchase Refunded (Order No: ' . $order->order_id . ')')->first();
            if(!$check_if_already_refund) {
                Log::insert_wallet($order->buyer_slot_id, $order->grand_total, "Shop/Purchase Refunded (Order No: " . $order->order_id . ")", $currency_id->currency_id);
                $return["status"] = "success";
                return $return;
            } else {
                $return["status_message"] = "This order has already been refunded!";
                $return["status"] = "error";
                return $return;
            }
        }   
    }
}
