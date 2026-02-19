<?php
namespace App\Http\Controllers\Cashier;
use App\Globals\Code;
use App\Globals\Item;
use App\Globals\Cashier;
use App\Globals\Member;
use App\Models\Refbrgy;
use App\Models\Refcitymun;
use App\Models\Refprovince;
use App\Models\Refregion;
use App\Models\Tbl_address;
use App\Models\Tbl_binary_settings;
use App\Models\Tbl_cashier;
use App\Models\Tbl_branch;
use App\Models\Tbl_inventory;
use App\Models\Tbl_item;
use App\Models\Tbl_codes;
use App\Models\Tbl_item_stairstep_rank_discount;
use App\Models\Tbl_mlm_plan;
use App\Models\Tbl_slot;
use App\Models\Tbl_orders;
use App\Models\Tbl_receipt;

use App\Models\Tbl_tree_placement;
use App\Models\Tbl_user_process;
use PDF;
use Excel;
use App\Models\Users;
use App\Globals\Slot;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Tbl_item_membership_discount;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Validator;

class CashierItemController extends CashierController
{
    public function get_item_list()
    {
        $search    = Request::input('item_search');
        $return = Tbl_item::where('archived', 0)
        ->where(function ($query) {
            $query->where('item_availability', 'cashier')
                ->orWhere('item_availability', 'all');
        });

        if($search)
        {
            $return = $return->where("item_sku", "like", "%". $search . "%");
        }

        $return = $return->get();
        return response()->json($return);
    }
    public function get_customer_list()
    {
        $search_key = Request::input('customer_search');
        $type = Request::input('type');
        $response = Member::get('member',$search_key, $type);
        return response()->json($response);
    }
    public function checkout()
    {
        $user = Users::where('id', Request::user()->id)->first();
        if($user->type == "cashier")
        {
            $cashier   = Request::user()->id;
            $cashier_info = DB::table('tbl_cashier')->where('cashier_user_id', $cashier)->first();
            $response = Item::get_cart(Request::input(), $cashier_info->cashier_branch_id);
        }
        else
        {
            $stockist_info = DB::table('tbl_stockist')->where('stockist_user_id', $user->id)->first();
            $response = Item::get_cart(Request::input(), $stockist_info->stockist_branch_id);
        }
        
        return response()->json($response);
    }
    public function select()
    {
        $data = Request::input();
        $currency = DB::table('tbl_currency')->where('currency_buying', 1)->first();
        $gc_currency = DB::table('tbl_currency')->where('currency_name', 'Gift Card')->first();
        $response['slot'] = Tbl_slot::where('slot_owner', $data['user_id'])->Wallet($currency->currency_id)->where('slot_status', 'active')->first();
        $response['other_slots'] = Tbl_slot::where('slot_owner', $data['user_id'])->where('slot_status', 'active')->get();
        $response['user'] = Users::where('id', $data['user_id'])->first();
        $response['address'] = Tbl_address::where('user_id', $data['user_id'])->where('is_default', 1)->first();
        $response['slot_gc'] = Tbl_slot::where('slot_owner', $data['user_id'])->Wallet($gc_currency->currency_id)->where('slot_status', 'active')->first();
        return response()->json($response);
    }

    public function select_slot()
    {
        $data = Request::input();
        $currency = DB::table('tbl_currency')->where('currency_buying', 1)->first();
        $response = Tbl_slot::where('slot_no', $data['slot_no'])->Wallet($currency->currency_id)->first();
        $response->gc_currency = DB::table('tbl_wallet')->where('slot_id', $response['slot_id'])->where('currency_id',4)->first();
        return response()->json($response);
    }

