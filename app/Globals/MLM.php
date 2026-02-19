<?php
namespace App\Globals;

use App\Models\Tbl_dropshipping_bonus;
use App\Models\Tbl_dropshipping_bonus_logs;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use App\Globals\Log;

use App\Models\Tbl_slot;
use App\Models\Tbl_mlm_plan;
use App\Models\Tbl_item;
use App\Models\Tbl_mlm_incentive_bonus;
use App\Models\Tbl_currency;
use App\Models\Tbl_membership_vortex;
use App\Models\Tbl_label;
use App\Models\Tbl_retailer_commission_logs;
use App\Models\Tbl_product_share_link_logs;
use App\Models\Tbl_membership;
use App\Models\Tbl_membership_product_level;
use App\Models\Tbl_tree_sponsor;
use App\Models\Tbl_membership_overriding_commission_level;
use App\Models\Tbl_product_direct_referral_logs;
use App\Models\Tbl_product_personal_cashback;
use App\Models\Tbl_product_personal_cashback_logs;
use App\Models\Tbl_overriding_commission_v2;
use App\Models\Tbl_team_sales_bonus_settings;
use App\Models\Tbl_team_sales_bonus_logs;
use App\Models\Tbl_overriding_bonus_settings;
use App\Models\Tbl_overriding_bonus_logs;
use App\Models\Tbl_team_sales_bonus_level;
use App\Models\Tbl_retailer_override;
use App\Models\Tbl_retailer_override_logs;


use App\Globals\Mlm_complan_manager;
use App\Globals\Vortex;



class MLM
{
	public static function entry($slot_info)
	{

	}

	public static function placement_entry($slot_id, $import = null,$membership_id = null)
	{
        $slot_info = Tbl_slot::where('slot_id', $slot_id)->where("slot_sponsor","!=","0")->first();
        if($slot_info)
        {
            if($membership_id != null)
            {
                $slot_info->slot_membership = $membership_id;
            }
            // Mlm Computation Plan
            $plan_settings = Tbl_mlm_plan::where('mlm_plan_enable', 1)
                                         ->where('mlm_plan_trigger', 'Slot Placement')
                                         ->get();
            
            if($slot_info->slot_type == 'PS')
            {
                foreach($plan_settings as $key => $value)
                {
                    $plan = strtolower($value->mlm_plan_code);
                    Mlm_complan_manager::$plan($slot_info);

                }
            }
            // End Computation Plan
            
        }
	}

	public static function create_entry($slot_id,$membership_id = null)
	{
        $slot_info = Tbl_slot::where('slot_id', $slot_id)->first();

        if($membership_id != null)
        {
            $slot_info->slot_membership = $membership_id;
        }
        // Mlm Computation Plan
        $plan_settings = Tbl_mlm_plan::where('mlm_plan_enable', 1)
                                     ->where('mlm_plan_trigger', 'Slot Creation')
                                     ->get();

        if($slot_info->slot_type == 'PS')
        {
            foreach($plan_settings as $key => $value)
            {
                $plan = strtolower($value->mlm_plan_code);
                $a = Mlm_complan_manager::$plan($slot_info);
            }
        }
        // End Computation Plan
	}

    public static function purchase($slot_id,$item_id)
    {
        $slot_info = Tbl_slot::where('slot_id', $slot_id)->first();
        // Mlm Computation Plan
        $plan_settings = Tbl_mlm_plan::where('mlm_plan_enable', 1)
                                     ->where('mlm_plan_trigger', 'Slot Repurchase')
                                     ->get();
        $item = Tbl_item::where("item_id",$item_id)->where("archived",0)->first();                             
        if($item)
        {
            $points       = $item->item_pv;
            $binary_pts   = $item->item_binary_pts;
            $vortex_token = $item->item_vortex_token;
        }
        else
        {
            $points       = 0;
            $binary_pts   = 0;
            $vortex_token = 0;
        }

        Self::added_days($slot_info,$item);
        
        foreach ($plan_settings as $key => $value) {
            $plan = strtolower($value->mlm_plan_code);
            
            if ($plan === "binary_repurchase" && $binary_pts != 0) {
                $a = Mlm_complan_manager_repurchase::$plan($slot_info, $binary_pts);
            } elseif ($plan === "project_001") {
                Mlm_complan_manager_repurchase::$plan($slot_info, 0, $item_id);
            } elseif ($points != 0) {
                $a = Mlm_complan_manager_repurchase::$plan($slot_info, $points, $item_id);
            }
        }
        

        if($vortex_token != 0)
        {   
            /* VORTEX */
            $vortex_plan = Tbl_mlm_plan::where("mlm_plan_code","VORTEX_PLAN")->where("mlm_plan_enable",1)->first();
            if($vortex_plan)
            {
                Vortex::insert_token($slot_id,$slot_id,"PRODUCT_REPURCHASE",$vortex_token);
            }
        }
        // End Computation Plan

        if (optional(Tbl_mlm_plan::where('mlm_plan_code', 'TEAM_SALES_BONUS')->first())->mlm_plan_trigger === 'Product Activation') {
            Self::team_sales_bonus($slot_id, $item_id);
        }
    }
    
