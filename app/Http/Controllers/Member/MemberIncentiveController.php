<?php

namespace App\Http\Controllers\Member;

use App\Models\Tbl_claimed_incentive_items;
use App\Models\Tbl_incentive_items;
use App\Models\Tbl_incentive_purchase_count;
use App\Models\Tbl_incentive_setup;
use App\Models\Tbl_item;
use App\Models\Tbl_slot;

use Illuminate\Http\Request;
use Exception;
use Carbon\Carbon;


class MemberIncentiveController extends MemberController
{
    public function get_item(Request $request)
    {   
        $id = $request->slot_id;
        $slot = Tbl_slot::findOrFail($id);
        
        // Fetch all items that are not archived
        $items = Tbl_incentive_items::where('archive', 0)->get();

        // Eager load the incentive setups only for existing items
        $incentive_setups = Tbl_incentive_setup::whereIn('reward_item_id', $items->pluck('item_id'))
            ->get()
            ->keyBy('reward_item_id');

        // Fetch direct counts for the slot
        $purchase_counts = Tbl_incentive_purchase_count::where("slot_id", $id)
            ->get()
            ->keyBy('item_id');

        // Filter items that are included in incentive setups
        $items = $items->filter(function ($item) use ($incentive_setups) {
            return $incentive_setups->has($item->item_id);
        });

        // Process each filtered item
        $items->transform(function ($item) use ($incentive_setups, $purchase_counts) {
            $incentive_setup = $incentive_setups->get($item->item_id);
            $purchase_count = $purchase_counts->get($incentive_setup->item_id);

            $item->purchase_count = $purchase_count ? $purchase_count->purchase_count : 0;
            $item->purchase_required = $incentive_setup->number_of_purchase;

            // Set the status based on the direct count
            $item->status = $item->purchase_count >= $item->purchase_required ? 1 : 0;

            // Fetch the product name
            $item->product_name = Tbl_item::where("item_id", $incentive_setup->item_id)->value("item_sku") ?? "Unknown";

            return $item;
        });

        return response()->json($items);
    }

    public function claimedRewardItem(Request $request)
    {
        $result = ['success' => 1, 'message' => 'Successfully ordered!'];
        try{
            if (!isset($request->slot_id)) {
                throw new Exception("Slot ID not found.");
            }
            
            // Fetch the slot and item
            $slot = Tbl_slot::findOrFail($request->slot_id);
            $item = Tbl_incentive_items::findOrFail($request->item_id);
            // Fetch lucky bonus setups for the relevant membership
            $incentive_setups = Tbl_incentive_setup::where('reward_item_id', $request->item_id)
                ->get()
                ->keyBy('reward_item_id');
            // Fetch direct counts for the slot and relevant membership entries
            $itemIds = $incentive_setups->pluck('item_id');
            $purchase_counts = Tbl_incentive_purchase_count::where("slot_id", $slot->slot_id)
                ->whereIn("item_id", $itemIds)
                ->get()
                ->keyBy('item_id');

            // Process the lucky bonus setup
            $incentive_setup = $incentive_setups->get($item->item_id);

            if (!$incentive_setup) {
                throw new Exception("Error, you can't claim this reward.");
            }
            
            // Get the direct count for the membership entry
            $purchase_count = $purchase_counts->get($incentive_setup->item_id);
            if (!$purchase_count) {
                throw new Exception("Error, you don't have enough balance.");
            }
            
            // Check if the user has enough balance to claim the reward
            if ($purchase_count->purchase_count < $incentive_setup->number_of_purchase) {
                throw new Exception("Error, you don't have enough balance.");
            }
            
            // Update the direct count
            $updated_count = $purchase_count->purchase_count - $incentive_setup->number_of_purchase;

            Tbl_incentive_purchase_count::where('slot_id', $request->slot_id)
                ->where('item_id', $incentive_setup->item_id)
                ->update(['purchase_count' => $updated_count]);
            
            $fields = [];
            $fields['slot_id'] = $request->slot_id;
            $fields['reward_item'] = $request->item_id;
            $fields['incentive_setup_id'] = $incentive_setup->setup_id;
            $fields['purchase_count'] = $incentive_setup->number_of_purchase;
            $fields['commission'] = $item->type == 0 ? $item->commission : 0;
            $fields['status'] = 'For Approval';
            $fields['claimed_at'] = Carbon::now();
            Tbl_claimed_incentive_items::insert($fields);

        } catch (Exception $e)
        {
            $result = ['success' => 0, 'message' => $e->getMessage()];
        }

        return response()->json($result);
      
    }

    public function getClaimedRewardItem(Request $request)
    {
        $id = $request->slot_id;

        $records = Tbl_claimed_incentive_items::where('slot_id',$id)
            ->leftjoin('tbl_incentive_items','tbl_incentive_items.item_id','tbl_claimed_incentive_items.reward_item')
            ->get();
            
        return response()->json($records);
    }
}