    public function adjust_discount()
    {
        $slot        = Request::input('slot');
        $item        = Request::input('items');
        // dd($item);
        $return = null;
        foreach($item as $key => $value)
        {
            $discount                   = Cashier::get_customer_discount($slot['slot_id'], $value['item_id']);
            $item_price                 = Tbl_item::where('item_id',$value['item_id'])->pluck('item_price')->first();
            $return[$key]               = $value;
            $return[$key]['discount']   = $discount['percentage'];
            // $discount_to_deduct         = $item_price * ($discount['percentage']/100);
            $discount_to_deduct         = $discount['percentage']; //MAKE IT FIX
            $return[$key]['item_price'] = $item_price - $discount_to_deduct;
            $return[$key]['org_price'] = $item_price;
        }
        
        return response()->json($return);
    }
    public function get_user()
    {
        $check_user_details    =  Users::where('id', Request::user()->id)->first();
        if($check_user_details->type == 'cashier')
        {
            $get_user_details    =  Users::where('id', Request::user()->id)->join('tbl_cashier', 'tbl_cashier.cashier_user_id', '=', 'users.id')->first();
            $branch_details = Tbl_branch::where('branch_id', $get_user_details->cashier_branch_id)->first();
            $return['name']  = $get_user_details->name;
            $return['branch_id'] = $get_user_details->cashier_branch_id;
            $return['branch'] = $branch_details;
            $return['cashier'] = $get_user_details;
            $return['branch_cashiers'] = Tbl_cashier::where('cashier_branch_id', $branch_details->branch_id)->join('users', 'users.id', '=', 'tbl_cashier.cashier_user_id')->get();
            $return['binary_settings'] = Tbl_binary_settings::first();
        }
        else
        {
            $get_user_details    =  Users::where('id', Request::user()->id)->join('tbl_stockist', 'tbl_stockist.stockist_user_id', '=', 'users.id')->first();
            $branch_details = Tbl_branch::where('branch_id', $get_user_details->stockist_branch_id)->first();
            $return['name']  = $get_user_details->name;
            $return['branch_id'] = $get_user_details->stockist_branch_id;
            $return['branch'] = $branch_details;
            $return['cashier'] = $get_user_details;
        }
        
        return response()->json($return);
    }
    public function process_sale()
    {
        $payment    = Request::input('payment');
        $items      = Request::input('items');
        $slot       = Request::input('slot');
        $pick_up       = Request::input('pick_up');
        $manager_discount   = Request::input('manager_discount');
        $vat            = Request::input('vat');
        $remarks       = Request::input('remarks');
        $response   = Item::cashier_sale($payment, $items, $slot, $pick_up, $vat , $manager_discount, $remarks);
        return response()->json($response);
    }
    public function get_claim_code_list()
    {
        $response   = Cashier::get_claim_codes(Request::input());
        return response()->json($response);
    }
    public function select_claim_code()
    {
        $receipt_id = Request::input('receipt_id');
        $response   = Cashier::select_claim_code($receipt_id);
        return response()->json($response);
    }
    public function update_claim_code()
    {
        $receipt_id = Request::input('receipt_id');
        $claim_code = Request::input('claim_code');
        $response   = Cashier::select_claim_code($receipt_id, $claim_code);
        return response()->json($response);
    }

    public function register_member()
    {
        $response = Member::add_member(request()->all(), 'cashier');
        return response()->json($response);
    }

    public function load_membership_kit()
    {
        $response = Item::get_membership_kit();
        return response()->json($response);
    }