    public static function purchase_item($ordered_item, $slot_id, $subtotal = 0)
    {
        $orders = json_decode($ordered_item);
        $slot = Tbl_slot::where('slot_id', $slot_id)->first();
        if($slot->membership_inactive == 1 && $slot->slot_sponsor_product != 0)
        {
            $slot_info = Tbl_slot::where('slot_id', $slot->slot_sponsor_product)->first();
            foreach($orders as $key => $value)
            {
                for($qty = 1; $qty <= $value->quantity; $qty++)
                {
                   Mlm_complan_manager_repurchase::repurchase_commission($slot_info,$slot_id,$value->item_id);
                   Self::personal_cashback($slot_id,$value->item_id);
                }
                
            }
        }
        else if($slot->membership_inactive == 1)
        {
            foreach($orders as $key => $value)
            {
                for($qty = 1; $qty <= $value->quantity; $qty++)
                {
                    Self::product_personal_cashback($slot_id,$value->item_id);
                    Self::retailer_override($slot_id,$value->item_id);
                }                
            }
        }
        else
        {
            foreach($orders as $key => $value)
            {
                for($qty = 1; $qty <= $value->quantity; $qty++)
                {
                    Self::personal_cashback($slot_id,$value->item_id);
                    Self::direct_cashback($slot_id,$value->item_id);
                    Self::overriding_commission_v2($slot_id,$value->item_id);
                    Self::overriding_bonus($slot_id,$value->item_id);
                    if (optional(Tbl_mlm_plan::where('mlm_plan_code', 'TEAM_SALES_BONUS')->first())->mlm_plan_trigger === 'Product Purchase') {
                        Self::team_sales_bonus($slot_id, $value->item_id);
                    }
                }
            }
        }

        /*RETIALER COMMISSION*/ 
        $retailer_commission                                = Tbl_mlm_plan::where('mlm_plan_code','RETAILER_COMMISSION')->pluck('mlm_plan_enable')->first() ?? 0;
        if($retailer_commission == 1)
        {
            if($slot->membership_inactive == 1 && $slot->slot_sponsor_member > 0)
            {
                $plan_label                                 = Tbl_label::where('plan_code','RETAILER_COMMISSION')->pluck('plan_name')->first();
                foreach($orders as $key => $value)
                {
                    for($qty = 1; $qty <= $value->quantity; $qty++)
                    {
                        $product                            = Tbl_item::where("item_id",$value->item_id)->first();
                        $currency_id	                    = Tbl_currency::where('currency_abbreviation','=',$product->item_points_currency)->first()->currency_id;
                        $sponsor_details                    = Tbl_slot::where('slot_id',$slot->slot_sponsor_member)
                                                            ->leftjoin('tbl_membership','tbl_membership.membership_id','tbl_slot.slot_membership')->first();
                        $commission                         = ($sponsor_details->retailer_commission/100) * $product->item_price;
                                
                        
                        $insert['slot_id']                  = $sponsor_details->slot_id;
                        $insert['cause_slot_id']            = $slot_id;
                        $insert['item_id']                  = $value->item_id;
                        $insert['date']                     = Carbon::now();
                        $insert['commission']               = $commission; 

                        sleep(1);
                        Tbl_retailer_commission_logs::insert($insert);
                        Log::insert_wallet($sponsor_details->slot_id,$commission, $plan_label,$currency_id);
                        Log::insert_earnings($sponsor_details->slot_id,$commission,"RETAILER_COMMISSION","PRODUCT PURCHASE",$slot_id,null,0,$currency_id);

                    }
                    
                }
            }

        }

        /*PRODUCT SHARE LINK*/
        $product_share_link                                 = Tbl_mlm_plan::where('mlm_plan_code','PRODUCT_SHARE_LINK')->pluck('mlm_plan_enable')->first() ?? 0;
        if($product_share_link == 1)
        { 
            $slot_info         = Tbl_slot::where('slot_id',$slot_id)->first();
            $slot_tree         = Tbl_tree_sponsor::where("sponsor_child_id",$slot_info->slot_id)->orderby("sponsor_level", "asc")->get();
           
            /* RECORD ALL INTO A SINGLE VARIABLE */
            /* CHECK IF LEVEL EXISTS */
            $price = 0;
            foreach($orders as $key => $value)
            {
                for($qty = 1; $qty <= $value->quantity; $qty++)
                {
                   $price += Tbl_item::where('item_id',$value->item_id)->where('item_type','product')->first()->item_price ?? 0;
                }
                
            }
            foreach($slot_tree as $key => $tree)
            {
                $slot_sponsor                               = Tbl_slot::where("slot_id",$tree->sponsor_parent_id)->first();
                $product_sharelink_income                   = Tbl_membership_product_level::where("membership_id",$slot_sponsor->slot_membership)->where("membership_entry_id",$slot_info->slot_membership)->where("membership_level",$tree->sponsor_level)->first()->membership_product_income ?? 0;
                $overriding_income                          = Tbl_membership_overriding_commission_level::where("membership_id",$slot_sponsor->slot_membership)->where("membership_entry_id",$slot_info->slot_membership)->where("membership_level",$tree->sponsor_level)->first()->membership_overriding_commission_income ?? 0;
            
                $plan_label                                 = Tbl_label::where('plan_code','PRODUCT_SHARE_LINK')->pluck('plan_name')->first();
                $overriding_label                           = Tbl_label::where('plan_code','OVERRIDING_COMMISSION')->pluck('plan_name')->first();

                $product                                    = Tbl_item::where("item_id",$value->item_id)->first();
                $currency_id	                            = Tbl_currency::where('currency_abbreviation','=',$product->item_points_currency)->first()->currency_id;

                sleep(1);
                if($product_sharelink_income != 0)
                {
                    $product_sharelink_income               = ($product_sharelink_income/100) * $price;
                    Log::insert_wallet($tree->sponsor_parent_id,$product_sharelink_income, $plan_label,$currency_id);
                    Log::insert_earnings($tree->sponsor_parent_id,$product_sharelink_income,"PRODUCT_SHARE_LINK","PRODUCT PURCHASE",$slot_id,null,0,$currency_id);
                }

                if($overriding_income != 0)
                {
                    $overriding_income                      = ($overriding_income/100) * $price;
                    Log::insert_wallet($tree->sponsor_parent_id,$overriding_income,$overriding_label,$currency_id);
                    Log::insert_earnings($tree->sponsor_parent_id,$overriding_income,"OVERRIDING_COMMISSION","PRODUCT PURCHASE",$slot_id,null,0,$currency_id);
                }
            } 
        }

        /*OVERRIDING COMMISSION*/
        $overriding_commission_status                       = Tbl_mlm_plan::where('mlm_plan_code','OVERRIDING_COMMISSION')->pluck('mlm_plan_enable')->first() ?? 0;
        if($overriding_commission_status == 1)
        { 
            $slot_info         = Tbl_slot::where('slot_id',$slot_id)->first();
            $slot_tree         = Tbl_tree_sponsor::where("sponsor_child_id",$slot_info->slot_id)->orderby("sponsor_level", "asc")->get();
        
            /* RECORD ALL INTO A SINGLE VARIABLE */
            /* CHECK IF LEVEL EXISTS */
            $price = 0;
            foreach($orders as $key => $value)
            {
                for($qty = 1; $qty <= $value->quantity; $qty++)
                {
                    $price += $value->discounted_price > 0 ? $value->discounted_price : Tbl_item::where('item_id',$value->item_id)->where('item_type','product')->first()->item_price ?? 0;
                }
                
            }
            foreach($slot_tree as $key => $tree)
            {
                $slot_sponsor                               = Tbl_slot::where("slot_id",$tree->sponsor_parent_id)->first();
                $overriding_income                          = Tbl_membership_overriding_commission_level::where("membership_id",$slot_sponsor->slot_membership)->where("membership_entry_id",$slot_info->slot_membership)->where("membership_level",$tree->sponsor_level)->first()->membership_overriding_commission_income ?? 0;
            
                $overriding_label                           = Tbl_label::where('plan_code','OVERRIDING_COMMISSION')->pluck('plan_name')->first();

                $product                                    = Tbl_item::where("item_id",$value->item_id)->first();
                $currency_id	                            = Tbl_currency::where('currency_abbreviation','=',$product->item_points_currency)->first()->currency_id;

                if($overriding_income != 0)
                {
                    $overriding_income                      = ($overriding_income/100) * $price;
                    Log::insert_wallet($tree->sponsor_parent_id,$overriding_income,$overriding_label,$currency_id);
                    Log::insert_earnings($tree->sponsor_parent_id,$overriding_income,"OVERRIDING_COMMISSION","PRODUCT PURCHASE",$slot_id,null,0,$currency_id);
                }
            } 
        }
     

        /*PRODUCT DIRECT REFERRAL*/
        $product_direct_referral                          = Tbl_mlm_plan::where('mlm_plan_code','PRODUCT_DIRECT_REFERRAL')->pluck('mlm_plan_enable')->first() ?? 0;
        if($product_direct_referral == 1)
        {
            if($slot->membership_inactive == 0 && $slot->slot_sponsor > 0)
            {
                foreach($orders as $key => $value)
                {
                    for($qty = 1; $qty <= $value->quantity; $qty++)
                    {
                        $product                            = Tbl_item::where("tbl_item.item_id",$value->item_id)->where('item_type','product')->first();
                        $sponsor_details                    = Tbl_slot::where('slot_id',$slot->slot_sponsor)
                        ->leftjoin('tbl_item_direct_referral_settings','tbl_item_direct_referral_settings.membership_id','tbl_slot.slot_membership')->where('item_id',$value->item_id)->first() ?? null;
                        
                        if($product)
                        {
                            $currency_id	                    = Tbl_currency::where('currency_abbreviation','=',$product->item_points_currency)->first()->currency_id;
                            if($sponsor_details != null && $sponsor_details->type != null)
                            {
                                $plan_label                     = Tbl_label::where('plan_code','PRODUCT_DIRECT_REFERRAL')->pluck('plan_name')->first();

                                if($sponsor_details->type == 'fixed')
                                {
                                    $commission                  = $sponsor_details->commission;
                                }
                                else
                                {
                                    $commission                  = ($sponsor_details->commission/100) * $product->item_price;
                                }
                                
                                $insert_logs['slot_id']          = $sponsor_details->slot_id;                    
                                $insert_logs['buyer_id']         = $slot_id;                       
                                $insert_logs['membership_id']    = $sponsor_details->slot_membership;                            
                                $insert_logs['item_id']          = $value->item_id;                    
                                $insert_logs['commission']       = $commission;                        
                                $insert_logs['type']             = $sponsor_details->type;                        
                                $insert_logs['date']             = Carbon::now(); 

                                sleep(1);
                                Tbl_product_direct_referral_logs::insert($insert_logs);
                                Log::insert_wallet($sponsor_details->slot_id,$commission, $plan_label,$currency_id);
                                Log::insert_earnings($sponsor_details->slot_id,$commission,"PRODUCT_DIRECT_REFERRAL","PRODUCT REPURCHASE",$slot_id,null,0,$currency_id);
                            }
                        }
                    }
                    
                }
            }
        }

    }

    
    public static function dropshipping_purchase_item($ordered_item, $slot_id, $subtotal = 0, $order_id)
    {
        $orders = json_decode($ordered_item);

        foreach($orders as $key => $value)
        {
            for($qty = 1; $qty <= $value->quantity; $qty++)
            {
                Self::dropshipping_bonus($slot_id,$value->item_id,$order_id);
            }
        }
    }

