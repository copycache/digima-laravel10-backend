<?php
namespace App\Globals;

use App\Models\Tbl_dropshipping_list;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Request;
use App\Models\Tbl_cashier;
use App\Models\Tbl_inventory;
use App\Models\Users;
use App\Models\Tbl_item;
use App\Models\Tbl_orders;
use App\Models\Tbl_cashier_sales;
use App\Models\Tbl_slot;
use App\Models\Tbl_receipt;
use App\Models\Tbl_currency;
use App\Models\Tbl_item_membership_discount;
use App\Models\Tbl_item_stairstep_rank_discount;
use App\Models\Tbl_codes;
use App\Models\Tbl_branch;
use App\Models\Tbl_delivery_charge;
use App\Models\Tbl_mlm_plan;
use App\Models\Tbl_dragonpay_transaction;
use App\Models\Tbl_dragonpay_settings;
use App\Models\Tbl_wallet_log;
use App\Models\Tbl_orders_for_approval;
use App\Models\Tbl_cod_list;


use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use App\Models\Tbl_wallet;
use App\Globals\MLM;
use App\Globals\Audit_trail;
use App\Globals\DragonPay;
class Cashier
{
	public static function add($data)
	{

		$rules["full_name"] 		= "required";
		$rules["email"] 			= "required|email";
		$rules["password"] 			= "required";
		$rules["address"] 			= "required";
		$rules["contact_number"]	= "required";

		$validator = Validator::make($data, $rules);

		if ($validator->fails())
		{
			$return["status"]         = "error";
			$return["status_code"]    = 400;
			$return["status_message"] = $validator->messages()->all();
		}
		else
		{
			$insert_user['name']								=	$data['full_name'];
			$insert_user['password']							=	Hash::make($data['password']);
			$insert_user['crypt']								= 	Crypt::encryptString($data['password']);
			$insert_user['email']								=	$data['email'];
			$insert_user['created_at']							=	Carbon::now();
			$insert_user['updated_at']							=	Carbon::now();
			$insert_user['type']								=	'cashier';


			$insert_cashier['cashier_branch_id']				=	$data['branch_id'];
			$insert_cashier['cashier_address']					=	$data['address'];
			$insert_cashier['cashier_contact_number']			=	$data['contact_number'];
			$insert_cashier['cashier_status']					=	$data['status'];
			$insert_cashier['cashier_position']					=	$data['position'];
			$insert_cashier['cashier_date_created']				=	Carbon::now();

			$cashier_user_id 									=	DB::table('users')->insertGetId($insert_user);
			$insert_cashier['cashier_user_id'] 					=	$cashier_user_id;

			Tbl_cashier::insert($insert_cashier);

			$insert_access_list['cashier_access_branch'] = $data['branch_id'];
			$insert_access_list['cashier_type'] 		 = $data['position'];
			DB::table('tbl_cashier_access')->insert($insert_access_list);
			$new_value['user']     = DB::table('users')->where('id',$cashier_user_id)->first();
			$new_value['cashier']  = Tbl_cashier::where('cashier_id',$cashier_user_id)->first();
			$new_value['cashier_access']  = DB::table('tbl_cashier_access')->where('cashier_access_id',$cashier_user_id)->first();
			$user   = Request::user()->id;
			$action = "Add Cashier";

			Audit_trail::audit(null,serialize($new_value),$user,$action);

			if($data['position'] == 'Cashier')
			{
				$return["status"]         = "success";
				$return["status_code"]    = 201;
				$return["status_message"] = "Cashier Created";
			}
			else
			{
				$return["status"]         = "success";
				$return["status_code"]    = 201;
				$return["status_message"] = "Manager Created";
			}
			

		}
		return $return;
	}

	public static function getList($branch_id, $filter = null)
	{
		$data = Tbl_cashier::where('cashier_branch_id', $branch_id)->join('users', 'tbl_cashier.cashier_user_id', '=', 'users.id');
		if(isset($filter["status"]) && $filter["status"] != "all")
		{
			$data = $data->where("cashier_status", $filter["status"]);
		}
		if(isset($filter["position"]) && $filter["position"] != "all")
		{
			$data = $data->where("cashier_position", $filter["position"]);
		}
		$data = $data->get();
		return $data;
	}

	public static function get_data($id)
	{

		$return = Tbl_cashier::where('cashier_id', $id)->join('users', 'tbl_cashier.cashier_user_id', '=', 'users.id')->first();
		$return->decrypted_password = Crypt::decryptString($return->crypt);
		return $return;
	}

	public static function cashier_update($data)
	{

		$update_user['name']						=	$data['name'];
		$update_user['email']						=	$data['email'];
		$update_user['password']					=	Hash::make($data['decrypted_password']);
		$update_user['crypt']						=	Crypt::encryptString($data['decrypted_password']);
		$update_cashier['cashier_position']			=	$data['cashier_position'];
		$update_cashier['cashier_address']			=	$data['cashier_address'];
		$update_cashier['cashier_status']			=	$data['cashier_status'];

		$old['cashier']  = Tbl_cashier::where('cashier_id', $data['cashier_id'])->first();
		$old['user']     = Users::where('id', $data['id'])->first();

		Tbl_cashier::where('cashier_id', $data['cashier_id'])->update($update_cashier);
		Users::where('id', $data['id'])->update($update_user);
		$new['cashier']  = Tbl_cashier::where('cashier_id', $data['cashier_id'])->first();
		$new['user']     = Users::where('id', $data['id'])->first();
		$action  		 = "Update_cashier";
		$user            = Request::user()->id;
		Audit_trail::audit(serialize($old),serialize($new),$user,$action);
		$return["status"]         = "success";
		$return["status_code"]    = 201;
		$return["status_message"] = "Cashier Updated";
	}

	public static function search($filters = null)
	{
		$data = Tbl_cashier::where("tbl_cashier.archived", 0);
		if(isset($filters["branch_type"]) && $filters["branch_type"] != "all")
		{
			$data = $data->where("branch_type", $filters["branch_type"]);
		}
		if(isset($filters["branch_location"]) && $filters["branch_location"] != "all")
		{
			$data = $data->where("branch_location", $filters["branch_location"]);
		}
		if(isset($filters["search_key"]))
		{
			$data = $data->where("branch_name", "like", "%". $filters["search_key"] . "%");
		}
		$data = $data->get();
		return $data;
	}

	public static function add_location($data)
	{
		foreach($data as $key => $value)
		{
			if($value["location"] == null || $value["location"] == "")
			{
				continue;
			}

			$rules["location"] = "required";

			$validator = Validator::make($value, $rules);

			if ($validator->fails())
			{
				$return["status"]         = "error";
				$return["status_code"]    = 400;
				$return["status_message"] = $validator->messages()->all();
			}
			else
			{
				$check_exist = DB::table('tbl_location')->where('archive', 0)->where('location', $value['location'])->first();
				$success_add = 0;
				if(!$check_exist)
				{
					$insert['location']			= 	$value['location'];

					DB::table('tbl_location')->insert($insert);
					$new_value = DB::table('tbl_location')->where('archive', 0)->where('location', $value['location'])->first();
					$action    = "Add Location";
					$user      = Request::user()->id;

					Audit_trail::audit(null,serialize($new_value),$user,$action);

					$success_add = $success_add + 1;
					$return["status"]         = "success";
					$return["status_code"]    = 200;
					$return["status_message"] = "Location created Successfully.";
				}

			}

		}


		if($success_add == 0)
		{
			$return["status"]         = "error";
			$return["status_code"]    = 400;
			$return["status_message"] = "Location already exists.";
		}
		return $return;

	}

	public static function get_location()
	{
		$return = DB::table('tbl_location')->where('archive', 0)->get();
		return $return;
	}

	public static function archive_location($data)
	{
		DB::table("tbl_location")->where("location", $data)->update(['archive' => 1]);
	}

