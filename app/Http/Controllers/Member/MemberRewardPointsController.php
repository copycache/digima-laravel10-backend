<?php

namespace App\Http\Controllers\Member;

use App\Globals\Log;
use App\Globals\Plan;
use App\Http\Controllers\Controller;
use App\Models\Tbl_added_redemption_wallet;
use App\Models\Tbl_claimed_redemption_item;
use App\Models\Tbl_claimed_reward_items;
use App\Models\Tbl_currency;
use App\Models\Tbl_prime_refund_points_log;
use App\Models\Tbl_redemption_shop_item;
use App\Models\Tbl_redemption_wallet_earnings;
use App\Models\Tbl_reward_items;
use App\Models\Tbl_reward_points_settings;
use App\Models\Tbl_slot;
use App\Models\Tbl_wallet;
use App\Models\Tbl_wallet_log;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Exception;

class MemberRewardPointsController extends MemberController
{
    public function claimedRewardItem(Request $request)
    {
        $result = ['success' => 1, 'message' => 'Successfully ordered!'];
        try {
            $item = Tbl_reward_items::findOrFail($request->item_id);
            $available_wallet = Tbl_wallet::where('slot_id', $request->slot_id)->where('currency_id', $item->currency_id)->first();

            if (!isset($request->slot_id)) {
                throw new Exception('Slot ID not found.');
            }

            if ($item->price > $available_wallet->wallet_amount) {
                throw new Exception('Sorry, you dont have enough balance.');
            }

            $running_balance = $available_wallet->wallet_amount - $item->price;

            $isUpgraded = $item->is_upgrade_for_prime_refund &&
                Tbl_prime_refund_points_log::where('slot_id', $request->slot_id)
                    ->where('status', 1)
                    ->exists();

            Tbl_wallet::where('slot_id', $request->slot_id)->where('currency_id', $item->currency_id)->update(['wallet_amount' => $running_balance]);
            $fields = [];
            $fields['slot_id'] = $request->slot_id;
            $fields['reward_item'] = $request->item_id;
            $fields['reward_commission'] = $isUpgraded ? $item->commission_upgraded : $item->commission;
            $fields['status'] = 'For Approval';
            $fields['membership_id'] = $item->membership_id;
            $fields['currency_id'] = $item->currency_id;
            $fields['reward_price'] = $item->price;
            $fields['upgrade_prime_reward'] = $isUpgraded;
            $fields['account_name'] = $request->account_name;
            $fields['account_number'] = $request->account_number;
            $fields['claimed_at'] = Carbon::now();
            Tbl_claimed_reward_items::insert($fields);

            // wallet log
            $reward_points_label = Plan::get_label('REWARD_POINTS');
            $wallet_log = new Tbl_wallet_log;
            $wallet_log->wallet_log_slot_id = $request->slot_id;
            $wallet_log->wallet_log_amount = '-' . $item->price;
            $wallet_log->wallet_log_details = $reward_points_label . ' Reward Points (Claimed)';
            $wallet_log->wallet_log_type = 'CREDIT';
            $wallet_log->wallet_log_running_balance = $running_balance;
            $wallet_log->wallet_log_date_created = Carbon::now();
            $wallet_log->currency_id = $item->currency_id;
            $wallet_log->save();
        } catch (Exception $e) {
            $result = ['success' => 0, 'message' => $e->getMessage()];
        }

        return response()->json($result);
    }

    public function getClaimedRewardItem(Request $request)
    {
        $id = $request->slot_id;

        $records = Tbl_claimed_reward_items::where('slot_id', $id)
            ->leftjoin('tbl_reward_items', 'tbl_reward_items.id', 'tbl_claimed_reward_items.reward_item')
            ->leftJoin('tbl_currency', 'tbl_reward_items.currency_id', '=', 'tbl_currency.currency_id')
            ->get();
        return response()->json($records);
    }