    public function create_slot()
    {
        $slot_info = Request::input('slot_info');
        $payment = intval($slot_info['payment']);
        $user_id = Request::input('user_id');
        $cashier = Request::user()->id;
        $payment_method = $slot_info['payment_method'];
       
        $cashier_info = DB::table('tbl_cashier')->where('cashier_user_id', $cashier)->first();
        $inventory = DB::table('tbl_inventory')->where('inventory_branch_id', $cashier_info->cashier_branch_id)->where('inventory_item_id', $slot_info['membership_kit'])->first();
        $code = Tbl_codes::where('code_inventory_id', $inventory->inventory_id)->CheckIfUsed(0)->CheckIfSold(0)->where('archived', 0)->first();
        if(isset($code))
        {
            $data['code'] = $code->code_activation;
            $data['pin']  = $code->code_pin;
            $data['slot_owner'] = $user_id;
            $data['slot_sponsor'] = strtoupper($slot_info['slot_sponsor']);
            $data['from_admin'] = 1;
            if(isset($slot_info['slot_code']))
            {
                $slot_code = strtoupper($slot_info['slot_code']);
            }
            else
            {
                $slot_code = null;
            }
            $item_sold = Tbl_item::where('item_id', $slot_info['membership_kit'])->first();
        
            if($payment >= $item_sold->item_price)
            {
                $slot = Slot::create_slot($data, $slot_code);
                if($slot['status_code'] < 300)
                {
                    $buyer = Users::join('tbl_slot', 'tbl_slot.slot_owner', '=', 'users.id')->where('tbl_slot.slot_id', $slot['status_data_id_inc'])->first();
                    $item['item_id'] = $slot_info['membership_kit'];
                    $item['quantity'] = 1;
                    $item['discounted_price'] = $item_sold->item_price;
                    $order = json_encode([$item]);
                    $discount = Self::get_customer_discount($buyer->slot_id, $item_sold->item_id);
                    $discount['original_price'] = $item_sold->item_price;
                    $change = $payment - $item_sold->item_price;
                    $insert['items']				= $order;
                    $insert['delivery_method']		= 'none';
                    $insert['delivery_charge']		= 0;
                    $insert['subtotal']				= $item_sold->item_price;
                    $insert['buyer_name']			= $buyer->name;
                    $insert['buyer_slot_code']		= $buyer->slot_no;
                    $insert['buyer_slot_id']		= $buyer->slot_id;
                    $insert['order_date_created']	= Carbon::now();
                    $insert['change']				= $change;
                    $insert['discount']				= json_encode([$discount]);
                    $insert['grand_total']			= $item_sold->item_price;
                    $insert['retailer']				= $inventory->inventory_branch_id;
                    $insert['order_from']			= 'cashier';
                    $insert['cashier_id']			= $cashier_info->cashier_id;
                    $insert['order_status']			= 'completed';
                    $insert['manager_discount']		= 0;
                    $insert['tax_amount'] 			= 0;
                    $insert['payment_method'] 		= $payment_method;
                    $order_id = Tbl_orders::insertGetId($insert);

                    $x = 0;
                    $ordered_item = json_decode($order);
                    foreach($ordered_item as $key => $items)
                    {
                            if(isset($items->item_id))
                            {
                                $insert_order['rel_order_id'] 				= $order_id;
                                $insert_order['item_id'] 					= $items->item_id;
                                $insert_order['quantity'] 					= $items->quantity;
                                DB::table('tbl_orders_rel_item')->insert($insert_order);
                            }
                        else
                        {
                            if($key == 'item_id')
                            {
                                $insert_order2['rel_order_id'] = $order_id;
                                $insert_order2['item_id'] = $items;
        
                            }
                            if($key == 'quantity')
                            {
                                $insert_order2['quantity'] = $items;
                            }
                            $x = 1;
                        }
                    }
                    if($x > 0)
                    {
                        DB::table('tbl_orders_rel_item')->insert($insert_order2);
                        $x = 0;
                    }
                    $receipt_id = Cashier::create_receipt($order_id, 'cashier');

                    if($receipt_id > 0)
                    {
                        $return['receipt']           = DB::table('tbl_receipt')->where('receipt_id', $receipt_id)->first();
                        $return['item']              = Tbl_item::where('item_id', $item['item_id'])->first();
                        $return["status"]            = "success"; 
                        $return["status_code"]       = 201; 
                        $return["status_message"]    = "Slot Created";
                    }
                }
                else
                {
                    return response()->json($slot);
                }
                
            }
            else
            {
                $return["status"]         = "error"; 
                $return["status_code"]    = 401; 
                $return["status_message"] = "Insufficient Payment";
            }
        }
        else
        {
            $return["status"]         = "error"; 
            $return["status_code"]    = 401; 
            $return["status_message"] = "Insufficient Inventory";
        }

        return response()->json($return);
    }

