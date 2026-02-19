<?php

namespace App\Http\Controllers\Admin;

use App\Globals\Log;
use App\Models\Tbl_claimed_incentive_items;
use App\Models\Tbl_currency;
use App\Models\Tbl_incentive_items;
use App\Models\Tbl_incentive_purchase_count;
use App\Models\Tbl_incentive_setup;
use App\Models\Tbl_slot;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminIncentiveController extends AdminController
{
    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'item_name' => 'required',
            'item_type' => 'required',
            'commission' => ['required_if:item_type,0', function ($attribute, $value, $fail) {
                if ($value !== null && $value <= 0) {
                    $fail('The commission field must be greater than zero.');
                }
            }],
        ], [
            'commission.required_if' => 'The commission field is required.',
        ]);
    

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }
        $item = new Tbl_incentive_items();
        $item->item_name = $request->item_name;
        $item->item_type = $request->item_type;
        $item->price = $request->price ?? 0;
        $item->commission =  $request->item_type == 0 ? $request->commission : 0;
        $item->thumbnail = $request->thumbnail;
        $item->created_at = Carbon::now();

        if($item->save())
        {
            $response['success'] = 1;
            $response['message']= 'New item is successfully added!';
            return response()->json($response);
        }
    }
    public function getData(Request $request)
    {
        if($request->type == 'all') {
            $records = Tbl_incentive_items::where('archive',0)->get();
        } else if ($request->type == 'archived') {
            $records = Tbl_incentive_items::where('archive',1)->get();
        }
        return response()->json($records);
    }
    public function show($id)
    {
        $record = Tbl_incentive_items::findOrFail($id);
        return response()->json($record);
    }
    public function update($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'item_name' => 'required',
            'item_type' => 'required',
            'commission' => ['required_if:item_type,0', function ($attribute, $value, $fail) {
                if ($value !== null && $value <= 0) {
                    $fail('The commission field must be greater than zero.');
                }
            }],
        ], [
            'commission.required_if' => 'The commission field is required.',
        ]);
        

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }

        $item = Tbl_incentive_items::findOrFail($id);
        $item->item_name = $request->item_name;
        $item->item_type = $request->item_type;
        $item->price = $request->price ?? 0;
        $item->commission =  $request->item_type == 0 ? $request->commission : 0;
        $item->thumbnail = $request->thumbnail;
        $item->updated_at = Carbon::now();
        
        if($item->save())
        {
            $response['success'] = 1;
            $response['message']= 'Item is successfully edited!';
            return response()->json($response);
        }
    }
    public function destroy($id)
    {
        $item = Tbl_incentive_items::findOrFail($id);
        $item->archive = 1;
        if($item->save())
        {
            $response['success'] = 1;
            $response['message']= 'Item is successfully deleted!';
            return response()->json($response);
        }
    }

    public function restore($id)
    {
        $item = Tbl_incentive_items::findOrFail($id);
        $item->archive = 0;
        if($item->save())
        {
            $response['success'] = 1;
            $response['message']= 'Item is successfully Restore!';
            return response()->json($response);
        }
    }

    public function list_of_claimed_redemption_items(Request $request)
    {

        $data = Tbl_claimed_incentive_items::leftjoin('tbl_incentive_items','tbl_incentive_items.item_id','tbl_claimed_incentive_items.reward_item')
        ->leftJoin('tbl_incentive_setup', 'tbl_incentive_setup.reward_item_id', '=', 'tbl_claimed_incentive_items.reward_item')
        ->leftJoin('tbl_item', 'tbl_item.item_id', '=', 'tbl_incentive_setup.item_id')
        ->leftJoin('tbl_slot', 'tbl_claimed_incentive_items.slot_id', '=', 'tbl_slot.slot_id')
        ->leftJoin('users', 'tbl_slot.slot_owner', '=', 'users.id')
        // ->leftJoin('tbl_address', 'users.id', '=', 'tbl_address.user_id')
        ->select('tbl_claimed_incentive_items.*','tbl_item.item_sku','tbl_incentive_items.item_name','tbl_incentive_items.item_type','users.name','users.email','users.contact','tbl_slot.slot_no');
        if (!empty($request->status)) {
            if($request->status === "For Approval") {
                $data->where('tbl_claimed_incentive_items.status', 'For Approval');
            }
            else if($request->status === "Approved") {
                $data->where('tbl_claimed_incentive_items.status', 'Approved');
            }
            else if($request->status === "Cancelled") {
                $data->where('tbl_claimed_incentive_items.status', 'Cancelled');
            }
        }
        $records = $data->orderByRaw("CASE WHEN tbl_claimed_incentive_items.status = 'For Approval' THEN 0 ELSE 1 END")
        ->orderBy('tbl_claimed_incentive_items.claimed_at')
        ->get();

        return response()->json($records);
    }

    public function claimed_redemption_item_change_status(Request $request)
    {
        $claimed_item = Tbl_claimed_incentive_items::findOrFail($request->claimed_id);

        if($request->status == 'approved')
        {
            $claimed_item->status = 'Approved';
            $claimed_item->approved_at = Carbon::now();
        } else {
            $claimed_item->status = 'Cancelled';
            $claimed_item->cancelled_at = Carbon::now();
        }
        
        $slot = Tbl_slot::findOrFail($claimed_item->slot_id);
        $incentive_setup = Tbl_incentive_setup::where('setup_id', $claimed_item->incentive_setup_id)->first();
       
        if ($incentive_setup) {
            // Fetch the direct count record
            $purchase_count_record = Tbl_incentive_purchase_count::where('item_id', $incentive_setup->item_id)
                ->where('slot_id', $claimed_item->slot_id) // Assuming you have slot_id in claimed_item
                ->first();
            $currency_id = Tbl_currency::where("currency_default",1)->first()->currency_id;
       
            if ($purchase_count_record) {
                if($request->status == 'approved')
                {
                    Log::insert_earnings($claimed_item->slot_id, $claimed_item->commission, "INCENTIVE","SLOT REPURCHASE", $claimed_item->slot_id, "", 0, $currency_id);
                    Log::insert_wallet($claimed_item->slot_id, $claimed_item->commission, "INCENTIVE", $currency_id);
                } else if($request->status == 'cancelled') {
                    $purchase_count_record->purchase_count += $claimed_item->purchase_count;
                }
                
                $purchase_count_record->save();
            }
        }
        $claimed_item->save();
        
        return response()->json(['success' => 1, 'message' => "Successfully {$claimed_item->status}!"]);
}
}