    public function list_of_claimed_redemption_items(Request $request)
    {
        $records = [];
        // dd(Tbl_claimed_reward_items::first());

        $data = Tbl_claimed_reward_items::leftjoin('tbl_reward_items', 'tbl_reward_items.id', 'tbl_claimed_reward_items.reward_item')
            ->leftJoin('tbl_slot', 'tbl_claimed_reward_items.slot_id', '=', 'tbl_slot.slot_id')
            ->leftJoin('users', 'tbl_slot.slot_owner', '=', 'users.id')
            ->leftJoin('tbl_address', 'users.id', '=', 'tbl_address.user_id')
            ->leftJoin('tbl_currency', 'tbl_claimed_reward_items.currency_id', '=', 'tbl_currency.currency_id')
            ->select('tbl_claimed_reward_items.*',
                'tbl_reward_items.item_name',
                'tbl_reward_items.item_name_upgraded',
                'tbl_reward_items.is_upgrade_for_prime_refund',
                'tbl_reward_items.price',
                'users.name', 'users.email',
                'users.contact',
                'tbl_address.additional_info',
                'tbl_slot.slot_no',
                'tbl_currency.currency_name',
                'tbl_currency.currency_abbreviation');

        if (!empty($request->status)) {
            if ($request->status === 'For Approval') {
                $data->where('tbl_claimed_reward_items.status', 'For Approval');
            } else if ($request->status === 'Approved') {
                $data->where('tbl_claimed_reward_items.status', 'Approved');
            } else if ($request->status === 'Cancelled') {
                $data->where('tbl_claimed_reward_items.status', 'Cancelled');
            }
        }

        $records = $data
            ->orderByRaw("CASE WHEN tbl_claimed_reward_items.status = 'For Approval' THEN 0 ELSE 1 END")
            ->orderBy('tbl_claimed_reward_items.claimed_at')
            ->get();

        return response()->json($records);
    }

    public function claimed_redemption_item_change_status(Request $request)
    {
        $claimed_item = Tbl_claimed_reward_items::findOrFail($request->claimed_id);

        $available_wallet = Tbl_wallet::where('slot_id', $claimed_item->slot_id)->where('currency_id', $claimed_item->currency_id)->first();

        if ($request->status == 'approved') {
            $claimed_item->status = 'Approved';
            $claimed_item->approved_at = Carbon::now();

            Log::insert_earnings($claimed_item->slot_id, $claimed_item->reward_commission, 'REWARD_POINTS', 'SLOT REPURHCASE', $claimed_item->slot_id, '', 1);
        } else {
            $claimed_item->status = 'Cancelled';
            $claimed_item->cancelled_at = Carbon::now();
            Tbl_wallet::where('slot_id', $claimed_item->slot_id)->where('currency_id', $claimed_item->currency_id)->update(['wallet_amount' => $available_wallet->wallet_amount + $claimed_item->reward_price]);

            // wallet log
            $reward_points_label = Plan::get_label('REWARD_POINTS');
            $wallet_log = new Tbl_wallet_log;
            $wallet_log->wallet_log_slot_id = $claimed_item->slot_id;
            $wallet_log->wallet_log_amount = '+' . $claimed_item->reward_price;
            $wallet_log->wallet_log_details = $reward_points_label . ' (Refunded)';
            $wallet_log->wallet_log_type = 'DEBIT';
            $wallet_log->wallet_log_running_balance = $available_wallet->wallet_amount + $claimed_item->reward_price;
            $wallet_log->wallet_log_date_created = Carbon::now();
            $wallet_log->currency_id = $claimed_item->currency_id;
            $wallet_log->save();
        }
        $claimed_item->proof_of_payment = $request->proof_of_payment;
        $claimed_item->save();

        return response()->json(['success' => 1, 'message' => "Successfully {$claimed_item->status}!"]);
    }

    public function get_item(Request $request)
    {
        $available_wallet = [];
        $slot = Tbl_slot::find($request->slot_id);
        $list_of_currency = Tbl_currency::where('currency_enable', 1)
            ->whereNotIn('currency_id', [1, 4])
            ->get();

        $currency_ids = $list_of_currency->pluck('currency_id')->unique()->filter();
        $wallets_map = Tbl_wallet::currency()
            ->where('slot_id', $request->slot_id)
            ->whereIn('tbl_currency.currency_id', $currency_ids)
            ->get()
            ->keyBy('currency_name');

        foreach ($list_of_currency as $currency) {
            $available_wallet[$currency->currency_name] = $wallets_map->get($currency->currency_name);
        }

        $items = Tbl_reward_items::where('archive', 0)
            ->whereNotNull('membership_id')
            ->where('membership_id', $slot->slot_membership)
            ->whereNotIn('currency_id', [1, 4])
            ->get();

        $isUpgraded = Tbl_prime_refund_points_log::where([
            ['slot_id', $slot->slot_id],
            ['status', 1]
        ])->exists();

        foreach ($items as $index => $list) {
            foreach ($list_of_currency as $currency) {
                if ($list->currency_id == $currency->currency_id) {
                    if ($list->price <= $available_wallet[$currency->currency_name]->wallet_amount) {
                        $items[$index]->status = 1;
                        $items[$index]->curreny_name = $currency->curreny_name;
                        $items[$index]->currency_abbreviation = $currency->currency_abbreviation;
                    } else {
                        $items[$index]->status = 0;
                        $items[$index]->curreny_name = $currency->curreny_name;
                        $items[$index]->currency_abbreviation = $currency->currency_abbreviation;
                    }
                    $items[$index]->is_upgraded = $isUpgraded;
                }
            }
        }
        $response['items'] = $items;
        $response['available_wallet'] = $available_wallet;

        return response()->json($response);
    }
}
