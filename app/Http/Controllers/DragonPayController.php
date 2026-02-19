<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use header;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Redirect;
use Carbon\Carbon;
use App\Models\Tbl_audit_trail;
use App\Models\Tbl_dragonpay_transaction;
use App\Globals\Cashier;
use App\Models\Tbl_inventory;
use App\Models\Tbl_codes;
use App\Models\Tbl_item;
use App\Globals\Log;
use App\Globals\Audit_trail;

class DragonPayController extends Controller
{
    public function getdigest($refresh = false)
    {
      $transaction_id                                   = 8;
      $status                                           = 'S';
      // $update['dragonpay_refno']                        = Request::input('refno');      
      // $update['dragonpay_status']                       = Request::input('status');      
      // $update['dragonpay_message']                      = Request::input('message'); 
      
      // if(Request::input('status') == 'S' || Request::input('status') == 'F' || Request::input('status') == 'U')
      // {
      //   $update['date_accomplished']                     = Carbon::now(); 
      // }
  
      // Tbl_dragonpay_transaction::where('id',$transaction_id)->orWhere('dragonpay_txnid',Request::input('txnid'))->update($update);
      $transaction_data                                 = Tbl_dragonpay_transaction::where('id',$transaction_id)->first();
      $items                                            = json_decode($transaction_data->ordered_item);
  
      if($status == 'S')
      {   
        $check_if_exists                                = Tbl_codes::where('order_id',$transaction_id)->count();

        // if($check_if_exists <= 0)
        // {
        //   Cashier::inventory_codes_ordered($transaction_data->ordered_item, $transaction_data->cashier_user_id, $transaction_data->buyer_slot_id, 1, $transaction_id);
        // }
  
        // $update_code['code_date_sold']                  = Carbon::now();               
        // $update_code['archived']                        = 0;         
        // $check_if_auto_distribute                       = DB::table('tbl_mlm_feature')->where('mlm_feature_name', 'auto_distribute')->first();
  
        // foreach ($items as $key => $value) 
        // {
        //   if($check_if_auto_distribute->mlm_feature_enable == 0)
        //   {
        //     $inventory_item 			                      = Tbl_item::where('item_id',$value->item_id)->first();
        //     if($inventory_item->item_type == 'product')
        //     {
        //       $update_code['code_date_used']            = Carbon::now(); 
        //     }
        //   }
        // }
        // Tbl_codes::where('order_id',$transaction_id)->update($update_code);
        Cashier::create_order_v2($transaction_data->ordered_item,$transaction_data->vat,$transaction_data->buyer_slot_id,$transaction_data->cashier_user_id,$transaction_data->from,$transaction_data->delivery_method,$transaction_data->picked_up,$transaction_data->change,$transaction_data->manager_discount,$transaction_data->remarks,$transaction_data->address,$transaction_data->cashier_method,0,1,$transaction_data->dragonpay_charged,$transaction_data->voucher,$transaction_data->courier,$transaction_data->shipping_fee,$transaction_data->other_discount,$transaction_data->for_approval_trans_no);
      }
      // if($status == 'P')
      // {   
      //   Cashier::inventory_codes_ordered($transaction_data->ordered_item, $transaction_data->cashier_user_id, $transaction_data->buyer_slot_id, 1, $transaction_id);
      // }
      // elseif($status == 'F' || $status == 'U') 
      // {
      //   $update_code['code_used']                       = 0;         
      //   $update_code['code_sold']                       = 0;         
      //   $update_code['code_sold_to']                    = null;             
      //   $update_code['code_date_sold']                  = null;               
      //   $update_code['code_date_used']                  = null;               
      //   $update_code['code_used_by']                    = null;             
      //   $update_code['archived']                        = 0;         
      //   $update_code['dragonpay']                       = 0;         
      //   $update_code['order_id']                        = null;   
              
      //   Tbl_codes::where('order_id',$transaction_id)->update($update_code);
      
      //   foreach ($items as $key => $value) 
      //   {
      //     $get_inventory_quantity                       = Tbl_inventory::where('inventory_item_id',$value->item_id)->first();
  
      //     $update_inventory['inventory_quantity']       =  $get_inventory_quantity->inventory_quantity + $value->quantity;
      //     $update_inventory['inventory_sold']           =  $get_inventory_quantity->inventory_sold     - $value->quantity;
        
      //     Tbl_inventory::where('inventory_item_id',$value->item_id)->update($update_inventory);
      //   }      
      // }
   
    }
  public static function dragonpay_postback()
  {
    $transaction_id                                   = Request::input('param1');
    $update['dragonpay_refno']                        = Request::input('refno');      
    $update['dragonpay_status']                       = Request::input('status');      
    $update['dragonpay_message']                      = Request::input('message'); 
    
    if(Request::input('status') == 'S' || Request::input('status') == 'F' || Request::input('status') == 'U')
    {
      $update['date_accomplished']                     = Carbon::now(); 
    }

    Tbl_dragonpay_transaction::where('id',$transaction_id)->orWhere('dragonpay_txnid',Request::input('txnid'))->update($update);
    $transaction_data                                 = Tbl_dragonpay_transaction::where('id',$transaction_id)->first();
    $items                                            = json_decode($transaction_data->ordered_item);

    if(Request::input('status') == 'S')
    {   
      $check_if_exists                                = Tbl_codes::where('order_id',$transaction_id)->count();
      if($check_if_exists <= 0)
      {
        Cashier::inventory_codes_ordered($transaction_data->ordered_item, $transaction_data->cashier_user_id, $transaction_data->buyer_slot_id, 1, $transaction_id);
      }

      $update_code['code_date_sold']                  = Carbon::now();               
      $update_code['archived']                        = 0;         
      $check_if_auto_distribute                       = DB::table('tbl_mlm_feature')->where('mlm_feature_name', 'auto_distribute')->first();

      foreach ($items as $key => $value) 
      {
        if($check_if_auto_distribute->mlm_feature_enable == 0)
        {
          $inventory_item 			                      = Tbl_item::where('item_id',$value->item_id)->first();
          if($inventory_item->item_type == 'product')
          {
            $update_code['code_date_used']            = Carbon::now(); 
          }
        }
      }
      Tbl_codes::where('order_id',$transaction_id)->update($update_code);
      Cashier::create_order_v2($transaction_data->ordered_item,$transaction_data->vat,$transaction_data->buyer_slot_id,$transaction_data->cashier_user_id,$transaction_data->from,$transaction_data->delivery_method,$transaction_data->picked_up,$transaction_data->change,$transaction_data->manager_discount,$transaction_data->remarks,$transaction_data->address,$transaction_data->cashier_method,0,1,$transaction_data->dragonpay_charged,$transaction_data->voucher,$transaction_data->courier,$transaction_data->shipping_fee,$transaction_data->other_discount,$transaction_data->for_approval_trans_no);
    }
    if(Request::input('status') == 'P')
    {   
			Cashier::inventory_codes_ordered($transaction_data->ordered_item, $transaction_data->cashier_user_id, $transaction_data->buyer_slot_id, 1, $transaction_id);
    }
    elseif(Request::input('status') == 'F' || Request::input('status') == 'U') 
    {
      $update_code['code_used']                       = 0;         
      $update_code['code_sold']                       = 0;         
      $update_code['code_sold_to']                    = null;             
      $update_code['code_date_sold']                  = null;               
      $update_code['code_date_used']                  = null;               
      $update_code['code_used_by']                    = null;             
      $update_code['archived']                        = 0;         
      $update_code['dragonpay']                       = 0;         
      $update_code['order_id']                        = null;   
            
      Tbl_codes::where('order_id',$transaction_id)->update($update_code);
    
      foreach ($items as $key => $value) 
      {
        $get_inventory_quantity                       = Tbl_inventory::where('inventory_item_id',$value->item_id)->first();

        $update_inventory['inventory_quantity']       =  $get_inventory_quantity->inventory_quantity + $value->quantity;
        $update_inventory['inventory_sold']           =  $get_inventory_quantity->inventory_sold     - $value->quantity;
      
        Tbl_inventory::where('inventory_item_id',$value->item_id)->update($update_inventory);
      }      
    }
 
  }
  public function dragonpay_return()
  {
    $transaction_id                                   = Request::input('param1');
    $update['dragonpay_refno']                        = Request::input('refno');      
    $update['dragonpay_status']                       = Request::input('status');      
    $update['dragonpay_message']                      = Request::input('message'); 
    if(Request::input('status') == 'S' || Request::input('status') == 'F' || Request::input('status') == 'U')
    {
      $update['date_accomplished']                     = Carbon::now(); 
    }

    Tbl_dragonpay_transaction::where('id',$transaction_id)->orWhere('dragonpay_txnid',Request::input('txnid'))->update($update);

    if(Request::input('status') == 'S')
    {
      return Redirect::to("https://staging.bestlabph.com/transaction/success");
    }
    elseif (Request::input('status') == 'P') 
    {
      return Redirect::to("https://staging.bestlabph.com/transaction/pending");
    }
    elseif (Request::input('status') == 'F' || Request::input('status') == 'U') 
    {
      $get_voucher                                    = Tbl_dragonpay_transaction::where('id',$transaction_id)->orWhere('dragonpay_txnid',Request::input('txnid'))->first();
      if($get_voucher)
      {
        Log::insert_wallet($get_voucher->buyer_slot_id, $get_voucher->voucher,'REFUND (INVALID TRANSACTION)', 13);
      }
      return Redirect::to("https://staging.bestlabph.com/transaction/failed");
    }      
  }
}


