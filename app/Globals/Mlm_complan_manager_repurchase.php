<?php
namespace App\Globals;

use DB;
use Carbon\Carbon;
use Validator;

use App\Globals\Log;
use App\Globals\Stairstep;
use App\Globals\Mlm_complan_manager;

use App\Models\Tbl_slot;
use App\Models\Tbl_mlm_plan;
use App\Models\Tbl_membership_income;
use App\Models\Tbl_binary_points_settings;
use App\Models\Tbl_tree_placement;
use App\Models\Tbl_tree_sponsor;
use App\Models\Tbl_binary_pairing;
use App\Models\Tbl_membership_unilevel_level;
use App\Models\Tbl_unilevel_or_points;
use App\Models\Tbl_stairstep_settings;
use App\Models\Tbl_stairstep_rank;
use App\Models\Tbl_unileveL_points;
use App\Models\Tbl_stairstep_points;
use App\Models\Tbl_membership_cashback_level;
use App\Models\Tbl_membership;
use App\Models\Tbl_item;
use App\Models\Tbl_item_stairstep_rank_discount;
use App\Models\Tbl_item_membership_discount;
use App\Models\Tbl_binary_settings;
use App\Models\Tbl_earning_log;
use App\Models\Tbl_currency;
use App\Models\Tbl_binary_points;
use App\Models\Tbl_incentive_purchase_count;
use App\Models\Tbl_membership_mentors_level;
use App\Models\Tbl_mlm_lockdown_plan;
use App\Models\Tbl_other_settings;

class Mlm_complan_manager_repurchase
{
    public static function binary_repurchase($slot_info, $binary_pts)
    {
        if($binary_pts != 0)
        {
            Mlm_complan_manager::binary($slot_info, $binary_pts);
        }
    }

	public static function unilevel($slot_info, $points, $item_id = null)
	{
        if($points != 0)
        {

            /* ADD POINTS ON SLOT */
            /* FOR RECORDING ONLY*/
            // $update_slot_child["slot_personal_spv"] = Tbl_slot::where("slot_id",$slot_info->slot_id)->first()->slot_personal_pv + $points;
            // Tbl_slot::where("slot_id",$slot_info->slot_id)->update($update_slot_child);

            Log::insert_points($slot_info->slot_id,$points,"UNILEVEL_PPV",$slot_info->slot_id, 0);
            Log::insert_unilevel_points($slot_info->slot_id,$points,"UNILEVEL_PPV",$slot_info->slot_id,0,$item_id);

            $unilevel_level = Tbl_membership::where('membership_id', $slot_info->slot_membership)->first()->membership_unilevel_level;
            $gained_level = [];
            $all_levels = $unilevel_level ? range(1, $unilevel_level) : 0;
            $slot_tree = Tbl_tree_sponsor::where("sponsor_child_id",$slot_info->slot_id)->where("sponsor_parent_id", "!=", 1)->orderby("sponsor_level", "asc")->get();

            foreach($slot_tree as $key => $tree)
            {
                /* GET SPONSOR AND GET UNILEVEL BONUS INCOME PERCENTAGE  */
                $slot_sponsor   = Tbl_slot::where("slot_id",$tree->sponsor_parent_id)->first();
                $unilevel_percentage = Tbl_membership_unilevel_level::where("membership_id",$slot_sponsor->slot_membership)->where("membership_entry_id",$slot_info->slot_membership)->where("membership_level",$tree->sponsor_level)->first();
                if($unilevel_percentage)
                {
                    $unilevel_pts = ($unilevel_percentage->membership_percentage/100) * $points;
                }
                else
                {
                    $unilevel_pts = 0;
                }
                /* CHECK IF BONUS IS ZERO */
                if($unilevel_pts != 0)
                {
                    /* ADD POINTS ON SLOT */
                    // $update_slot_parent["slot_group_pv"] = Tbl_slot::where("slot_id",$slot_sponsor->slot_id)->first()->slot_group_pv + $unilevel_pts;
                    // Tbl_slot::where("slot_id",$slot_sponsor->slot_id)->update($update_slot_parent);
                    // dd($item_id);
                    $gained_level[] = $tree->sponsor_level;
                    Log::insert_points($slot_sponsor->slot_id,$unilevel_pts,"UNILEVEL_GPV",$slot_info->slot_id, $tree->sponsor_level);
                    Log::insert_unilevel_points($slot_sponsor->slot_id,$unilevel_pts,"UNILEVEL_GPV",$slot_info->slot_id, $tree->sponsor_level,$item_id);
                }
            } 
            // if (count($gained_level)) {
            //     Mlm_complan_manager::ungained_earnings_based_on_levels($all_levels, $gained_level, $slot_info, "unilevel", $item_id, $points);
            // }
        }
	}