    public static function added_days($slot_info,   $item)
    {
        $new_date_end        = Carbon::now()->addDays($item->added_days);
        $old_date_end        = $slot_info->maintained_until_date;
        $old_pv = Tbl_slot::where("slot_id",$slot_info->slot_id)->first()->slot_personal_spv;
        $update_slot_child["slot_personal_spv"] = $old_pv + $item->item_pv;
        Tbl_slot::where("slot_id",$slot_info->slot_id)->update($update_slot_child);
        if($new_date_end >= $old_date_end)
        {
            Tbl_slot::where("slot_id",$slot_info->slot_id)->update(["maintained_until_date" => $new_date_end]);
        }
    }
    public static function personal_cashback($slot_id,$item_id)
    {
        //--------------------------------------------------------------------------------------------------------------------------

        $code = Tbl_item::where("item_id",$item_id)->first();
        if($code)
        {
            $check_plan_incentive     = Tbl_mlm_plan::where('mlm_plan_code','=','INCENTIVE_BONUS')->first() ? Tbl_mlm_plan::where('mlm_plan_code','=','INCENTIVE_BONUS')->first()->mlm_plan_enable : 0;
            $check_incentive_settings = Tbl_mlm_incentive_bonus::first() ? Tbl_mlm_incentive_bonus::first()->incentives_status : 0 ;
            if($code->item_points_incetives != 0 && $check_incentive_settings == 1 && $check_plan_incentive == 1)
            {
                $currency_id	= Tbl_currency::where('currency_abbreviation','=',$code->item_points_currency)->first()->currency_id;
                Log::insert_wallet($slot_id,$code->item_points_incetives,"UPCOIN",$currency_id);
                $details = "";
                Log::insert_earnings($slot_id,$code->item_points_incetives,"UPCOIN","PRODUCT REPURCHASE",$slot_id,$details,0,$currency_id);
            }
            //--------------------------------------------------------------------------------------------------------------------------
    
            $check_plan_cashback     = Tbl_mlm_plan::where('mlm_plan_code','=','PERSONAL_CASHBACK')->first() ? Tbl_mlm_plan::where('mlm_plan_code','=','PERSONAL_CASHBACK')->first()->mlm_plan_enable: 0;
            if($code->cashback_points == 0 && $code->cashback_wallet != 0 || $code->cashback_points != 0 && $code->cashback_wallet == 0 || $code->cashback_points != 0 && $code->cashback_wallet != 0)
            {
                if($check_plan_cashback == 1)
                {
                    $percent        = Tbl_slot::where("slot_id",$slot_id)->JoinMembership()->pluck("cashback_percent")->first();
                    $total_wallet   = number_format((($code->cashback_wallet*$percent)/100), 2);
                    $total_points   = number_format((($code->cashback_points*$percent)/100), 2);
                    $currency_id	= Tbl_currency::where('currency_abbreviation','=',$code->item_points_currency)->first()->currency_id;
                    Log::insert_wallet($slot_id,$total_wallet,"PERSONAL_CASHBACK",$currency_id);
                    $details = "";
                    Log::insert_earnings($slot_id,$total_wallet,"PERSONAL_CASHBACK","PRODUCT REPURCHASE",$slot_id,$details,0,$currency_id);
                    Log::insert_personal_cashback_points($slot_id,$total_points);
                }
            }
        
        }
        
        //--------------------------------------------------------------------------------------------------------------------------
    }
    public static function  direct_cashback($slot_id, $item_id)
    {
        $check_if_enable                        = Tbl_mlm_plan::where('mlm_plan_code','DIRECT_PERSONAL_CASHBACK')->pluck('mlm_plan_enable')->first();

        if($check_if_enable == 1)
        {
            $label                              = Tbl_label::where('plan_code','DIRECT_PERSONAL_CASHBACK')->pluck('plan_name')->first();
            $currency_id                        = Tbl_currency::where('currency_buying',1)->pluck('currency_id')->first();
            $membership_direct_cashback         = Tbl_slot::where('slot_id', $slot_id)->leftjoin('tbl_membership','tbl_membership.membership_id','tbl_slot.slot_membership')->pluck('direct_cashback')->first();
            $item_direct_cashback               = Tbl_item::where('item_id',$item_id)->pluck('direct_cashback')->first();
            $total_cashback                     = ( $membership_direct_cashback / 100) * $item_direct_cashback;
            
            sleep(2);
            Log::insert_wallet($slot_id,$total_cashback,$label,$currency_id);
            Log::insert_earnings($slot_id,$total_cashback,"DIRECT_PERSONAL_CASHBACK","PRODUCT PURCHASE",$slot_id,"",0,$currency_id);

        }
    }
    public static function product_personal_cashback($slot_id, $item_id)
    {
        $product_personal_cashback                      = Tbl_mlm_plan::where('mlm_plan_code','PRODUCT_PERSONAL_CASHBACK')->pluck('mlm_plan_enable')->first() ?? 0;
        if($product_personal_cashback == 1)
        {
            $check_if_product                           = Tbl_item::where('item_id',$item_id)->first();
            $plan_label                                 = Tbl_label::where('plan_code','PRODUCT_PERSONAL_CASHBACK')->pluck('plan_name')->first();
            
            if($check_if_product->item_type == 'product')
            {
                $slot                                   = Tbl_slot::where('slot_id',$slot_id)->where('membership_inactive',1)->first();
                if($slot->slot_sponsor != 0)
                {
                    $check_sponsor_details              = Tbl_slot::where('slot_id',$slot->slot_sponsor)->first();
                    // $slot_membership                    = Tbl_slot::where('slot_id',$slot_id)->first()->slot_membership;
                    $currency_id                        = Tbl_currency::where('currency_default',1)->pluck('currency_id')->first();
                    $cashback_settings                  = Tbl_product_personal_cashback::where('membership_id',$check_sponsor_details->slot_membership)->where('item_id',$item_id)->first();
                    if($cashback_settings)
                    {
                        if($cashback_settings->commission > 0)
                        {
                            $personal_cashback              = $cashback_settings->type == 'fixed' ? $cashback_settings->commission : ($cashback_settings->commission / 100) * $check_if_product->item_price;
                           
                            $insert_log['slot_id']          = $check_sponsor_details->slot_id;                    
                            $insert_log['cause_id']         = $slot_id;                    
                            $insert_log['membership_id']    = $check_sponsor_details->slot_membership;                            
                            $insert_log['item_id']          = $item_id;                    
                            $insert_log['commission']       = $personal_cashback;                        
                            $insert_log['type']             = $cashback_settings->type ?? null;                       
                            $insert_log['date']             = Carbon::now(); 
        
                            // sleep(1);
                            Tbl_product_personal_cashback_logs::insert($insert_log);
                            Log::insert_wallet($check_sponsor_details->slot_id,$personal_cashback,$plan_label,$currency_id);
                            Log::insert_earnings($check_sponsor_details->slot_id,$personal_cashback,"PRODUCT_PERSONAL_CASHBACK","PRODUCT PURCHASE",$slot_id,"",0,$currency_id);
                        }
                    }
                }
            }
        }
    }
    public static function overriding_commission_v2($slot_id, $item_id)
    {
        $plan_status                                    = Tbl_mlm_plan::where('mlm_plan_code','OVERRIDING_COMMISSION_V2')->pluck('mlm_plan_enable')->first() ?? 0;
        if($plan_status == 1)
        {
            $check_if_product                           = Tbl_item::where('item_id',$item_id)->first();
            $plan_label                                 = Tbl_label::where('plan_code','OVERRIDING_COMMISSION_V2')->pluck('plan_name')->first();
            
            if($check_if_product->item_type == 'membership_kit')
            {
                $slot                                   = Tbl_slot::where('slot_id',$slot_id)->where('membership_inactive',0)->first();
                if($slot->slot_sponsor != 0)
                {
                    $check_sponsor_details              = Tbl_slot::where('slot_id',$slot->slot_sponsor)->first();

                    $earning                            = Tbl_overriding_commission_v2::where('membership_id',$check_sponsor_details->slot_membership)->where('membership_entry_id',$slot->slot_membership)->pluck('income')->first() ?? 0;
                    $currency_id                        = Tbl_currency::where('currency_default',1)->pluck('currency_id')->first();
                    if($earning > 0)
                    {
                        Log::insert_wallet($check_sponsor_details->slot_id,$earning,$plan_label,$currency_id);
                        Log::insert_earnings($check_sponsor_details->slot_id,$earning,"OVERRIDING_COMMISSION_V2","PRODUCT PURCHASE",$slot_id,"",0,$currency_id);
                    }
                }
            }
        }
    }
    public static function team_sales_bonus($slot_id, $item_id)
    {
        $plan_status                                    = Tbl_mlm_plan::where('mlm_plan_code','TEAM_SALES_BONUS')->pluck('mlm_plan_enable')->first() ?? 0;
        if($plan_status == 1)
        {
            $check_if_product                           = Tbl_item::where('item_id',$item_id)->first();
            $plan_label                                 = Tbl_label::where('plan_code','TEAM_SALES_BONUS')->pluck('plan_name')->first();
            
            if($check_if_product->item_type == 'product')
            {
                $slot                                    = Tbl_slot::where('slot_id',$slot_id)->where('membership_inactive',0)->leftjoin('tbl_membership','tbl_membership.membership_id','tbl_slot.slot_membership')->first();

                if($slot)
                {
                    $tree                                = Tbl_tree_sponsor::where('sponsor_child_id',$slot_id)->where('sponsor_level','<=',$slot->team_sales_bonus_level)->get();
                    
                    foreach ($tree as $key => $value) 
                    {
                        $get_parent_details             = Tbl_slot::where('slot_id',$value->sponsor_parent_id)->leftjoin('tbl_membership','tbl_membership.membership_id','tbl_slot.slot_membership')->first();

                        $currency_id                    = Tbl_currency::where('currency_default',1)->pluck('currency_id')->first();
                        $earning                        = Tbl_team_sales_bonus_level::where('item_id',$item_id)->where('membership_level',$value->sponsor_level)->where('membership_id',$get_parent_details->membership_id)->where('membership_entry_id',$slot->membership_id)->pluck('team_sales_bonus')->first() ?? 0;

                        if($earning > 0)
                        {
                            
                            Log::insert_wallet($value->sponsor_parent_id,$earning,$plan_label,$currency_id);
                            Log::insert_wallet($value->sponsor_parent_id,$earning,$plan_label,16);
                            Log::insert_earnings($value->sponsor_parent_id,$earning,"TEAM_SALES_BONUS","PRODUCT PURCHASE",$slot_id,"",0,$currency_id);

                            $insert_log['slot_id']          = $value->sponsor_parent_id;                    
                            $insert_log['cause_id']         = $slot_id;                    
                            $insert_log['membership_id']    = $get_parent_details->membership_id;                            
                            $insert_log['item_id']          = $item_id;                    
                            $insert_log['commission']       = $earning;                        
                            $insert_log['type']             = 'fixed';                       
                            $insert_log['date']             = Carbon::now(); 
        
                            Tbl_team_sales_bonus_logs::insert($insert_log);
                        }

                    }
                }
                // if($slot->slot_sponsor != 0)
                // {


                //     $check_sponsor_details                = Tbl_slot::where('slot_id',$slot->slot_sponsor)->first();
                //     $currency_id                          = Tbl_currency::where('currency_default',1)->pluck('currency_id')->first();
                //     $team_sales_settings                  = Tbl_team_sales_bonus_settings::where('membership_id',$check_sponsor_details->slot_membership)->where('item_id',$item_id)->first();
                //     if($team_sales_settings)
                //     {
                //         if($team_sales_settings->commission > 0)
                //         {
                //             $personal_cashback              = $team_sales_settings->type == 'fixed' ? $team_sales_settings->commission : ($team_sales_settings->commission / 100) * $check_if_product->item_price;
                           
                //             $insert_log['slot_id']          = $check_sponsor_details->slot_id;                    
                //             $insert_log['cause_id']         = $slot_id;                    
                //             $insert_log['membership_id']    = $check_sponsor_details->slot_membership;                            
                //             $insert_log['item_id']          = $item_id;                    
                //             $insert_log['commission']       = $personal_cashback;                        
                //             $insert_log['type']             = $team_sales_settings->type ?? null;                       
                //             $insert_log['date']             = Carbon::now(); 
        
                //             Tbl_team_sales_bonus_logs::insert($insert_log);
                //             Log::insert_wallet($check_sponsor_details->slot_id,$personal_cashback,$plan_label,$currency_id);
                //             Log::insert_wallet($check_sponsor_details->slot_id,$personal_cashback,$plan_label,16);
                //             Log::insert_earnings($check_sponsor_details->slot_id,$personal_cashback,"TEAM_SALES_BONUS","PRODUCT PURCHASE",$slot_id,"",0,$currency_id);
                //         }
                //     }
                // }
            }
        }
    }
    public static function overriding_bonus($slot_id, $item_id)
    {
        $plan_status                                    = Tbl_mlm_plan::where('mlm_plan_code','OVERRIDING_BONUS')->pluck('mlm_plan_enable')->first() ?? 0;
        if($plan_status == 1)
        {
            $check_if_product                           = Tbl_item::where('item_id',$item_id)->first();
            $plan_label                                 = Tbl_label::where('plan_code','OVERRIDING_BONUS')->pluck('plan_name')->first();
            
            if($check_if_product->item_type == 'product')
            {

                $tree                                    = Tbl_tree_sponsor::where('sponsor_child_id',$slot_id)->get();
                $slot                                    = Tbl_slot::where('slot_id',$slot_id)->where('membership_inactive',0)->leftjoin('tbl_membership','tbl_membership.membership_id','tbl_slot.slot_membership')->first();
                $get_highest_membership                  = Tbl_membership::where('archive',0)->where('hierarchy',15)->first();
                if($get_highest_membership)
                {
                    $get_second_highest_membership           = Tbl_membership::where('archive',0)->where('membership_id','!=',$get_highest_membership->membership_id)->orderBy('hierarchy','DESC')->first();
                }
                else
                {
                    $get_second_highest_membership           = Tbl_membership::where('archive',0)->orderBy('hierarchy','DESC')->first();
                }
                $higher_package_status                   = 0;
                $second_package_status                   = 0;
               
                foreach ($tree as $key => $value) 
                {
                   $check_slot                          = Tbl_slot::where('slot_id',$value->sponsor_parent_id)->leftjoin('tbl_membership','tbl_membership.membership_id','tbl_slot.slot_membership')->first();

                   if($higher_package_status == 0)
                   {
                       if($check_slot->hierarchy == $get_second_highest_membership->hierarchy)
                       {
                            if($second_package_status == 0)
                            {
                                if($get_second_highest_membership->hierarchy > $slot->hierarchy)
                                {
                                   $proceed                                 = 1;
                                   $second_package_status                   = 1;

                                    $overriding_bonus                       = Tbl_overriding_bonus_settings::where('membership_id',$check_slot->slot_membership)->where('item_id',$item_id)->first();
                                    if($overriding_bonus)
                                    {
                                        if($overriding_bonus->commission > 0)
                                        {
                                            $commission                     = $overriding_bonus->type == 'fixed' ? $overriding_bonus->commission : ($overriding_bonus->commission / 100) * $check_if_product->item_price;
                                            $currency_id                    = Tbl_currency::where('currency_default',1)->pluck('currency_id')->first();
                                        
                                            $insert_log['slot_id']          = $check_slot->slot_id;                    
                                            $insert_log['cause_id']         = $slot_id;                    
                                            $insert_log['membership_id']    = $check_slot->slot_membership;                            
                                            $insert_log['item_id']          = $item_id;                    
                                            $insert_log['commission']       = $commission;                        
                                            $insert_log['type']             = $overriding_bonus->type ?? null;                       
                                            $insert_log['date']             = Carbon::now(); 
                        
                                            Tbl_overriding_bonus_logs::insert($insert_log);
                                            Log::insert_wallet($check_slot->slot_id,$commission,$plan_label,$currency_id);
                                            Log::insert_wallet($check_slot->slot_id,$commission,$plan_label,17);
                                            Log::insert_earnings($check_slot->slot_id,$commission,"OVERRIDING_BONUS","PRODUCT PURCHASE",$slot_id,"",0,$currency_id);
                                        }
                                    }
                                }
                            }
                       }
                       if($get_highest_membership)
                       {
                           if($check_slot->hierarchy == $get_highest_membership->hierarchy)
                           {
                               if($get_highest_membership->hierarchy > $slot->hierarchy)
                               {
                                    $proceed                                 = 1;
                                    $higher_package_status                   = 1;

                                    if($second_package_status == 0)
                                    {
                                        $overriding_bonus                         = Tbl_overriding_bonus_settings::where('membership_id',$get_second_highest_membership->membership_id)->where('item_id',$item_id)->first();
                                    }
                                    else
                                    {
                                        $overriding_bonus                         = Tbl_overriding_bonus_settings::where('membership_id',$check_slot->slot_membership)->where('item_id',$item_id)->first();
                                    }
                                    if($overriding_bonus)
                                    {
                                        if($overriding_bonus->commission > 0)
                                        {
                                            $commission                     = $overriding_bonus->type == 'fixed' ? $overriding_bonus->commission : ($overriding_bonus->commission / 100) * $check_if_product->item_price;
                                            $currency_id                    = Tbl_currency::where('currency_default',1)->pluck('currency_id')->first();
                                        
                                            $insert_log['slot_id']          = $check_slot->slot_id;                    
                                            $insert_log['cause_id']         = $slot_id;                    
                                            $insert_log['membership_id']    = $check_slot->slot_membership;                            
                                            $insert_log['item_id']          = $item_id;                    
                                            $insert_log['commission']       = $commission;                        
                                            $insert_log['type']             = $overriding_bonus->type ?? null;                       
                                            $insert_log['date']             = Carbon::now(); 
                        
                                            Tbl_overriding_bonus_logs::insert($insert_log);
                                            Log::insert_wallet($check_slot->slot_id,$commission,$plan_label,$currency_id);
                                            Log::insert_wallet($check_slot->slot_id,$commission,$plan_label,17);
                                            Log::insert_earnings($check_slot->slot_id,$commission,"OVERRIDING_BONUS","PRODUCT PURCHASE",$slot_id,"",0,$currency_id);
                                        }
                                    }
                                 }
                           }
                       }
                   }
                   else
                   {
                    return;
                   }
                }
            }
        }
    }
    public static function retailer_override($slot_id, $item_id)
    {
        $retailer_override                      = Tbl_mlm_plan::where('mlm_plan_code','RETAILER_OVERRIDE')->pluck('mlm_plan_enable')->first() ?? 0;
        if($retailer_override == 1)
        {
            $check_if_product                           = Tbl_item::where('item_id',$item_id)->first();
            $plan_label                                 = Tbl_label::where('plan_code','RETAILER_OVERRIDE')->pluck('plan_name')->first();
            
            if($check_if_product->item_type == 'product')
            {
                $slot                                   = Tbl_slot::where('slot_id',$slot_id)->where('membership_inactive',1)->first();
                if($slot->slot_sponsor != 0)
                {
                    $check_sponsor_details              = Tbl_slot::where('slot_id',$slot->slot_sponsor)->first();
                    // $slot_membership                    = Tbl_slot::where('slot_id',$slot_id)->first()->slot_membership;
                    $currency_id                        = Tbl_currency::where('currency_default',1)->pluck('currency_id')->first();
                    $cashback_settings                  = Tbl_retailer_override::where('membership_id',$check_sponsor_details->slot_membership)->where('item_id',$item_id)->first();
                    if($cashback_settings)
                    {
                        if($cashback_settings->commission > 0)
                        {
                            $personal_cashback              = $cashback_settings->type == 'fixed' ? $cashback_settings->commission : ($cashback_settings->commission / 100) * $check_if_product->item_price;
                           
                            $insert_log['slot_id']          = $check_sponsor_details->slot_id;                    
                            $insert_log['cause_id']         = $slot_id;                    
                            $insert_log['membership_id']    = $check_sponsor_details->slot_membership;                            
                            $insert_log['item_id']          = $item_id;                    
                            $insert_log['commission']       = $personal_cashback;                        
                            $insert_log['type']             = $cashback_settings->type ?? null;                       
                            $insert_log['date']             = Carbon::now(); 
        
                            // sleep(1);
                            Tbl_retailer_override_logs::insert($insert_log);
                            Log::insert_wallet($check_sponsor_details->slot_id,$personal_cashback,$plan_label,$currency_id);
                            Log::insert_earnings($check_sponsor_details->slot_id,$personal_cashback,"RETAILER_OVERRIDE","PRODUCT PURCHASE",$slot_id,"",0,$currency_id);
                        }
                    }
                }
            }
        }
    }

