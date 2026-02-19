<?php

namespace App\Http\Controllers\Admin;

use App\Globals\Plan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Tbl_reward_items;
use App\Models\Tbl_currency;
use App\Models\Tbl_membership;
use App\Models\Tbl_reward_points_settings;
use PhpParser\Node\Stmt\Label;
use Illuminate\Support\Facades\Validator;
class AdminRewardPointsController extends AdminController
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'item_name' => 'required',
            'commission' => [
                'required',
                function ($attribute, $value, $fail) {
                    if ((float) $value <= 0) {  // Ensure commission is greater than 0
                        $fail('The commission field must be greater than zero.');
                    }
                }
            ],
            'price' => 'required',
            'commission_upgraded' => [
                'required_if:is_upgrade_for_prime_refund,1',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->is_upgrade_for_prime_refund == 1) { // Only validate if required
                        if ($value === null || $value <= 0) {
                            $fail('The upgraded commission field must be greater than zero.');
                        }
                    }
                }
            ],
            'currency_id' => 'required',
            'membership_id' => 'exists:tbl_membership,membership_id',
        ], [
            'commission_upgraded.required_if' => 'The upgraded commission field is required.',
        ]);
        
        

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }

        $redemption_shop_item = new Tbl_reward_items;
        $redemption_shop_item->item_name = $request->item_name;
        $redemption_shop_item->commission = $request->commission;
        $redemption_shop_item->is_upgrade_for_prime_refund = $request->is_upgrade_for_prime_refund;
        $redemption_shop_item->item_name_upgraded = $request->item_name_upgraded;
        $redemption_shop_item->commission_upgraded = $request->commission_upgraded;
        $redemption_shop_item->price = $request->price;
        $redemption_shop_item->membership_id = $request->membership_id;
        $redemption_shop_item->currency_id = $request->currency_id;
        $redemption_shop_item->thumbnail = $request->thumbnail;
        $redemption_shop_item->created_at = Carbon::now();

        if($redemption_shop_item->save())
        {
            $response['success'] = 1;
            $response['message']= 'New redemption shop item is successfully added!';
            return response()->json($response);
        }
    }
    public function getData(Request $request)
    {
        // Determine the archive status based on the request type
        $archiveStatus = $request->type == 'archived' ? 1 : 0;

        // Eager load the currency and membership relationships
        $records = Tbl_reward_items::with(['currency', 'membership'])
            ->where('archive', $archiveStatus)
            ->get();

        // Map the records to include currency and membership details
        $records = $records->map(function ($item) {
            return [
                'id' => $item->id, // Assuming you have an id field
                'item_name' => $item->item_name,
                'price' => $item->price,
                'currency_name' => $item->currency->currency_name ?? null,
                'currency_abbreviation' => $item->currency->currency_abbreviation ?? null,
                'membership_name' => $item->membership->membership_name ?? null,
                'archive' => $item->archive,
                // Add other fields as necessary
            ];
        });

        return response()->json($records);
    }
    public function show($id)
    {
        $record = Tbl_reward_items::findOrFail($id);
        return response()->json($record);
    }
    public function update($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'item_name' => 'required',
            'commission' => [
                'required',
                function ($attribute, $value, $fail) {
                    if ((float) $value <= 0) {  // Ensure commission is greater than 0
                        $fail('The commission field must be greater than zero.');
                    }
                }
            ],
            'price' => 'required',
            'commission_upgraded' => [
                'required_if:is_upgrade_for_prime_refund,1',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->is_upgrade_for_prime_refund == 1) { // Only validate if required
                        if ($value === null || $value <= 0) {
                            $fail('The upgraded commission field must be greater than zero.');
                        }
                    }
                }
            ],
            'currency_id' => 'required',
            'membership_id' => 'exists:tbl_membership,membership_id',
        ], [
            'commission_upgraded.required_if' => 'The upgraded commission field is required.',
        ]);
        
        

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }

        $redemption_shop_item = Tbl_reward_items::findOrFail($id);
        $redemption_shop_item->item_name = $request->item_name;
        $redemption_shop_item->commission = $request->commission;
        $redemption_shop_item->is_upgrade_for_prime_refund = $request->is_upgrade_for_prime_refund;
        $redemption_shop_item->item_name_upgraded = $request->item_name_upgraded;
        $redemption_shop_item->commission_upgraded = $request->commission_upgraded;
        $redemption_shop_item->price = $request->price;
        $redemption_shop_item->currency_id = $request->currency_id;
        $redemption_shop_item->membership_id = $request->membership_id;
        $redemption_shop_item->thumbnail = $request->thumbnail;
        $redemption_shop_item->updated_at = Carbon::now();
        if($redemption_shop_item->save())
        {
            $response['success'] = 1;
            $response['message']= 'Item is successfully edited!';
            return response()->json($response);
        }
    }
    public function destroy($id)
    {
        $redemption_shop_item = Tbl_reward_items::findOrFail($id);
        $redemption_shop_item->archive = 1;
        if($redemption_shop_item->save())
        {
            $response['success'] = 1;
            $response['message']= 'Item is successfully deleted!';
            return response()->json($response);
        }
    }

    public function restore($id)
    {
        $redemption_shop_item = Tbl_reward_items::findOrFail($id);
        $redemption_shop_item->archive = 0;
        if($redemption_shop_item->save())
        {
            $response['success'] = 1;
            $response['message']= 'Item is successfully Restore!';
            return response()->json($response);
        }
    }

    public function get_settings()
    {
        $response["list_of_currency"] = Tbl_currency::where('currency_enable', 1)->get();
        $response["membership_list"] = Tbl_membership::where('archive', 0)->get();
        $response["label"] = Plan::get_label('REWARD_POINTS');

        return response()->json($response);
    }
}