    public static function stairstep($slot_info,$points)
    {
        // dd($slot_info,$points);
        $settings                    = Tbl_stairstep_settings::first();
        $override_percentage         = 0;
        $compare_override_percentage = 0;

        if($settings)
        {
                $cause_slot                       = Tbl_slot::where("slot_id",$slot_info->slot_id)->first();
                $update_self["slot_personal_spv"] = $cause_slot->slot_personal_spv + $points;
                Tbl_slot::where("slot_id",$cause_slot->slot_id)->update($update_self);
                Log::insert_points($cause_slot->slot_id,$points,"STAIRSTEP_PPV",$cause_slot->slot_id, 0);
                Log::insert_stairstep_points($cause_slot->slot_id,$points,"STAIRSTEP_PPV",$cause_slot->slot_id, 0);

                if($settings->live_update == 0)
                {
                    Stairstep::update_rank($cause_slot->slot_id);
                }
                
                $cause_current_rank_info = Tbl_stairstep_rank::where("stairstep_rank_id",$cause_slot->slot_stairstep_rank)->first();
                if($cause_current_rank_info)
                {
                    $override_percentage = $cause_current_rank_info->stairstep_rank_override;
                }
                else
                {
                    $override_percentage = 0;
                }
                
                /* CHECK UPPER SPONSOR IF THEY WILL RANK UP OR NOT */
                $slot_tree = Tbl_tree_sponsor::where("sponsor_child_id",$slot_info->slot_id)->orderby("sponsor_level", "asc")->get();
                foreach($slot_tree as $key => $tree)
                {
                    
                    $parent_slot                     = Tbl_slot::where("slot_id",$tree->sponsor_parent_id)->first();
                    $update_parent["slot_group_spv"] = $parent_slot->slot_group_spv + $points;
                    Tbl_slot::where("slot_id",$parent_slot->slot_id)->update($update_parent);
                    Log::insert_points($parent_slot->slot_id,$points,"STAIRSTEP_GPV",$cause_slot->slot_id, $tree->sponsor_level);

                    
                    /* GET OVERRIDE PERCENTAGE BY RANK*/
                    $current_rank_info = Tbl_stairstep_rank::where("stairstep_rank_id",$parent_slot->slot_stairstep_rank)->first();
                    if($current_rank_info)
                    {
                        $compare_override_percentage = $current_rank_info->stairstep_rank_override;
                    }

                    $override_given = 0;

                    /* COMPUTE OVERRIDE POINTS */
                    if($compare_override_percentage > $override_percentage)
                    {
                        $compute_override    = (($compare_override_percentage - $override_percentage)/100) * ($points);
                        $update_override["slot_override_points"] = Tbl_slot::where("slot_id",$tree->sponsor_parent_id)->first()->slot_override_points + $compute_override;
                        Tbl_slot::where("slot_id",$parent_slot->slot_id)->update($update_override);
                        Log::insert_points($parent_slot->slot_id,$compute_override,"OVERRIDE_POINTS",$cause_slot->slot_id, $tree->sponsor_level);
                        Log::insert_override_points($parent_slot->slot_id,$compute_override);
                        // Log::insert_stairstep_points($parent_slot->slot_id,$compute_override,"OVERRIDE_POINTS",$cause_slot->slot_id, $tree->sponsor_level);
                       

                        $override_given      = $compute_override;
                        $override_percentage = $compare_override_percentage;
                    }

                    Log::insert_stairstep_points($parent_slot->slot_id,$points,"STAIRSTEP_GPV",$cause_slot->slot_id, $tree->sponsor_level, $override_given);
                    
                    /* PROCEED TO HERE IF LIVE UPDATE IS ON*/
                    if($settings->live_update == 0)
                    {
                         
                        Stairstep::update_rank($parent_slot->slot_id);
                    }
                }     
        }
    }
    public static function cashback($slot_info,$points)
    {
        if($points != 0)
        {
            $slot_tree         = Tbl_tree_sponsor::where("sponsor_child_id",$slot_info->slot_id)->orderby("sponsor_level", "ASC")->get();
            foreach($slot_tree as $key => $tree)
            {
                $slot_sponsor   = Tbl_slot::where("slot_id",$tree->sponsor_parent_id)->first();
                $cashback_percentage = Tbl_membership_cashback_level::where("membership_id",$slot_sponsor->slot_membership)->where("membership_level",$tree->sponsor_level)->first();
                if($cashback_percentage)
                {
                    $cashback_money = ($cashback_percentage->membership_cashback_income/100) * $points;
                }
                else
                {
                    $cashback_money = 0;
                }
                if($cashback_money != 0)
                {
                    $cashback_money = round($cashback_money,2);
                    Log::insert_wallet($slot_sponsor->slot_id,$cashback_money,"CASHBACK");
                    Log::insert_earnings($slot_sponsor->slot_id,$cashback_money,"CASHBACK","CASHBACK",$slot_info->slot_id,"details");
                }
            } 
        }
    }
    //for seeding wag burahin
    public static function incentive_bonus()
    {
        
    }
    //
    public static function unilevel_or($slot_info,$points)
    {
        if($points != 0)
        {
           $insert["slot_id"] = $slot_info->slot_id;
           $insert["pv_points"] = $points;
           $insert["processed"] = 0;
           $insert["created_at"] = carbon::now();
           $insert["updated_at"] = carbon::now();
           Tbl_unilevel_or_points::insert($insert);

        }
    }