    public static function dropshipping_bonus($slot_id,$item_id, $order_id) {
        $plan_status = Tbl_mlm_plan::where('mlm_plan_code','DROPSHIPPING_BONUS')->pluck('mlm_plan_enable')->first() ?? 0;
        if($plan_status == 1) {
            $check_if_product = Tbl_item::where('item_id',$item_id)->first();
            $plan_label = Tbl_label::where('plan_code','DROPSHIPPING_BONUS')->pluck('plan_name')->first();

            if($check_if_product->item_type == 'product')
            {
                $slot = Tbl_slot::where('slot_id',$slot_id)->where('membership_inactive',0)->first();

                if($slot)
                {
                    $currency_id = Tbl_currency::where('currency_default',1)->pluck('currency_id')->first();
                    $dropshipping_settings = Tbl_dropshipping_bonus::where('membership_id',$slot->slot_membership)->where('item_id',$item_id)->first();
                    if($dropshipping_settings)
                    {
                        if($dropshipping_settings->commission > 0)
                        {
                            $dropshipping_bonus = $dropshipping_settings->type == 'fixed' ? $dropshipping_settings->commission : ($dropshipping_settings->commission / 100) * $check_if_product->item_price;

                            $insert_log['slot_id'] = $slot_id;                       
                            $insert_log['membership_id'] = $slot->slot_membership;                            
                            $insert_log['item_id'] = $item_id;                    
                            $insert_log['order_id'] = $order_id;                    
                            $insert_log['commission'] = $dropshipping_bonus;                        
                            $insert_log['type'] = $dropshipping_settings->type ?? null;                       
                            $insert_log['date'] = Carbon::now(); 
                            Tbl_dropshipping_bonus_logs::insert($insert_log);
                            Log::insert_wallet($slot_id,$dropshipping_bonus,$plan_label,$currency_id);
                            Log::insert_earnings($slot_id,$dropshipping_bonus,"DROPSHIPPING_BONUS","SPECIAL PLAN",null,"",0,$currency_id);

                        }
                    }
                }
            }
        }
    }
}