    public function select_for_slot_creation()
    {
        $response = Users::where('id', Request::input('user_id'))
        ->JoinSlot()
        ->leftJoin("tbl_slot as sponsor","sponsor.slot_id","=","tbl_slot.slot_sponsor")
        ->select("tbl_slot.*","users.*","sponsor.slot_no as slot_sponsor_no")
        ->first();
        return response()->json($response);

    }
    public function check_password()
    {
        $password       = Request::input('password');
        $response = Member::check_password($password);
        return response()->json($response);
    }
    public function check_invoice()
    {
        $return['invoice_number'] = DB::table('tbl_receipt')->count();
        $date = Carbon::now();
        $return['date_order'] = date_format($date, "Y/m/d H:i:s");
        
        return response()->json($return);
    }
    public function check_stocks()
    {
        $check_user = Users::where('id', Request::user()->id)->first();
        if($check_user->type == "cashier")
        {
            $cashier = Tbl_cashier::where('cashier_user_id', Request::user()->id)->first();
            $inventory =  Tbl_inventory::where('inventory_branch_id', $cashier->cashier_branch_id)->get();
        }
        else
        {
            $stockist = DB::table('tbl_stockist')->where('stockist_user_id', Request::user()->id)->first();
            $inventory =  Tbl_inventory::where('inventory_branch_id', $stockist->stockist_branch_id)->get();
        }
        $holder = 0;
        foreach($inventory as $key => $value)
        {
            $item = Tbl_item::JoinInventory()->JoinBranch()->where('tbl_item.item_id', $value->inventory_item_id)->where('inventory_branch_id', $value->inventory_branch_id)->where('tbl_item.archived', 0)->first();
            if($item)
            {
                $return[$holder] = $item;
                $holder = $holder + 1;
            }
        }
        return $return;
    }

    public static function list_of_codes(){

        $membership = Request::input('filter');
		$status = Request::input('status');
		$search = Request::input('search');
		$paginate = Request::input('paginate');
        $response = Code::load_membership_code_by_cashier($membership, $status, $search, $paginate);

        return $response;
    }

    public static function load_list_of_codes($membership, $status, $search, $paginate){

        $response = Code::load_membership_code_by_cashier($membership, $status, $search, $paginate);

        return $response;
    }

    public function recount_inventory()
    {
        Item::recount_inventory();
        $response = Item::recount_inventory();

        return response()->json($response);
    }
    
    public function get_payment_type()
    {
        $response = DB::table('tbl_cashier_payment_method')->where('cashier_payment_method_status', 1)->get();

        return response()->json($response);
    }

    public static function load_sales_report($export = 0)
    {
        $data      = Request::input();
        // $item           = Request::input('item');
        $query          = Tbl_receipt::where('tbl_receipt.retailer', $data['branch_id'])
                                        ->join('tbl_orders', 'tbl_orders.order_id', '=', 'tbl_receipt.receipt_order_id')
                                        ->where('tbl_orders.order_from', 'cashier');

        if($data['sales_person'] > 0)
        {
            $query = $query->where('tbl_orders.cashier_id', $data['sales_person']);
        }

        $query = $query->join('tbl_slot', 'tbl_slot.slot_id', '=', 'tbl_orders.buyer_slot_id');
        $query = $query->join('users', 'users.id', '=', 'tbl_slot.slot_owner');
        
        if(isset($data['date_from']))
        {
            $query->whereDate('receipt_date_created', '>=', $data['date_from']);
        }

        if(isset($data['date_to']))
        {
            $query->whereDate('receipt_date_created', '<=', $data['date_to']);
        }
        
        $receipt = $query->paginate(15);
        foreach($receipt as $key => $value)
        {
            $receipt[$key]['items'] = DB::table('tbl_receipt_rel_item')->where('rel_receipt_id', $value->receipt_id)->join('tbl_item', 'tbl_item.item_id', '=', 'tbl_receipt_rel_item.item_id')->get();
            $receipt[$key]['payment_method'] = DB::table('tbl_cashier_payment_method')->where('cashier_payment_method_id', $value->payment_method)->first();
            $receipt[$key]['tax_amount'] = round($value->tax_amount, 2);
        }

        if(!$receipt)
        {
            $receipt == 1;
        }
        return $receipt;

    }