	public static function ecom_checkout($data)
	{		
		$get_user 						= Request::user();
		$subtotal						= 0;

		//direct = pickup, indirect = delivery
		if($data['method'] == 'Direct')
		{
			$rules["branch_id"] 		= "required";

			$validator = Validator::make($data, $rules);

			if ($validator->fails())
			{
				$return["status"]         = "error";
				$return["status_code"]    = 400;
				$return["status_message"] = "Pick up location is required.";

				return $return;
			}
			else
			{

				// $delivery_charge 			= $data["method_charge"];
				$delivery_method 			= 'pickup';
				$address         			= $data["address"] ?? null;
				$order_status 				= 'processed';
				$retailer  	  				= $data['branch_id'];
				//if true, slot ng bumibili yung nakalogin
			}
		}
		else
		{
			$delivery_charge 				= $data["method_charge"];
			$delivery_method 				= 'delivery';
			$order_status 					= 'pending';
			$address         				= $data["address"] ?? null;
			//retailer is yung main branch
			$retailer 	  					= $data['branch_id'];
		}
		if($data['slot']['slot_owner'] == $get_user->id)
		{

			foreach($data['items'] as $key => $value)
			{
				if($data['payment_method'] == 'GC_Wallet')
				{
					if($value['item_gc_price'] > 0)
					{
						$items[$key]['item_id'] 				= $value['item_id'];
						$items[$key]['quantity'] 				= $value['item_qty'];
		
						$get_item[$key] 						= Tbl_item::where('item_id', $value['item_id'])->first();
						$check_item_kit[$key]['type'] 	  		= $get_item[$key]->item_type;
						$check_item_kit[$key]['item'] 	  		= $get_item[$key]->item_id;
						$check_item_kit[$key]['quantity'] 		= $value['item_qty'];
						$items[$key]['discounted_price']  		= $value['item_gc_price'];
					}
				}
				else
				{
					if($value['item_qty'] > 0)
					{
						$items[$key]['item_id'] 				= $value['item_id'];
						$items[$key]['quantity'] 				= $value['item_qty'];
		
						$get_item[$key] 						= Tbl_item::where('item_id', $value['item_id'])->first();
						$check_item_kit[$key]['type'] 	  		= $get_item[$key]->item_type;
						$check_item_kit[$key]['item'] 	  		= $get_item[$key]->item_id;
						$check_item_kit[$key]['quantity'] 		= $value['item_qty'];
						$items[$key]['discounted_price']  		= $value['discounted_price'];
					}
					else
					{
						$return["status"]         				= "Error";
						$return["status_code"]    				= 400;
						$return["status_message"] 				= "Invalid quantity";

						return $return;
					}
				}


				// $subtotal							 	= $subtotal + ($get_item[$key]->discounted_price * $value['item_qty']);
			}
			$currency_id								=	Tbl_currency::where('currency_buying',1)->pluck('currency_id')->first();
			$user 										= DB::table('tbl_slot')->where('tbl_slot.slot_id', $data['slot']['slot_id'])->join('users', 'tbl_slot.slot_owner', '=', 'users.id')->join('tbl_wallet', 'tbl_slot.slot_id', '=', 'tbl_wallet.slot_id')->where('currency_id', $currency_id)->first();
			// $grand_total 								= $subtotal + $delivery_charge;
		
			if($data['payment_method'] == 'Wallet')
			{
				if($user->wallet_amount < $data['grandtotal'])
				{
					$return["status"]         				= "Error";
					$return["status_code"]    				= 400;
					$return["status_message"] 				= "Not enough wallet.";
					return $return;	
				}	
				else if($user->wallet_amount >= $data['grandtotal'])	
				{	
					
					$payment 								= ["method" => "wallet", "amount" => $subtotal];
					$ordered_item							= json_encode($items);
					$vat									= 0;
					$buyer_slot_id							= $user->slot_id;
					$payment_given							= json_encode($payment);
					$cashier_user_id 						= $retailer;

					$return = Self::create_order($ordered_item, $vat, $buyer_slot_id, $cashier_user_id,'ecommerce', $delivery_method, null, 0, 0, null,$address,4,0,0,0,0,$data['shipping_fee'],$data['handling_fee'],$data['grandtotal'],$data['receiver_name'],$data['receiver_email'],$data['receiver_contact_number']);
					return $return;
				}
			}
			if($data['payment_method'] == 'GC_Wallet')
			{
				$user 										= DB::table('tbl_slot')->where('tbl_slot.slot_id', $data['slot']['slot_id'])->join('users', 'tbl_slot.slot_owner', '=', 'users.id')->join('tbl_wallet', 'tbl_slot.slot_id', '=', 'tbl_wallet.slot_id')->where('currency_id',4)->first();

				if($user->wallet_amount < $data['grandtotal'])
				{
					$return["status"]         				= "Error";
					$return["status_code"]    				= 400;
					$return["status_message"] 				= "Not enough wallet.";
					return $return;	
				}	
				else	
				{	
					
					$payment 								= ["method" => "wallet", "amount" => $subtotal];
					$ordered_item							= json_encode($items);
					$vat									= 0;
					$buyer_slot_id							= $user->slot_id;
					$payment_given							= json_encode($payment);
					$cashier_user_id 						= $retailer;
	
					$return = Self::create_order($ordered_item, $vat, $buyer_slot_id, $cashier_user_id,'ecommerce', $delivery_method, null, 0, 0, null,$address,3,0,0,0,0,$data['shipping_fee'],$data['handling_fee'],$data['grandtotal'],$data['receiver_name'],$data['receiver_email'],$data['receiver_contact_number']);

					return $return;
				}
			}
			else if($data['payment_method'] == 'Dragonpay')
			{
				// dd($data);
				$payment 									= ["method" => "dragonpay", "amount" => $subtotal];
				$ordered_item								= json_encode($items);
				$vat										= 0;
				$buyer_slot_id								= $user->slot_id;
				$payment_given								= json_encode($payment);
				$cashier_user_id 							= $retailer;
				$txnid										= "TRANS".time();

				$insert['ordered_item']						= $ordered_item;										
				$insert['vat']								= $vat;								
				$insert['buyer_slot_id']					= $buyer_slot_id;											
				$insert['cashier_user_id']					= $cashier_user_id;											
				$insert['from']								= 'ecommerce';								
				$insert['delivery_method']					= $delivery_method;											
				$insert['picked_up']						= null;										
				$insert['change']							= 0;									
				$insert['manager_discount']					= 0;											
				$insert['remarks']							= null;									
				$insert['address']							= $address;									
				$insert['cashier_method']					= 5;											
				$insert['dragonpay_charged']				= Tbl_dragonpay_settings::first()->service_charged;									
				$insert['payment_given']					= $payment_given;											
				$insert['status']							= 'Pending';
				$insert['subtotal']							= $data['subtotal'];
				$insert['grandtotal']						= $data['grandtotal'];
				$insert['dragonpay_txnid']					= $txnid;
				$insert['created_at']						= Carbon::now();

				// dd($insert);
				// $inventory_codes 							= Self::inventory_codes_ordered($ordered_item, $retailer, $buyer_slot_id, 1, $dragonpaytrans_id);
				$product_summary 							= "";
				
				foreach ($data['items'] as $key => $value) 
				{
					$get_inventory_quantity                 = Tbl_inventory::where('inventory_item_id',$value['item_id'])->pluck('inventory_quantity')->first();
					$product_summary 						.=$value["item_sku"] . $value["item_id"] . "(x" . $value["item_qty"] . ")-" ."PHP". number_format($value["discounted_price"]) . ",";
				
					if($value['item_qty'] > $get_inventory_quantity)
					{
						$return["status"]         			= "Error";
						$return["status_code"]    			= 400;
						$return["status_message"] 			= $value["item_sku"]." does not have enough inventory.";
	
						return $return;
					}
					else 
					{
						$inventory_codes['status_code'] 	= 200;
					}
				}
				
				if($inventory_codes['status_code'] < 400)
				{
					$dragonpaytrans_id 						= Tbl_dragonpay_transaction::insertGetId($insert);
					$return 								= Dragonpay::create_transaction($dragonpaytrans_id, $buyer_slot_id,$product_summary,$data['grandtotal'],$data['email_address'],$txnid);

					return $return;	
				}
			}
			else if($data['payment_method'] == 'COD')
			{
				// $grand_total										= $data['info']['grandtotal'];
				$payment 											= ["method" => "cod", "amount" => $subtotal];
				$ordered_item										= json_encode($items);
				$vat												= 0;
				$buyer_slot_id										= $user->slot_id;
				$payment_given										= json_encode($payment);
				$cashier_user_id 									= $retailer;

				$return = Self::create_order($ordered_item, $vat, $buyer_slot_id, $cashier_user_id,'ecommerce', $delivery_method, null, 0, 0, null,$address,6,0,0,0,0,$data['shipping_fee'],$data['handling_fee'],$data['grandtotal']);
				return $return;
			}
		}
		else
		{
			$return["status"]         = "Error";
			$return["status_code"]    = 400;
			$return["status_message"] = "This ain't yours, is it?";

			return $return;
		}
	}
	public static function inventory_codes_ordered($items,$retailer, $slot_id, $dragonpay_status = 0, $dgp_transid = null)
	{
		$decoded = json_decode($items);
		$customer = Tbl_slot::leftJoin('tbl_membership', 'tbl_membership.membership_id', '=', 'tbl_slot.slot_membership')->where('tbl_slot.slot_id', $slot_id)->join('users', 'tbl_slot.slot_owner', '=', 'users.id')->join('tbl_wallet', 'tbl_slot.slot_id', '=', 'tbl_wallet.slot_id')->first();
		$pass = 0;
		foreach($decoded as $key => $value)
		{
			$inventory = DB::table('tbl_inventory')->where('tbl_inventory.inventory_branch_id', $retailer)->where('tbl_inventory.inventory_item_id', $value->item_id)->first();
			$code = DB::table('tbl_codes')->where('code_inventory_id', $inventory->inventory_id)->where('code_used', 0)->where('code_sold',0)->where('archived', 0)->limit($value->quantity)->get();
			if(count($code) < $value->quantity)
			{
				$return["status"]         = "Error";
				$return["status_code"]    = 400;
				$return["status_message"] = "Not enough inventory.";
			}
			else
			{
				$pass = $pass + 1;
			}
		}
		if($pass == count($decoded))
		{
			unset($inventory);
			unset($code);

			foreach($decoded as $key => $value)
			{
				$inventory = DB::table('tbl_inventory')->where('tbl_inventory.inventory_branch_id', $retailer)->where('tbl_inventory.inventory_item_id', $value->item_id)->first();
				$code = DB::table('tbl_codes')->where('code_inventory_id', $inventory->inventory_id)->where('code_used', 0)->where('code_sold',0)->where('archived', 0)->limit($value->quantity)->get();

				if(count($code) < $value->quantity)
				{
					$return["status"]         = "Error";
					$return["status_code"]    = 400;
					$return["status_message"] = "Not enough inventory.";
				}
				else
				{
					if($customer->type == "stockist")
					{
						$stockist = DB::table('tbl_stockist')->where('stockist_user_id', $customer->id)->first();
						$new_inventory = Tbl_inventory::where('inventory_branch_id', $stockist->stockist_branch_id)->where('inventory_item_id',$value->item_id)->first();

						foreach($code as $key2 => $value2)
						{
							$update_code['code_inventory_id'] 	= $new_inventory->inventory_id;
							DB::table('tbl_codes')->where('code_id', $value2->code_id)->update($update_code);
						}

						$update_inventory['inventory_quantity'] = $inventory->inventory_quantity - $value->quantity;
						$update_inventory['inventory_sold']		= $inventory->inventory_sold + $value->quantity;
						DB::table('tbl_inventory')->where('inventory_id', $inventory->inventory_id)->where('inventory_item_id', $inventory->inventory_item_id)->update($update_inventory);

						$update_stockist_inventory['inventory_quantity'] = $new_inventory->inventory_quantity + $value->quantity;
						DB::table('tbl_inventory')->where('inventory_id', $new_inventory->inventory_id)->where('inventory_item_id', $new_inventory->inventory_item_id)->update($update_stockist_inventory);


					}
					else
					{
						foreach($code as $key2 => $value2)
						{
							$update_codes['code_sold']		= 1;
							$update_codes['org_code_sold_to']	= $customer->slot_owner;
							$update_codes['code_sold_to']	= $customer->slot_owner;
							$update_codes['code_date_sold']	= Carbon::now();

							$check_if_auto_distribute = DB::table('tbl_mlm_feature')->where('mlm_feature_name', 'auto_distribute')->first();
							$code_check = Tbl_codes::Inventory()->InventoryItem()->where('code_id', $value2->code_id)->first();
							if($customer->auto_activate_product_code && $code_check["item_type"] == "product") {
								$data["pin"]            = $value2->code_pin;
								$data["code"]           = $value2->code_activation;
								$data["slot_id"]        = $customer->slot_id;
								$data["code_id"]        = $value2->code_id;
								$data["slot_owner"]     = $customer->slot_owner;
								Product::activate_code($data);
							}
							else if($check_if_auto_distribute->mlm_feature_enable == 0)
							{
								$inventory_of_codes = Tbl_inventory::where('inventory_id', $value2->code_inventory_id)->first();
								$inventory_item = Tbl_item::where('item_id', $inventory_of_codes->inventory_item_id)->first();
								if($inventory_item->item_type == 'product')
								{
									$update_codes_used['code_used_by']		= $customer->slot_owner;
									$update_codes_used['code_date_used']	= Carbon::now();
									$update_codes_used['code_used']		    = 1;

									DB::table('tbl_codes')->where('code_id', $value2->code_id)->update($update_codes_used);
								}
							}
							if($dragonpay_status == 1)
							{
								$update_codes['archived']		 			= 1;
								$update_codes['dragonpay']		 			= 1;
								$update_codes['order_id']		 			= $dgp_transid;
							}
							DB::table('tbl_codes')->where('code_id', $value2->code_id)->update($update_codes);
						}

						$update_inventory['inventory_quantity'] = $inventory->inventory_quantity - $value->quantity;
						$update_inventory['inventory_sold']		= $inventory->inventory_sold + $value->quantity;

						DB::table('tbl_inventory')->where('inventory_id', $inventory->inventory_id)->where('inventory_item_id', $inventory->inventory_item_id)->update($update_inventory);
					}
					$return["status"]         = "Success";
					$return["status_code"]    = 200;
				}
			}
		}

		return $return;
	}