    public static function repurchase_commission($slot_info,$slot_id, $item_id)
    {
        $item = Tbl_item::where('item_id',$item_id)->first();
        if($item->item_type == 'product')
        {
            $rank_discount          = 0;
            $membership_discount    = 0;
            $discount               = 0;

            $rank       = Tbl_item_stairstep_rank_discount::where('stairstep_rank_id',$slot_info->slot_stairstep_rank)->where('item_id',$item_id)->first();
            $membership = Tbl_membership::ItemDiscount()->where('tbl_membership.membership_id',$slot_info->slot_membership)->where('item_id',$item_id)->first();


            $rank_discount          = $rank == null ? 0 : $rank->discount;
            $membership_discount    = $membership == null ? 0 : $membership->discount;


            if($rank_discount > $membership_discount)
            {
                $discount = $rank_discount;
            }
            else if($rank_discount < $membership_discount)
            {
                $discount = $membership_discount;
            }
            else if($rank_discount == $membership_discount && $membership_discount != 0)
            {
                $discount = $rank_discount;
            }


            $commission = $item->item_price * ($discount/100);

            $commission = round($commission,2);
            if($commission != 0.0 && $membership->enable_commission == 0)
            {
                Log::insert_wallet($slot_info->slot_id,$commission,"COMMISSION");
                Log::insert_earnings($slot_info->slot_id,$commission,"COMMISSION","COMMISION",$slot_id,"details");
            }
            
        }
    }

    public static function reward_points($slot_info, $points, $item_id = null) {
        $item = Tbl_item::where('item_id',$item_id)->first(); 
        $currency_id = Tbl_currency::where("currency_abbreviation", "UPTS")->first()->currency_id;
        if($points) {
            if($item->item_type == 'product') {
                Log::insert_wallet($slot_info->slot_id, $points, "UNILEVEL_POINTS", $currency_id);
            }
    
            $slot_tree = Tbl_tree_sponsor::where("sponsor_child_id",$slot_info->slot_id)->where("sponsor_parent_id", "!=", 1)->orderby("sponsor_level", "asc")->get();
    
            foreach($slot_tree as $key => $tree)
            {
                $slot_sponsor   = Tbl_slot::where("slot_id",$tree->sponsor_parent_id)->first();
                $unilevel_percentage = Tbl_membership_unilevel_level::where("membership_id",$slot_sponsor->slot_membership)->where("membership_entry_id",$slot_info->slot_membership)->where("membership_level",$tree->sponsor_level)->first();
                if($unilevel_percentage) {
                    $unilevel_pts = ($unilevel_percentage->membership_percentage/100) * $points;
                } else {
                    $unilevel_pts = 0;
                }
                if($unilevel_pts != 0)
                {
                    Log::insert_wallet($slot_info->slot_id, $points, "UNILEVEL_POINTS", $currency_id);
                }
            } 
        }
    }

    public static function incentive($slot_info, $points, $item_id) {
        // Fetch all products and their purchase counts for the given slot in one go
        $products = Tbl_item::where("item_type", "product")
            ->where("archived", 0)
            ->get()
            ->keyBy('item_id'); // Key by item_id for easier access
    
        $purchaseCounts = Tbl_incentive_purchase_count::where("slot_id", $slot_info->slot_id)
            ->whereIn("item_id", $products->keys())
            ->get()
            ->keyBy('item_id'); // Key by item_id for easier access
    
        $updates = [];
        $inserts = [];
        
        foreach ($products as $product) {
            $itemId = $product->item_id;
            $purchaseCount = $purchaseCounts->get($itemId);
    
            if ($itemId == $item_id) {
                // If the item_id matches, increment the purchase count
                if ($purchaseCount) {
                    // Increment the existing purchase count
                    $updates[] = [
                        'item_id' => $itemId,
                        'purchase_count' => $purchaseCount->purchase_count + 1,
                    ];
                } else {
                    // Prepare for insert with purchase count of 1
                    $inserts[] = [
                        'slot_id' => $slot_info->slot_id,
                        'item_id' => $itemId,
                        'purchase_count' => 1,
                    ];
                }
            } else {
                // If the item_id does not match, prepare for insert with purchase count of 0
                if (!$purchaseCount) {
                    $inserts[] = [
                        'slot_id' => $slot_info->slot_id,
                        'item_id' => $itemId,
                        'purchase_count' => 0,
                    ];
                }
            }
        }
    
        // Perform batch updates
        foreach ($updates as $update) {
            Tbl_incentive_purchase_count::where('slot_id', $slot_info->slot_id)
                ->where('item_id', $update['item_id'])
                ->update(['purchase_count' => $update['purchase_count']]);
        }
    
        // Perform batch inserts
        if (!empty($inserts)) {
            Tbl_incentive_purchase_count::insert($inserts);
        }
    }
}