    public function get_access()
    {
        $cashier = Request::input('cashier');
        if($cashier['type'] == 'cashier')
        {
            $return =  DB::table('tbl_cashier_access')->where('cashier_access_branch', $cashier['cashier_branch_id'])->where('cashier_type', $cashier['cashier_position'])->first();
        }
        else
        {
            $return =  DB::table('tbl_cashier_access')->where('cashier_access_branch', $cashier['stockist_branch_id'])->where('cashier_type', "Stockist")->first();
        }
        return response()->json($return);
    }

    public function upline_preview()
    {
        $type = Request::input("type"); 
        $_data  = Request::input("data");
        $sponsor_id = Tbl_slot::where('slot_no', $_data["slot_no"])->first()->slot_sponsor;
        $owner_id   = $sponsor_id;
        $data["slot_code"] = $_data["slot_no"];
        $data["slot_position"] =  $_data["position"];
        // dd($_data);
        $binary_extreme_enable = Tbl_binary_settings::first()->binary_extreme_position;
        if($binary_extreme_enable) {
            $slot = Tbl_slot::where('slot_id', $owner_id)->first();
            $last_outer =Tbl_tree_placement::where("placement_parent_id",$owner_id)->where("placement_position",$data['slot_position'])->where("position_type","OUTER")->orderBy('tree_placement_id','desc')->first();
            if($last_outer)
            {
                $slot2 = Tbl_slot::where('slot_id',$last_outer->placement_child_id)->first();
                $data['slot_placement'] = $slot2->slot_no;
            } else {
                $data['slot_placement'] = $slot->slot_no;
            }
        } else {
            $data["slot_placement"] = $_data["placement"];
        }
        $i = 0;
		$return["status_message"] = [];

		$placement = $data["slot_placement"];
		$position  = $data["slot_position"];
		$slot_no   = $data["slot_code"];
		$rules["slot_placement"]  = "required|exists:tbl_slot,slot_no";
		$rules["slot_code"]       = "required|exists:tbl_slot,slot_no";

		$validator = Validator::make($data, $rules);
        if ($validator->fails())
        {
			foreach ($validator->errors()->getMessages() as $key => $value)
			{
				foreach($value as $val)
				{
					$response["status_message"][$i] = $val;
				    $i++;
				}
			}
        }
        else
        {
         	if($position != "LEFT" && $position != "RIGHT")
         	{
				$response["status_message"][$i] = "Please select Placement Position";
				$i++;
         	}

         	$target_slot     	 	= Tbl_slot::where("slot_no",$slot_no)->first();


			/* PREVENTS MULTIPLE PROCESS AT ONE TIME */
			$user_process_level = 1;
			Tbl_user_process::where("user_id",$target_slot->slot_owner)->delete();

			$insert_user_process["level_process"] = $user_process_level;
			$insert_user_process["user_id"]       = $target_slot->slot_owner;
			Tbl_user_process::where("user_id",$target_slot->slot_owner)->where("level_process",$user_process_level)->insert($insert_user_process);

			while($user_process_level <= 4)
			{
				$user_process_level++;
				$insert_user_process["level_process"] = $user_process_level;
				$insert_user_process["user_id"]       = $target_slot->slot_owner;
				Tbl_user_process::where("user_id",$target_slot->slot_owner)->where("level_process",$user_process_level)->insert($insert_user_process);

				$count_process_before = Tbl_user_process::where("user_id",$target_slot->slot_owner)->where("level_process", ($user_process_level - 1) )->count();

				if($count_process_before != 1)
				{
				   $response["status_message"][$i] = "Please try again...";
				   $i++;
			  	   break;
				}
			}


			Tbl_user_process::where("user_id",$target_slot->slot_owner)->delete();

         	$slot_id         	 	= $target_slot->slot_id;
         	$slot_sponsor_id 	 	= $target_slot->slot_sponsor;


         	$placement       	 	= Tbl_slot::where("slot_no",$placement)->first()->slot_id;
         	$check_placement 	 	= Tbl_slot::where("slot_placement",$placement)->where("slot_position",$position)->first();
        
            $check_binary_settings  = Tbl_binary_settings::first();
			$check_plan_binary      = Tbl_mlm_plan::where('mlm_plan_code','=','BINARY')->first()->mlm_plan_enable;
			if($check_binary_settings)
			{
				if($check_binary_settings->crossline == 1 && $check_plan_binary == 1)
				{
					if($slot_sponsor_id != $placement)
					{
						$check_sponsor_under = Tbl_tree_placement::where('placement_parent_id',$slot_sponsor_id)->where('placement_child_id',$placement)->first();
						if($check_sponsor_under == null)
						{
							$response["status_message"][$i] = "Attempting crossline...";
							$i++;
						}
					}
				}
			}
         	if($check_placement)
         	{
				$response["status_message"][$i] = "Placement already taken...";
				$i++;
         	}
         	else
         	{
         		$check_placement = Tbl_slot::where("slot_id",$placement)->first();
				if( ($check_placement->slot_placement == 0 && $check_placement->slot_sponsor != 0) || $check_placement->membership_inactive == 1)
				{
					$response["status_message"][$i] = "Placement is not allowed on unplaced slot";
					$i++;
				}
         	}

         	if($target_slot->slot_placement != 0)
         	{
				$response["status_message"][$i] = "This slot is already placed.";
				$i++;
         	}

         	if($type == "member_downline")
         	{
                 $slot_owned  = Tbl_slot::where("slot_no",$slot_no)->first();
         		if(!$slot_owned)
         		{
					$response["status_message"][$i] = "Error 501...";
					$i++;
         		}
         		else
         		{
                    $check_sponsor = Tbl_slot::where("slot_id",$slot_owned->slot_sponsor)->where("slot_owner",$owner_id)->first();
         			if(!$check_sponsor)
         			{
						$response["status_message"][$i] = "Error 503...";
						$i++;
         			}
         		}
         	}
        }
        if($i == 0)
        {
            $response["details"]        = Tbl_slot::where("slot_no",$data["slot_placement"])->Owner()->first();
        }
        else
        {
            $response["status"]         = "error";
			$response["status_code"]    = 400;
        }
        return response()->json($response);
    }