	//picked up = 1 means kinuha na yung item
	public static function create_receipt($order_id, $from, $picked_up = 1, $voucher_deduct = 0)
	{
		$order = Tbl_orders::where('order_id', $order_id)->first();

		if($picked_up == 0 || $order->delivery_method == 'pickup')
		{
			do
			{
				$claim_code = implode("-", str_split(strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8)), 4));
				$check_claim_code = DB::table('tbl_receipt')->where('claim_code', $claim_code)->get();
			}
			while (count($check_claim_code) != 0);
			$claimed = 0;
		}
		else
		{
			$claim_code = "none";
			$claimed 	= 1;
		}


		$insert_receipt['items'] 						= $order->items;
		$insert_receipt['delivery_method'] 				= $order->delivery_method;
		$insert_receipt['delivery_charge'] 				= $order->delivery_charge;
		$insert_receipt['subtotal'] 					= $order->subtotal;
		$insert_receipt['voucher'] 						= $voucher_deduct;
		$insert_receipt['buyer_name'] 					= $order->buyer_name;
		$insert_receipt['buyer_address'] 				= $order->buyer_address;
		$insert_receipt['buyer_slot_code'] 				= $order->buyer_slot_code;
		$insert_receipt['buyer_slot_id'] 				= $order->buyer_slot_id;
		$insert_receipt['receipt_date_created'] 		= Carbon::now();
		$insert_receipt['discount'] 					= $order->discount;
		$insert_receipt['grand_total'] 					= $order->grand_total + $order->delivery_charge - $voucher_deduct;
		$insert_receipt['claim_code'] 					= $claim_code;
		$insert_receipt['claimed'] 						= $claimed;
		$insert_receipt['retailer']						= $order->retailer;
		$insert_receipt['receipt_order_id'] 			= $order->order_id;
		$insert_receipt['change']						= $order->change;
		$insert_receipt['manager_discount']				= $order->manager_discount;
		$insert_receipt['tax_amount']					= round($order->tax_amount, 2);
		$insert_receipt['remarks']						= $order->remarks;
		$insert_receipt['payment_method']				= $order->payment_method;

		$receipt_id = Tbl_receipt::insertGetId($insert_receipt);

		$x = 0;
		$dd = json_decode($order->items);
		foreach($dd as $key3 => $value3)
		{
			if(isset($value3->item_id))
			{
				$insert['rel_receipt_id'] = $receipt_id;
				$insert['item_id'] = $value3->item_id;
				$insert['quantity'] = $value3->quantity;
				$item_price = Tbl_item::where("item_id", $value3->item_id)->first()->item_price;
				$insert['price'] = $item_price;
				$insert['subtotal'] = ($item_price * $value3->quantity);
				DB::table('tbl_receipt_rel_item')->insert($insert);
			}
			else
			{
				if($key3 == 'item_id')
				{
					$insert2['rel_receipt_id'] = $receipt_id;
					$insert2['item_id'] = $value3;

				}
				if($key3 == 'quantity')
				{
					$insert2['quantity'] = $value3;
				}

				$x = 1;
			}
		}

		if($x > 0)
		{
			DB::table('tbl_receipt_rel_item')->insert($insert2);
			$x = 0;
		}


		return $receipt_id;
	}

	public static function  create_order($ordered_item, $vat, $buyer_slot_id, $cashier_user_id,$from, $delivery_method, $picked_up = 1, $change = 0, $manager_discount = 0, $remarks = null , $address = null, $cashier_method = 4,$payment_given = 0,$dragonpay_status = 0, $dragonpay_charged = 0, $voucher_deduct = 0,$shipping_fee_v2 = 0, $handling_fee = 0,$checkout_total = 0, $receiver_name = null, $receiver_email = null, $receiver_contact_number = null)
	{
		// dd($ordered_item, $vat, $buyer_slot_id, $cashier_user_id,$from, $delivery_method, $picked_up, $change, $manager_discount, $remarks, $address, $cashier_method,$payment_given );
		$subtotal = 0;
		if($delivery_method == "pickup"|| $delivery_method == "none")
		{
			$delivery_charge = Tbl_delivery_charge::where("method_name","=","Direct")->first() ? Tbl_delivery_charge::where("method_name","Direct")->first()->method_charge : 0;
		}
		else
		{
			$delivery_charge = Tbl_delivery_charge::where("method_name","=","Indirect")->first() ? Tbl_delivery_charge::where("method_name","Indirect")->first()->method_charge : 0;
		}
		
		if($from == 'cashier')
		{
			$check_cashier = Tbl_cashier::where('cashier_user_id', $cashier_user_id)->first();
			$retailer = $check_cashier->cashier_branch_id;
			$cashier = $check_cashier->cashier_id;
			if($picked_up == 1)
			{
				$order_status = 'claimed';
			}
			else
			{
				$order_status = 'pickup';
			}

			$payment_method = DB::table('tbl_cashier_payment_method')->where('cashier_payment_method_id', $cashier_method )->first();
			
		}
		elseif($from == 'ecommerce')
		{
			$retailer = $cashier_user_id;
			$cashier = null;
			if($delivery_method == 'pickup')
			{
				$order_status = 'pickup';
			}
			else
			{
				$order_status = 'pending';
			}

			$payment_method = DB::table('tbl_cashier_payment_method')->where('cashier_payment_method_id', $cashier_method )->first();
		}
		elseif($from == "stockist")
		{
			$check_stockist = DB::table('tbl_stockist')->where('stockist_user_id',$cashier_user_id)->first();
			$retailer = $check_stockist->stockist_branch_id;
			$cashier =  $check_stockist->stockist_id;
			$payment_method = DB::table('tbl_cashier_payment_method')->where('cashier_payment_method_id', $cashier_method )->first();
			if($picked_up == 1)
			{
				$order_status = 'completed';
			}
			else
			{
				$order_status = 'pickup';
			}

		}

		if($payment_method->cashier_payment_method_name == 'GC')
		{
			$currency_id = Tbl_currency::where('currency_id', 4)->first();
		}
		else
		{
			$currency_id = Tbl_currency::where('currency_buying', 1)->first();
		}

		$buyer = Tbl_slot::where('slot_id', $buyer_slot_id)->Owner()->first();

		$orders = json_decode($ordered_item);

		foreach($orders as $key => $value)
		{
			// dd($value['quantity']);
			$item[$key] 									= Tbl_item::where('item_id', $value->item_id)->first();
			$item['discount'][$key] 						= Self::get_customer_discount($buyer->slot_id,$value->item_id);
			$item['discount'][$key]['original_price']		= $item[$key]['item_price'];


			if($payment_method->cashier_payment_method_name == "GC")
			{
				$item_price = $item[$key]['item_gc_price'] * $value->quantity;
				$discount = 'none';
			}
			else
			{
				if($item['discount'][$key]['percentage'] == 0)
				{
				
						$item_price = $item[$key]['item_price'] * $value->quantity;
						$discount = 'none';
				}
				else
				{
					// $discount_to_deduct 	=	$item[$key]['item_price'] * ($item['discount'][$key]['percentage']/100);
					$discount_to_deduct 	= $item['discount'][$key]['percentage']; //MAKE IT FIX
					$item_price  			= ($item[$key]['item_price'] - $discount_to_deduct) * $value->quantity;

				}
			}
			
			if($delivery_method == 'delivery')
			{
				if($value->quantity > 1)
				{
					$charges  = $item[$key]['item_charged'] + (($item[$key]['item_charged']/100 * $item[$key]['qty_charged']) * ($value->quantity - 1));
					$subtotal = $subtotal + $item_price + $charges;
			
				}
				else
				{
					$subtotal = $subtotal + $item_price + $item[$key]['item_charged'] ;
				}
			}
			else
			{
				$subtotal = $subtotal + $item_price;
			}
			$get_plan_status		 = Tbl_mlm_plan::where('mlm_plan_code','PRODUCT_DOWNLINE_DISCOUNT')->first()->mlm_plan_enable;
			if($get_plan_status == 1)
			{
				$value->product_downline_discount = $value->product_downline_discount ?? 0;
				$subtotal = $subtotal - $value->product_downline_discount;
			}
		}
		if($delivery_method == 'delivery') {
			if($subtotal > 998) {
				$delivery_charge = $delivery_charge + 50;
			}
		}
		$discount = $item['discount'];
		$manager_discount_amount = 0;
		if($manager_discount > 0)
		{
			$manager_discount_amount = ($subtotal * ($manager_discount/100));
		}

		$vat_amount = 0;
		if($vat == 1) {
			$vat_amount = ($subtotal - $manager_discount_amount) * 0.12;
			$checkout_total = $checkout_total + $vat_amount;
		} else if ($vat == 2) {
			$vat_amount = ($subtotal - $manager_discount_amount) - (($subtotal - $manager_discount_amount) / 1.12);
		}
		if($dragonpay_status == 0)
		{
			$inventory_codes = Self::inventory_codes_ordered($ordered_item, $retailer, $buyer->slot_id);
		}
		else {
			$inventory_codes['status_code'] = 200;
		}
		if($inventory_codes['status_code'] < 400)
		{
			$grand_total = $subtotal + $delivery_charge + ($vat == 1 ? $vat_amount : 0) + ($dragonpay_charged - ($manager_discount_amount - $voucher_deduct))+ $shipping_fee_v2 + $handling_fee;
			
			if($grand_total == $checkout_total)
			{
				$insert['items']				= $ordered_item;
				$insert['delivery_method']		= $delivery_method;
				$insert['delivery_charge']		= $delivery_charge;
				$insert['subtotal']				= $subtotal;
				$insert['voucher']				= $voucher_deduct;
				$insert['buyer_name']			= $buyer->name;
				$insert['buyer_address']		= $address;
				$insert['buyer_slot_code']		= $buyer->slot_no;
				$insert['buyer_slot_id']		= $buyer->slot_id;
				$insert['order_date_created']	= Carbon::now();
				$insert['dragonpay_charged']	= $dragonpay_charged;
				$insert['change']				= $change;
				$insert['discount']				= $payment_method->cashier_payment_method_name == "GC" ? "None, GC payment" : json_encode($discount);
				$insert['grand_total']			= $grand_total;
				$insert['retailer']				= $retailer;
				$insert['order_from']			= $from;
				$insert['cashier_id']			= $cashier;
				$insert['order_status']			= $order_status;
				$insert['manager_discount']		= $manager_discount_amount;
				$insert['tax_amount'] 			= $vat_amount;
				$insert['remarks']				= $remarks;
				$insert['payment_method']		= $payment_method->cashier_payment_method_id;
				$insert['payment_tendered']		= $payment_given;
				$insert['shipping_fee_v2']		= $shipping_fee_v2;
				$insert['handling_fee']			= $handling_fee;
				$insert['receiver_name']		= $receiver_name;
				$insert['receiver_contact']		= $receiver_contact_number;
				$insert['receiver_email']		= $receiver_email;
			
				if($from != 'cashier' && $from != 'stockist')
				{
					if($dragonpay_status == 0)
					{
						if($payment_method->cashier_payment_method_name == 'Wallet')
						{
							Log::insert_wallet($buyer->slot_id, $grand_total * -1, $from, $currency_id->currency_id);
							
							if($voucher_deduct > 0)
							{
								Log::insert_wallet($buyer->slot_id, $voucher_deduct * -1, $from, 13);
							}
						}
						elseif($payment_method->cashier_payment_method_name == 'COD')
						{
							$insert_log['wallet_log_slot_id']				= $buyer->slot_id;						
							$insert_log['wallet_log_amount']				= $grand_total * -1;						
							$insert_log['wallet_log_details']				= "Shop/Purchased (COD)";						
							$insert_log['wallet_log_type']					= "CREDIT";					
							$insert_log['wallet_log_running_balance']		= 0;								
							$insert_log['wallet_log_date_created']			= Carbon::now();							
							$insert_log['currency_id']						= 15;	
						
							Tbl_wallet_log::insert($insert_log);
							
							if($voucher_deduct > 0)
							{
								Log::insert_wallet($buyer->slot_id, $voucher_deduct * -1, $from, 13);
							}
						}
						if($payment_method->cashier_payment_method_name == 'GC')
						{
							Log::insert_wallet($buyer->slot_id, $grand_total * -1, $from, $currency_id->currency_id);
						}
					}
					elseif($dragonpay_status == 1)
					{
						$insert_log['wallet_log_slot_id']				= $buyer->slot_id;						
						$insert_log['wallet_log_amount']				= $grand_total * -1;						
						$insert_log['wallet_log_details']				= "Ecommerce (DRAGONPAY)";						
						$insert_log['wallet_log_type']					= "CREDIT";					
						$insert_log['wallet_log_running_balance']		= 0;								
						$insert_log['wallet_log_date_created']			= Carbon::now();							
						$insert_log['currency_id']						= 12;	
						
						Tbl_wallet_log::insert($insert_log);
					}
				}
			
				$order_id = Tbl_orders::insertGetId($insert);
				
				$update['transaction_id']	= Cashier::generate_transaction_id($order_id);
				Tbl_orders::where("order_id", $order_id)->update($update);

				$x = 0;
				$dd = json_decode($ordered_item);
				foreach($dd as $key3 => $value3)
				{
					if(isset($value3->item_id))
					{
						$insert_order['rel_order_id'] 				= $order_id;
						$insert_order['item_id'] 					= $value3->item_id;
						$insert_order['quantity'] 					= $value3->quantity;
						DB::table('tbl_orders_rel_item')->insert($insert_order);
					}
					else
					{
						if($key3 == 'item_id')
						{
							$insert_order2['rel_order_id'] = $order_id;
							$insert_order2['item_id'] = $value3;
	
						}
						if($key3 == 'quantity')
						{
							$insert_order2['quantity'] = $value3;
						}
						$x = 1;
					}
				}
				if($x > 0)
				{
					DB::table('tbl_orders_rel_item')->insert($insert_order2);
					$x = 0;
				}
				$receipt_id = Self::create_receipt($order_id, $from, $picked_up, $voucher_deduct);
	
				if(is_numeric($receipt_id))
				{
	
					if($payment_method->cashier_payment_method_name != 'COD')
					{
						$check_if_auto_distribute = DB::table('tbl_mlm_feature')->where('mlm_feature_name', 'auto_distribute')->first();
						if($check_if_auto_distribute->mlm_feature_enable == 0 && $buyer->membership_inactive ==  0)
						{
							$item_for_pv = json_decode($ordered_item);
							foreach($item_for_pv as $key => $value)
							{
								for($x = 0; $x < $value->quantity; $x++)
								{
									MLM::purchase($buyer->slot_id,$value->item_id);
								}
							}
							//update codes as used
	
						}
						MLM::purchase_item($ordered_item, $buyer_slot_id,$subtotal);
					}
					else
					{
						$insert_cod['slot_id']					= $buyer_slot_id;
						$insert_cod['order_id']					= $order_id;
						$insert_cod['ordered_item']				= $ordered_item;
						$insert_cod['subtotal']					= $subtotal;
						$insert_cod['date_ordered']			    = Carbon::now();
						Tbl_cod_list::insert($insert_cod);
					}
					
					$return["status"]         		  = "success";
					$return["status_code"]    		  = 200;
					$return["status_message"] 		  = "Ordered Successfully!";
					$return["receipt"]				  = Tbl_receipt::where('receipt_id', $receipt_id)->first();
					// dd($return);
					return $return;
	
				}
			}
			else
			{
				$return["status"]         = "Error";
				$return["status_code"]    = 400;
				$return["status_message"] = "Prices of items might change overtime. Please reload the page and try to checkout again!";

				return $return;
			}
		}
		else
		{
			return $inventory_codes;
		}
	}
	public static function get_claim_codes($data)
	{
		$cashier 	= Tbl_cashier::where('cashier_user_id', Request::user()->id)->first();


		$claim_codes 	= Tbl_receipt::where('retailer', $cashier->cashier_branch_id)->where('claim_code', '!=', 'none');

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

	public static function select_claim_code($receipt_id, $claim_code = null)
	{
		if(isset($claim_code))
		{
			$check_receipt 	= Tbl_receipt::where('receipt_id', $receipt_id)->where('claimed', 0)->first();

			if($check_receipt)
			{
				$update['claimed'] = 1;

				Tbl_receipt::where('receipt_id', $receipt_id)->update($update);

				$update2['order_status'] = "claimed";
				$update2['date_status_changed'] = Carbon::now();
				DB::table('tbl_orders')->where('order_id', $check_receipt->receipt_order_id)->update($update2);

				$return["status"]         = "success";
				$return["status_code"]    = 200;
				$return["status_message"] = "Used Successfully!";
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
	public static function ecom_checkout_v2($data)
	{		
		$get_user 														= Request::user();
		$subtotal														= 0;

		//direct = pickup, indirect = delivery
		if($data['info']['method'] == 'Direct')
		{
			$rules['info']["branch_id"] 										= "required";

			$validator = Validator::make($data, $rules);

			if ($validator->fails())
			{
				$return["status"]         								= "error";
				$return["status_code"]    								= 400;
				$return["status_message"] 								= "Pick up location is required.";

				return $return;
			}
			else
			{

				$delivery_charge 										= $data['info']["method_charge"];
				$delivery_method 										= 'pickup';
				$address         										= $data['info']["address"];
				$order_status 											= 'processed';
				$retailer  	  											= $data['info']['branch_id'];
				//if true, slot ng bumibili yung nakalogin
			}
		}
		if($data['info']['method'] == 'Indirect')
		{
			
			$delivery_charge 											= $data['info']["method_charge"];
			$delivery_method 											= 'delivery';
			$order_status 												= 'pending';
			$address         											= $data['info']["address"];
			//retailer is yung main branch
			$retailer 	  												= 1;
		}
		if($data['info']['slot']['slot_owner'] == $get_user->id)
		{
			$update_for_approval['user_status']							= 'already_purchased';
			$update_for_approval['date_purchased']						= Carbon::now();
			$update_for_approval['shop_status']							= 1;

			Tbl_orders_for_approval::where('id',$data['info']['id'])->update($update_for_approval);

			foreach($data['item_list'] as $key => $value)
			{
				$items[$key]['item_id'] 								= $value['item_id'];
				$items[$key]['quantity'] 								= $value['quantity'];

				$get_item[$key] 										= Tbl_item::where('item_id', $value['item_id'])->first();
				$check_item_kit[$key]['type'] 	  						= $get_item[$key]->item_type;
				$check_item_kit[$key]['item'] 	  						= $get_item[$key]->item_id;
				$check_item_kit[$key]['quantity'] 						= $value['quantity'];
				$items[$key]['discounted_price']  						= $value['discounted_price'];
				$items[$key]['shipping_fee']  							= $value['shipping_fee'];
				$items[$key]['total_per_item']  						= $value['total_per_item'];
				$items[$key]['product_downline_discount']  				= $value['product_downline_discount'] ?? 0;

				// $subtotal							 				= $subtotal + ($get_item[$key]->discounted_price * $value['item_qty']);
			}
			$currency_id												= Tbl_currency::where('currency_buying',1)->pluck('currency_id')->first();
			$user 														= DB::table('tbl_slot')->where('tbl_slot.slot_id', $data['info']['slot']['slot_id'])->join('users', 'tbl_slot.slot_owner', '=', 'users.id')->join('tbl_wallet', 'tbl_slot.slot_id', '=', 'tbl_wallet.slot_id')->where('currency_id', $currency_id)->first();
			// $grand_total 											= $subtotal + $delivery_charge;

			if($data['info']['default_voucher_status'] == 1)
			{
				$datap['info']['voucher_deduct']										= $data['info']['sum'] >= $data['info']['min_spend'] ? $data['info']['voucher_deduct'] : 0;
			}
			else
			{
				$data['info']['voucher_deduct']										= 0;
			}
		
			if($data['info']['payment_method'] == 'Wallet')
			{
				if($user->wallet_amount < $data['info']['grandtotal'])
				{
					$return["status"]         							= "Error";
					$return["status_code"]    							= 400;
					$return["status_message"] 							= "Not enough wallet.";
					return $return;	
				}	
				else	
				{	
					$grand_total										= $data['info']['grandtotal'];
					$payment 											= ["method" => "wallet", "amount" => $grand_total];
					$ordered_item										= json_encode($items);
					$vat												= 0;
					$buyer_slot_id										= $user->slot_id;
					$payment_given										= json_encode($payment);
					$cashier_user_id 									= $retailer;
	
					$return = Self::create_order_v2($ordered_item, $vat, $buyer_slot_id, $cashier_user_id,'ecommerce', $delivery_method, null, 0, 0, null,$address,4,0,0,0,$data['info']['voucher_deduct'],$data['info']['courier'],$data['info']['shipping_fee'], $data['info']['other_discount'],$data['info']['transaction_number']);
					return $return;
				}
			}
			elseif($data['info']['payment_method'] == 'Dragonpay')
			{
				$subtotal 											    = $data['info']['sum'];
				$payment 												= ["method" => "dragonpay", "amount" => $subtotal];
				$ordered_item											= json_encode($items);
				$vat													= 0;
				$buyer_slot_id											= $user->slot_id;
				$payment_given											= json_encode($payment);
				$cashier_user_id 										= $retailer;
				$txnid													= "TRANS".time();

				$convenience_fee										= Tbl_dragonpay_settings::first()->service_charged ?? 0;
				$_total													= $data['info']['total_item_price'] + $data['info']['shipping_fee'] + $convenience_fee;
				$grand_total											= ($_total - $data['info']['voucher_deduct']) - $data['info']['other_discount'];

				$insert['ordered_item']									= $ordered_item;										
				$insert['vat']											= $vat;								
				$insert['buyer_slot_id']								= $buyer_slot_id;											
				$insert['cashier_user_id']								= $cashier_user_id;											
				$insert['from']											= 'ecommerce';								
				$insert['delivery_method']								= $delivery_method;											
				$insert['picked_up']									= null;										
				$insert['change']										= 0;									
				$insert['manager_discount']								= 0;											
				$insert['remarks']										= null;									
				$insert['address']										= $address;									
				$insert['cashier_method']								= 5;											
				$insert['dragonpay_charged']							= $convenience_fee;									
				$insert['payment_given']								= $payment_given;											
				$insert['status']										= 'Pending';
				$insert['subtotal']										= $data['info']['sum'];
				$insert['voucher']										= $data['info']['voucher_deduct'];
				$insert['grandtotal']									= $grand_total;
				$insert['dragonpay_txnid']								= $txnid;
				$insert['created_at']									= Carbon::now();
				$insert['courier']										= $data['info']['courier'];
				$insert['shipping_fee']									= $data['info']['shipping_fee'];
				$insert['other_discount']								= $data['info']['other_discount'];
				$insert['for_approval_trans_no']						= $data['info']['transaction_number'];

				// dd($insert);
				// $inventory_codes 							= Self::inventory_codes_ordered($ordered_item, $retailer, $buyer_slot_id, 1, $dragonpaytrans_id);
				$product_summary 										= "";
				
				foreach ($data['item_list'] as $key => $value) 
				{
					$get_inventory_quantity                 			= Tbl_inventory::where('inventory_item_id',$value['item_id'])->pluck('inventory_quantity')->first();
					$product_summary 									.=$value["item_sku"] . $value["item_id"] . "(x" . $value["quantity"] . ")-" ."PHP". number_format($value["discounted_price"]) . ",";
				
					// dd($)
					if($value['quantity'] > $get_inventory_quantity)
					{
						$return["status"]         						= "Error";
						$return["status_code"]    						= 400;
						$return["status_message"] 						= $value["item_sku"]." does not have enough inventory.";

						return $return;			
					}			
					else 			
					{			
						$inventory_codes['status_code'] 				= 200;
					}					
				}
				if($data['info']['voucher_deduct'] > 0)
				{
					Log::insert_wallet($buyer_slot_id, $data['info']['voucher_deduct'] * -1, 'ecommerce', 13);
				}
				if($inventory_codes['status_code'] < 400)
				{
					// $product_summary									= $product_summary, "(+) Shipping Fee ".$data['info']['shipping_fee'] 
					$dragonpaytrans_id 									= Tbl_dragonpay_transaction::insertGetId($insert);
					$return 											= Dragonpay::create_transaction($dragonpaytrans_id, $buyer_slot_id,$product_summary,$grand_total,$data['info']['email_address'],$txnid);

					return $return;	
				}
			}	
			else
			{
				$grand_total										= $data['info']['grandtotal'];
				$payment 											= ["method" => "cod", "amount" => $grand_total];
				$ordered_item										= json_encode($items);
				$vat												= 0;
				$buyer_slot_id										= $user->slot_id;
				$payment_given										= json_encode($payment);
				$cashier_user_id 									= $retailer;

				$return = Self::create_order_v2($ordered_item, $vat, $buyer_slot_id, $cashier_user_id,'ecommerce', $delivery_method, null, 0, 0, null,$address,6,0,0,0,$data['info']['voucher_deduct'],$data['info']['courier'],$data['info']['shipping_fee'], $data['info']['other_discount'],$data['info']['transaction_number']);
				return $return;
			}	
		}
		else
		{									
			$return["status"]         									= "Error";
			$return["status_code"]    									= 400;
			$return["status_message"] 									= "This ain't yours, is it?";

			return $return;
		}
	}
	public static function create_order_v2($ordered_item, $vat, $buyer_slot_id, $cashier_user_id,$from, $delivery_method, $picked_up = 1, $change = 0, $manager_discount = 0, $remarks = null , $address = null, $cashier_method = 4,$payment_given = 0,$dragonpay_status = 0, $dragonpay_charged = 0, $voucher_deduct = 0,$courier = null, $shipping_fee = 0,$other_discount = 0,$for_approval_trans_no = null)
	{
		// dd($ordered_item, $vat, $buyer_slot_id, $cashier_user_id,$from, $delivery_method, $picked_up, $change, $manager_discount, $remarks, $address, $cashier_method,$payment_given );
		$subtotal = 0;
		if($delivery_method == "pickup"|| $delivery_method == "none")
		{
			$delivery_charge = Tbl_delivery_charge::where("method_name","=","Direct")->first() ? Tbl_delivery_charge::where("method_name","Direct")->first()->method_charge : 0;
		}
		else
		{
			$delivery_charge = Tbl_delivery_charge::where("method_name","=","Indirect")->first() ? Tbl_delivery_charge::where("method_name","Indirect")->first()->method_charge : 0;
		}
		
		if($from == 'cashier')
		{
			$check_cashier 	= Tbl_cashier::where('cashier_user_id', $cashier_user_id)->first();
			$retailer 		= $check_cashier->cashier_branch_id;
			$cashier 		= $check_cashier->cashier_id;
			if($picked_up == 1)
			{
				$order_status = 'completed';
			}
			else
			{
				$order_status = 'pickup';
			}

			$payment_method = DB::table('tbl_cashier_payment_method')->where('cashier_payment_method_id', $cashier_method )->first();
			
		}
		elseif($from == 'ecommerce')
		{
			$retailer = $cashier_user_id;
			$cashier = null;
			if($delivery_method == 'pickup')
			{
				$order_status = 'pickup';
			}
			else
			{
				$order_status = 'pending';
			}

			$payment_method = DB::table('tbl_cashier_payment_method')->where('cashier_payment_method_id', $cashier_method )->first();

		}
		elseif($from == "stockist")
		{
			$check_stockist = DB::table('tbl_stockist')->where('stockist_user_id',$cashier_user_id)->first();
			$retailer = $check_stockist->stockist_branch_id;
			$cashier =  $check_stockist->stockist_id;
			$payment_method = DB::table('tbl_cashier_payment_method')->where('cashier_payment_method_id', $cashier_method )->first();
			if($picked_up == 1)
			{
				$order_status = 'completed';
			}
			else
			{
				$order_status = 'pickup';
			}

		}

		$currency_id = Tbl_currency::where('currency_buying', 1)->first();

		$buyer = Tbl_slot::where('slot_id', $buyer_slot_id)->Owner()->first();

		$orders = json_decode($ordered_item);

		foreach($orders as $key => $value)
		{
			// dd($value);
			$item[$key] 								= Tbl_item::where('item_id', $value->item_id)->first();
			$item['discount'][$key] 					= Self::get_customer_discount($buyer->slot_id,$value->item_id);
			$item['discount'][$key]['original_price']	= $item[$key]['item_price'];


			if($payment_method->cashier_payment_method_name == "GC")
			{
				$item_price = $item[$key]['item_gc_price'] * $value->quantity;
				$discount = 'none';
			}
			else
			{
				if($item['discount'][$key]['percentage'] == 0)
				{
				
						$item_price = $item[$key]['item_price'] * $value->quantity;
						$discount = 'none';
				}
				else
				{
					// $discount_to_deduct 	=	$item[$key]['item_price'] * ($item['discount'][$key]['percentage']/100);
					$discount_to_deduct 	=	$item['discount'][$key]['percentage']; //Make it fix value
					$item_price  			= ($item[$key]['item_price'] - $discount_to_deduct) * $value->quantity;

				}
			}
			
			if($delivery_method == 'delivery')
			{
				if($value->quantity > 1)
				{
					$charges  = $item[$key]['item_charged'] + (($item[$key]['item_charged']/100 * $item[$key]['qty_charged']) * ($value->quantity - 1));
					$subtotal = $subtotal + $item_price + $charges;
			
				}
				else
				{
					$subtotal = $subtotal + $item_price + $item[$key]['item_charged'] ;
				}
			}
			else
			{
				$subtotal = $subtotal + $item_price;
			}
			$get_plan_status		 = Tbl_mlm_plan::where('mlm_plan_code','PRODUCT_DOWNLINE_DISCOUNT')->first()->mlm_plan_enable;
			if($get_plan_status == 1)
			{
				$subtotal = $subtotal - $value->product_downline_discount;
			}
		}
		
		$discount = $item['discount'];
		$manager_discount_amount = 0;
		if($manager_discount > 0)
		{
			$manager_discount_amount = ($subtotal * ($manager_discount/100));
		}

		$vat_amount = 0;
		if($vat == 1)
		{
			$vat_amount = ($subtotal - $manager_discount_amount) * 0.12;
		}
		if($dragonpay_status == 0)
		{
			$inventory_codes = Self::inventory_codes_ordered($ordered_item, $retailer, $buyer->slot_id);
		}
		else {
			$inventory_codes['status_code'] = 200;
		}
		if($inventory_codes['status_code'] < 400)
		{
			$_total									= $subtotal + $shipping_fee + $dragonpay_charged + $vat_amount + $delivery_charge - $manager_discount_amount;
			$total									= ($_total - $voucher_deduct) - $other_discount;
			$grand_total 							= $total;
			$insert['items']						= $ordered_item;
			$insert['delivery_method']				= $delivery_method;
			$insert['delivery_charge']				= $delivery_charge;
			$insert['subtotal']						= $subtotal;
			$insert['voucher']						= $voucher_deduct;
			$insert['buyer_name']					= $buyer->name;
			$insert['buyer_address']				= $address;
			$insert['buyer_slot_code']				= $buyer->slot_no;
			$insert['buyer_slot_id']				= $buyer->slot_id;
			$insert['order_date_created']			= Carbon::now();
			$insert['dragonpay_charged']			= $dragonpay_charged;
			$insert['change']						= $change;
			$insert['discount']						= $payment_method->cashier_payment_method_name == "GC" ? "None, GC payment" : json_encode($discount);
			$insert['grand_total']					= $grand_total;
			$insert['retailer']						= $retailer;
			$insert['order_from']					= $from;
			$insert['cashier_id']					= $cashier;
			$insert['order_status']					= $order_status;
			$insert['manager_discount']				= $manager_discount_amount;
			$insert['tax_amount'] 					= $vat_amount;
			$insert['remarks']						= $remarks;
			$insert['payment_method']				= $payment_method->cashier_payment_method_id;
			$insert['payment_tendered']				= $payment_given;
			$insert['courier']						= $courier;
			$insert['shipping_fee']					= $shipping_fee;
			$insert['other_discount']				= $other_discount;
			$insert['for_approval_trans_no']		= $for_approval_trans_no;
		
			$order_id = Tbl_orders::insertGetId($insert);
			
			$update['transaction_id']	= Cashier::generate_transaction_id($order_id);
			Tbl_orders::where("order_id", $order_id)->update($update);

			if($from != 'cashier' && $from != 'stockist')
			{
				if($dragonpay_status == 0)
				{
					if($payment_method->cashier_payment_method_name == 'Wallet')
					{
						Log::insert_wallet($buyer->slot_id, $grand_total * -1, $from, $currency_id->currency_id);
						
						if($voucher_deduct > 0)
						{
							Log::insert_wallet($buyer->slot_id, $voucher_deduct * -1, $from, 13);
						}
					}
					elseif($payment_method->cashier_payment_method_name == 'COD')
					{
						$insert_log['wallet_log_slot_id']				= $buyer->slot_id;						
						$insert_log['wallet_log_amount']				= $grand_total * -1;						
						$insert_log['wallet_log_details']				= "Shop/Purchased (COD)";						
						$insert_log['wallet_log_type']					= "CREDIT";					
						$insert_log['wallet_log_running_balance']		= 0;								
						$insert_log['wallet_log_date_created']			= Carbon::now();							
						$insert_log['currency_id']						= 14;	
					
					Tbl_wallet_log::insert($insert_log);
						
						if($voucher_deduct > 0)
						{
							Log::insert_wallet($buyer->slot_id, $voucher_deduct * -1, $from, 13);
						}
					}
				}
				elseif($dragonpay_status == 1)
				{
					$insert_log['wallet_log_slot_id']				= $buyer->slot_id;						
					$insert_log['wallet_log_amount']				= $grand_total * -1;						
					$insert_log['wallet_log_details']				= "Ecommerce (DRAGONPAY)";						
					$insert_log['wallet_log_type']					= "CREDIT";					
					$insert_log['wallet_log_running_balance']		= 0;								
					$insert_log['wallet_log_date_created']			= Carbon::now();							
					$insert_log['currency_id']						= 12;	
					
					Tbl_wallet_log::insert($insert_log);
				}
			}
		
			
			$x = 0;
			$dd = json_decode($ordered_item);
			foreach($dd as $key3 => $value3)
			{
				if(isset($value3->item_id))
				{
					$insert_order['rel_order_id'] 				= $order_id;
					$insert_order['item_id'] 					= $value3->item_id;
					$insert_order['quantity'] 					= $value3->quantity;
					DB::table('tbl_orders_rel_item')->insert($insert_order);
				}
				else
				{
					if($key3 == 'item_id')
					{
						$insert_order2['rel_order_id'] = $order_id;
						$insert_order2['item_id'] = $value3;

					}
					if($key3 == 'quantity')
					{
						$insert_order2['quantity'] = $value3;
					}
					$x = 1;
				}
			}
			if($x > 0)
			{
				DB::table('tbl_orders_rel_item')->insert($insert_order2);
				$x = 0;
			}

			$receipt_id = Self::create_receipt($order_id, $from, $picked_up, $voucher_deduct);

			if(is_numeric($receipt_id))
			{

				$check_if_auto_distribute = DB::table('tbl_mlm_feature')->where('mlm_feature_name', 'auto_distribute')->first();
				if($check_if_auto_distribute->mlm_feature_enable == 0 && $buyer->membership_inactive ==  0)
				{
					$item_for_pv = json_decode($ordered_item);
					foreach($item_for_pv as $key => $value)
					{
						for($x = 0; $x < $value->quantity; $x++)
						{
							MLM::purchase($buyer->slot_id,$value->item_id);
						}
					}
					//update codes as used

				}
				MLM::purchase_item($ordered_item, $buyer_slot_id,$subtotal);
				
				$return["status"]         		  = "success";
				$return["status_code"]    		  = 200;
				$return["status_message"] 		  = "Ordered Successfully!";
				$return["receipt"]				  = Tbl_receipt::where('receipt_id', $receipt_id)->first();
				// dd($return);
				return $return;

			}
		}
		else
		{
			return $inventory_codes;
		}
	}

	public static function dropshipping_checkout($data)
	{		
		$subtotal						= 0;
		$address         				= $data["address"] ?? null;
		$grand_total = 0;

		foreach($data['checkout_summary'] as $key => $value) {
			if($value['item_qty'] > 0)
			{
				$items[$key]['item_id'] 				= $value['item_id'];
				$items[$key]['quantity'] 				= $value['item_qty'];
				$items[$key]['subtotal'] 				= $value['subtotal'];
				$grand_total += $value['subtotal'];
				$get_item[$key] 						= Tbl_item::where('item_id', $value['item_id'])->first();

				$check_item_kit[$key]['type'] 	  		= $get_item[$key]->item_type;
				$check_item_kit[$key]['item'] 	  		= $get_item[$key]->item_id;
				$check_item_kit[$key]['quantity'] 		= $value['item_qty'];
			}
			else
			{
				$return["status"]         				= "Error";
				$return["status_code"]    				= 400;
				$return["status_message"] 				= "Invalid quantity";

				return $return;
			}
		}
		$grand_total = $grand_total + $data["shipping_fee"];
		if($data['payment_method']['method'] == 'Wallet') {
		}
		else if($data['payment_method']['method'] == 'COD')
		{
			$payment 											= ["method" => "cod", "amount" => $subtotal];
			$ordered_item										= json_encode($items);
			$vat												= 0;
			$buyer_slot_id										= $data['customer_info'];
			$return = Self::create_dropshipping_order($ordered_item, $vat, $buyer_slot_id, 1,'ecommerce', 'delivery', null, 0, 0, null,$address,6,0,0,0,0,$data["shipping_fee"],0, $grand_total);
			return $return;
		}
	}

	public static function create_dropshipping_order($ordered_item, $vat, $buyer_info, $cashier_user_id,$from, $delivery_method, $picked_up = 1, $change = 0, $manager_discount = 0, $remarks = null , $address = null, $cashier_method = 4,$payment_given = 0,$dragonpay_status = 0, $dragonpay_charged = 0, $voucher_deduct = 0,$shipping_fee = 0, $handling_fee = 0,$checkout_total = 0)
	{
		$subtotal = 0;
		
		if($from == 'ecommerce')
		{
			$retailer = $cashier_user_id;
			$cashier = null;
			$order_status = 'pending';
			$payment_method = DB::table('tbl_cashier_payment_method')->where('cashier_payment_method_id', $cashier_method )->first();
		}

		$buyer = $buyer_info;
		$orders = json_decode($ordered_item);

		foreach($orders as $key => $value)
		{
			$item[$key] = Tbl_item::where('item_id', $value->item_id)->first();
			$item['discount'][$key]['item_id'] = $item[$key]['item_id'];
			$item['discount'][$key]['type'] = 'none';
			$item['discount'][$key]['percentage'] = 0;
			$item['discount'][$key]['original_price'] = $item[$key]['item_price'];

			if($item['discount'][$key]['percentage'] == 0) {
				$item_price = $item[$key]['item_price'] * $value->quantity;
				$discount = 'none';
			}
			
			$subtotal = $subtotal + $item_price;
		}
		
		$discount = $item['discount'];
		{
			$grand_total = $subtotal + $shipping_fee;
			if($grand_total == $checkout_total)
			{
				$insert['items']				= $ordered_item;
				$insert['delivery_method']		= $delivery_method;
				$insert['delivery_charge']		= $shipping_fee;
				$insert['subtotal']				= $subtotal;
				$insert['voucher']				= $voucher_deduct;
				$insert['buyer_name']			= $buyer['first_name'] . ' ' . $buyer['last_name'];
				$insert['buyer_address']		= $address;
				$insert['buyer_contact_number']	= $buyer['contact'];
				$insert['buyer_email']			= $buyer['email'] ?? NULL;
				$insert['buyer_sponsor_id']		= $buyer['sponsor_id'] ?? NULL;
				$insert['buyer_slot_code']		= 'N/A';
				$insert['buyer_slot_id']		= null;
				$insert['order_date_created']	= Carbon::now();
				$insert['dragonpay_charged']	= $dragonpay_charged;
				$insert['change']				= $change;
				$insert['discount']				= json_encode($discount);
				$insert['grand_total']			= $grand_total;
				$insert['retailer']				= $retailer;
				$insert['order_from']			= $from;
				$insert['cashier_id']			= $cashier;
				$insert['order_status']			= $order_status;
				$insert['manager_discount']		= 0;
				$insert['tax_amount'] 			= 0;
				$insert['remarks']				= $remarks;
				$insert['payment_method']		= $payment_method->cashier_payment_method_id;
				$insert['payment_tendered']		= $payment_given;
				$insert['handling_fee']			= $handling_fee;
			
				$order_id = Tbl_orders::insertGetId($insert);
				
				$update['transaction_id'] = Cashier::generate_transaction_id($order_id);
				Tbl_orders::where("order_id", $order_id)->update($update);

				$x = 0;
				$order_item = json_decode($ordered_item);
				foreach($order_item as $order)
				{
					if(isset($order->item_id))
					{
						$insert_order['rel_order_id'] = $order_id;
						$insert_order['item_id'] = $order->item_id;
						$insert_order['quantity'] = $order->quantity;
						DB::table('tbl_orders_rel_item')->insert($insert_order);
					}
				}
				$receipt_id = Self::create_receipt($order_id, $from, $picked_up, $voucher_deduct);
	
				if(is_numeric($receipt_id))
				{
					$insert_dropshipping['order_id'] = $order_id;
					$insert_dropshipping['ordered_item'] = $ordered_item;
					$insert_dropshipping['subtotal'] = $subtotal;
					$insert_dropshipping['shipping_fee'] = $shipping_fee;
					$insert_dropshipping['grand_total'] = $grand_total;
					$insert_dropshipping['date_ordered'] = Carbon::now();

					Tbl_dropshipping_list::insert($insert_dropshipping);

					$return["status"] = "success";
					$return["status_code"] = 200;
					$return["status_message"] = "Ordered Successfully!";
					$return["receipt"] = Tbl_receipt::where('receipt_id', $receipt_id)->first();
					return $return;
				}
			}
			else
			{
				$return["status"] = "Error";
				$return["status_code"] = 400;
				$return["status_message"] = "Prices of items might change overtime. Please reload the page and try to checkout again!";

				return $return;
			}
		}
	}

	public static function generate_transaction_id($order_id){
		$padded_id = str_pad((int)$order_id, 3, '0', STR_PAD_LEFT);
    	return 'TXN' . date('Ymd') . $padded_id;
	}
}