    public function submit_placement()
    {

      $owner_id = Request::input("owner_id");
      $data["slot_position"]   = Request::input("position");
      $data["slot_code"]       = Request::input("slot_no");
      $slot = Tbl_slot::where('slot_id',$owner_id)->first();
      $binary_extreme_enable = Tbl_binary_settings::first()->binary_extreme_position;

      if($binary_extreme_enable) {
        $last_outer =Tbl_tree_placement::where("placement_parent_id",$owner_id)->where("placement_position",$data['slot_position'])->where("position_type","OUTER")->orderBy('tree_placement_id','desc')->first();
      
        if($last_outer)
        {
            $slot2 = Tbl_slot::where('slot_id',$last_outer->placement_child_id)->first();
            $data['slot_placement'] = $slot2->slot_no;
        } else {
            $data['slot_placement'] = $slot->slot_no;
        }
      } else {
        $data["slot_placement"]  = Request::input("placement");
      }

      $response                = Slot::place_slot($data,"member_downline",$owner_id);
      $placement                 = Tbl_slot::where("slot_no",$data["slot_placement"])->first();
      $new_slot                  = Tbl_slot::where("slot_placement",$placement->slot_id)->where("slot_position",Request::input("position"))->owner()->select("last_name","first_name","slot_id","slot_no","slot_position","slot_placement")->first();
      if($new_slot)
      {
        $response["slot_placement"]= $placement->slot_id;
        $response["new_slot"]      = $new_slot;
        $response["placement"]     = $response["new_slot"]->slot_no;
        $response["level"]         = Tbl_tree_placement::where("placement_parent_id",Request::input("root_id"))->where("placement_child_id",$placement->slot_id)->first() ? Tbl_tree_placement::where("placement_parent_id",Request::input("root_id"))->where("placement_child_id",$placement->slot_id)->first()->placement_level + 1 : 1;
      }
      else 
      {
        $response["status"]  = "error";
        $response["status_message"][0] = "Slot Not Placed";
      }
      return $response;
    }
    
    public static function get_customer_discount($slot_id, $item_id)
	{
		$slot = Tbl_slot::where('slot_id', $slot_id)->first();
		if($slot->slot_type == "SS")
		{
			$stockist = DB::table('tbl_stockist')->where('stockist_user_id', $slot->slot_owner)->first();
			$stockist_discount = DB::table('tbl_item_stockist_discount')->where('item_id', $item_id)->where('stockist_level_id', $stockist->stockist_level)->first();
			if($stockist_discount)
			{
				$discount['item_id'] = $item_id;
				$discount['type'] 	 = 'stockist';
				$discount['percentage']	= $stockist_discount->discount;
			}
			else
			{
				$discount['item_id'] = $item_id;
				$discount['type'] 	 = 'none';
				$discount['percentage']	= 0;
			}
		}
		else
		{
			$check_membership_discount = Tbl_item_membership_discount::where('membership_id', $slot->slot_membership)->where('item_id', $item_id)->first();
			if($check_membership_discount)
			{
				$membership_discount = $check_membership_discount->discount;
			}
			else
			{
				$membership_discount = 0;
			}

			$check_rank_discount = Tbl_item_stairstep_rank_discount::where('stairstep_rank_id', $slot->slot_stairstep_rank)->where('item_id', $item_id)->first();

			if($check_rank_discount)
			{
				$rank_discount = $check_rank_discount->discount;
			}
			else
			{
				$rank_discount = 0;
			}

			if($membership_discount > $rank_discount)
			{
				$discount['item_id'] = $item_id;
				$discount['type'] 	 = 'membership';
				$discount['percentage']	= $membership_discount;
			}
			elseif($rank_discount > $membership_discount)
			{
				$discount['item_id'] = $item_id;
				$discount['type'] 	 = 'rank';
				$discount['percentage']	= $rank_discount;
			}
			else
			{
				$discount['item_id'] = $item_id;
				$discount['type'] 	 = 'none';
				$discount['percentage']	= 0;
			}
		}
		return $discount;
	}
    
    public function get_addresses()
	{
        $slot_owner = Tbl_slot::where('slot_id', Request::input('slot_id'))->first()->slot_owner ?? null;

        $list = Tbl_address::where('tbl_address.user_id', $slot_owner)->where("tbl_address.archived",0)->where("tbl_address.is_default", 1)
                ->leftjoin('tbl_receiver_infomation','tbl_receiver_infomation.address_id','tbl_address.address_id')->select('*',DB::raw('tbl_address.address_id as address_id'))->first();

        if($list) {
            $address_list['refregion'] = Refregion::where('refregion.regCode', '=' ,$list->regCode)->pluck('regDesc')->first();
            $address_list['refprovince'] = Refprovince::where('refprovince.provCode', '=' ,$list->provCode)->pluck('provDesc')->first();
            $address_list['refcitymun'] = Refcitymun::where('refcitymun.citymunCode', '=' ,$list->citymunCode)->pluck('citymunDesc')->first();
            $address_list['refbrgy'] = Refbrgy::where('refbrgy.brgyCode', '=' ,$list->brgyCode)->pluck('brgyCode')->first();
            $address_list['brgyDesc'] = Refbrgy::where('refbrgy.brgyCode', '=' ,$list->brgyCode)->pluck('brgyDesc')->first();

            $address_list['complete_address'] 	= $list->additional_info . ", " . $address_list['brgyDesc'].", ".$address_list['refcitymun'] .", ".  $address_list['refprovince'] ." - ". $list->address_postal_code;
        } else {
            $address_list = null;
        }

		return 	response()->json($address_list);
	}
}
