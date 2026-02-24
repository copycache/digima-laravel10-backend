<?php
namespace App\Http\Controllers\Member;

use App\Globals\Code;
use App\Globals\Item;
use App\Globals\Member;
use App\Globals\Product;
use App\Globals\Slot;
use App\Models\Tbl_code_transfer_logs;
use App\Models\Tbl_codes;
use App\Models\Tbl_item;
use App\Models\Tbl_membership;
use App\Models\Tbl_slot;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class MemberCodeController extends MemberController
{
    public function get_member_codes()
    {
        $user_id = Request::input('user_id');
        $filter = Request::input('filter');
        $response = Code::get_member_codes($user_id, $filter);

        return response()->json($response);
    }

    public function get_claim_codes()
    {
        $slot_id = Request::input('slot_id');
        $response = Code::get_claim_codes($slot_id);

        return response()->json($response);
    }

    public function get_filters()
    {
        $return = Tbl_membership::get();
        return response()->json($return);
    }

    public function load_product_code()
    {
        $slot_id = Request::input('slot_id');
        $user_id = Request::user()->id;
        $item = Request::input('item');
        $search = Request::input('search');
        $membership = Request::input('filter');

        $code_list = Tbl_codes::where('item_type', 'product')
            ->where('code_sold_to', $user_id)
            ->Inventory()
            ->InventoryItem()
            ->CheckIfArchived()
            ->leftJoin('tbl_orders', function ($join) {
                $join
                    ->on('tbl_orders.order_date_created', '=', 'tbl_codes.code_date_sold')
                    ->orWhereRaw('ABS(TIMESTAMPDIFF(SECOND, tbl_orders.order_date_created, tbl_codes.code_date_sold)) <= 5');
            })
            ->where('tbl_orders.order_status', 'claimed')
            ->select(
                'tbl_codes.*',
                'tbl_inventory.*',
                'tbl_item.*',
                'tbl_orders.order_id',
                'tbl_orders.order_status'
            )
            ->orderByRaw('CASE WHEN code_date_used IS NOT NULL THEN 1 ELSE 0 END ASC')
            ->orderBy('code_date_used', 'desc')
            ->orderBy('code_date_sold', 'desc');

        if (isset($item) && $item != 'all') {
            $code_list = $code_list->where('item_id', $item);
        }

        if (isset($search)) {
            $code_list = $code_list->Search($search);
        }
        // $count_unused = $code_list->where("code_slot_used",null)->count();
        $code_list = $code_list->paginate(15);

        $item_list = Tbl_item::where('item_type', 'product')->where('archived', 0)->get();

        $return['item_list'] = $item_list;
        $return['code_list'] = $code_list;
        $return['total_unused'] = Tbl_codes::where('code_sold_to', $user_id)
            ->Inventory()
            ->InventoryItem()
            ->CheckIfArchived()
            ->CheckIfProduct()
            ->leftJoin('tbl_orders', function ($join) {
                $join
                    ->on('tbl_orders.order_date_created', '=', 'tbl_codes.code_date_sold')
                    ->orWhereRaw('ABS(TIMESTAMPDIFF(SECOND, tbl_orders.order_date_created, tbl_codes.code_date_sold)) <= 5');
            })
            ->where('tbl_orders.order_status', 'claimed')
            ->where('code_used', 0)
            ->count();
        $return['total_used'] = Tbl_codes::where('code_sold_to', $user_id)
            ->Inventory()
            ->InventoryItem()
            ->CheckIfArchived()
            ->CheckIfProduct()
            ->leftJoin('tbl_orders', function ($join) {
                $join
                    ->on('tbl_orders.order_date_created', '=', 'tbl_codes.code_date_sold')
                    ->orWhereRaw('ABS(TIMESTAMPDIFF(SECOND, tbl_orders.order_date_created, tbl_codes.code_date_sold)) <= 5');
            })
            ->where('tbl_orders.order_status', 'claimed')
            ->where('code_used', 1)
            ->count();

        // dd($user_id);
        return response()->json($return);
    }

    public function load_membership_code()
    {
        $slot_id = Request::input('slot_id');
        $user_id = Request::user()->id;
        // $response   = Code::load_membership_code($user_id,$slot_id);/

        $code_list = Tbl_codes::where('tbl_item.item_type', 'membership_kit')
            ->where('code_sold_to', $user_id)
            ->Inventory()
            ->InventoryItem()
            ->InventoryItemMembership()
            ->CheckIfArchived()
            ->leftJoin('tbl_orders', function ($join) {
                $join
                    ->on('tbl_orders.order_date_created', '=', 'tbl_codes.code_date_sold')
                    ->orWhereRaw('ABS(TIMESTAMPDIFF(SECOND, tbl_orders.order_date_created, tbl_codes.code_date_sold)) <= 5');
            })
            ->where('tbl_orders.order_status', 'claimed')
            ->select(
                'tbl_codes.*',
                'tbl_inventory.*',
                'tbl_item.*',
                'tbl_membership.*',
                'tbl_orders.order_id',
                'tbl_orders.order_status'
            )
            ->orderByRaw('CASE WHEN code_date_used IS NOT NULL THEN 1 ELSE 0 END ASC')
            ->orderBy('code_date_used', 'desc')
            ->orderBy('code_date_sold', 'desc');

        $membership = Request::input('filter');
        $status = Request::input('status');
        if (isset($membership) && $membership != 'all') {
            $code_list->where('tbl_membership.membership_id', $membership);
        }

        if (Request::input('search')) {
            $code_list->Search2(Request::input('search'));
        }
        if (isset($status) && $status != 'all') {
            $code_list->where('tbl_codes.code_used', $status);
        }
        $return['membership_list'] = Tbl_membership::where('archive', 0)->get();
        $return['code_list'] = $code_list->paginate(15);
        $query = Tbl_codes::where('code_sold_to', $user_id)->Inventory()->InventoryItem()->InventoryItemMembership()->where('tbl_item.item_type', 'membership_kit')->CheckIfArchived();
        $return['total_unused'] = Tbl_codes::where('code_sold_to', $user_id)
            ->Inventory()
            ->InventoryItem()
            ->InventoryItemMembership()
            ->CheckIfKit()
            ->CheckIfArchived()
            ->leftJoin('tbl_orders', function ($join) {
                $join
                    ->on('tbl_orders.order_date_created', '=', 'tbl_codes.code_date_sold')
                    ->orWhereRaw('ABS(TIMESTAMPDIFF(SECOND, tbl_orders.order_date_created, tbl_codes.code_date_sold)) <= 5');
            })
            ->where('tbl_orders.order_status', 'claimed')
            ->select(
                'tbl_codes.*',
                'tbl_inventory.*',
                'tbl_item.*',
                'tbl_membership.*',
                'tbl_orders.order_id',
                'tbl_orders.order_status'
            )
            ->where('code_used', 0)
            ->count();

        $return['total_used'] = Tbl_codes::where('code_sold_to', $user_id)
            ->Inventory()
            ->InventoryItem()
            ->InventoryItemMembership()
            ->CheckIfKit()
            ->CheckIfArchived()
            ->leftJoin('tbl_orders', function ($join) {
                $join
                    ->on('tbl_orders.order_date_created', '=', 'tbl_codes.code_date_sold')
                    ->orWhereRaw('ABS(TIMESTAMPDIFF(SECOND, tbl_orders.order_date_created, tbl_codes.code_date_sold)) <= 5');
            })
            ->where('tbl_orders.order_status', 'claimed')
            ->select(
                'tbl_codes.*',
                'tbl_inventory.*',
                'tbl_item.*',
                'tbl_membership.*',
                'tbl_orders.order_id',
                'tbl_orders.order_status'
            )
            ->where('code_used', 1)
            ->count();

        $used_by_ids = collect($return['code_list']->items())->where('code_used', 1)->pluck('code_used_by')->unique()->filter();
        $users_map = User::whereIn('id', $used_by_ids)->pluck('name', 'id');

        foreach ($return['code_list'] as $key => $value) {
            if ($value->code_used == 1) {
                $return['code_list'][$key]['name'] = $users_map->get($value->code_used_by);
            }
        }

        return response()->json($return);
    }

    public function load_transfer_history_code()
    {
        // dd(Request::input());
        $slot_id = Request::input('slot_id') ? Request::input('slot_id') : null;
        $search = Request::input('search');
        if ($search == 'undefined') {
            $search = null;
        }

        $date_from = Request::input('date_from');
        $date_to = Request::input('date_to');

        // if($date_from == null || $date_to == null || $date_from == 'undefined' || $date_to == 'undefined')
        // {
        // 	$date_from 	= Carbon::now()->format('Y-m-d');
        // 	$date_to   	= Carbon::now()->format('Y-m-d');
        // }

        $query = Tbl_code_transfer_logs::leftJoin('tbl_codes', 'tbl_codes.code_id', '=', 'tbl_code_transfer_logs.code_id')
            ->leftJoin('tbl_inventory', 'tbl_inventory.inventory_id', '=', 'tbl_codes.code_inventory_id')
            ->leftJoin('tbl_item', 'tbl_item.item_id', '=', 'tbl_inventory.inventory_item_id')
            ->select('tbl_code_transfer_logs.code_id', 'from_slot', 'to_slot', 'original_slot', 'date_transfer', 'code_activation', 'code_pin', 'item_sku', 'item_type');

        if ($search != '' || $search != null) {
            $search2 = Tbl_slot::where('slot_no', $search)->first() ? Tbl_slot::where('slot_no', $search)->first()->slot_id : null;
            // dd($search2);
            if ($search2 != '' || $search2 != null) {
                $query->where('from_slot', 'like', '%' . $search2 . '%')->orwhere('to_slot', 'like', '%' . $search2 . '%')->orwhere('original_slot', 'like', '%' . $search2 . '%');
            } else {
                $query->where('tbl_codes.code_activation', 'like', '%' . $search . '%');
            }
        }
        if ($slot_id != '' || $slot_id != null) {
            $query->where('from_slot', '=', $slot_id);
        }
        // lazy fixing; baligtad talaga yan, kasi baligtad yung sa html
        if ($date_from) {
            $query->whereDate('date_transfer', '<=', $date_from);
        }
        if ($date_to) {
            $query->whereDate('date_transfer', '>=', $date_to);
        }
        $query->orderBy('date_transfer', 'DESC');

        $response = $query->paginate(15);
        $response = $query->paginate(15);

        $slot_ids = collect($response->items())
            ->pluck('from_slot')
            ->merge(collect($response->items())->pluck('to_slot'))
            ->merge(collect($response->items())->pluck('original_slot'))
            ->unique()
            ->filter();
        $slots_map = Tbl_slot::whereIn('slot_id', $slot_ids)->pluck('slot_no', 'slot_id');

        foreach ($response as $key => $value) {
            if ($response[$key]['item_type'] == 'membership_kit') {
                $response[$key]['kit'] = 'Membership Kit';
            } else if ($response[$key]['item_type'] == 'product') {
                $response[$key]['kit'] = 'Product Kit';
            } else {
                $response[$key]['kit'] = 'Service Kit';
            }
            $response[$key]['from_slot_code'] = $slots_map->get($value->from_slot);
            $response[$key]['to_slot_code'] = $slots_map->get($value->to_slot);
            $response[$key]['original_slot_code'] = $slots_map->get($value->original_slot);
        }

        // dd($response);

        return response()->json($response);
    }

    public function bulk_membership_transfer()
    {
        $data = Request::input('code_list');

        foreach ($data['data'] as $key => $value) {
            if (isset($value['checked'])) {
                if ($value['checked'] == true) {
                    $user_id = Request::user()->id;
                    $code_id = $value['code_id'];

                    $check_if_owned = Tbl_codes::where('code_id', $code_id)->where('code_sold_to', $user_id)->where('code_used', 0)->first();
                    if (!$check_if_owned) {
                        $return['status'] = 'error';
                        $return['status_code'] = 400;
                        $return['status_message'] = 'One of the codes cannot be transferred, please try to refresh your browser.';

                        return response()->json($return);
                    }
                }
            }
        }

        foreach ($data['data'] as $key => $value) {
            if (isset($value['checked'])) {
                if ($value['checked'] == true) {
                    // $checked[$count] = $value;
                    // $count = $count + 1;
                    $user_id = Request::user()->id;
                    $code_id = $value['code_id'];
                    $transfer_from = Request::input('transfer_from');
                    $transfer_to = Request::input('transfer_to');
                    $slot_to = Tbl_slot::where('slot_id', $transfer_to)->first();

                    $check_if_owned = Tbl_codes::where('code_id', $code_id)->where('code_sold_to', $user_id)->where('code_used', 0)->first();
                    if ($check_if_owned) {
                        $check_if_first_log = DB::table('tbl_code_transfer_logs')->where('code_id', $code_id)->first();

                        $update_code['code_sold_to'] = $slot_to->slot_owner;
                        DB::table('tbl_codes')->where('code_id', $code_id)->update($update_code);

                        $insert_log['code_id'] = $code_id;
                        $insert_log['from_slot'] = $transfer_from;
                        $insert_log['to_slot'] = $transfer_to;
                        $insert_log['original_slot'] = $check_if_first_log ? $check_if_first_log->original_slot : $transfer_from;
                        $insert_log['date_transfer'] = Carbon::now();

                        $insert = DB::table('tbl_code_transfer_logs')->insertGetId($insert_log);

                        if (is_numeric($insert)) {
                            $return['status'] = 'success';
                            $return['status_code'] = 201;
                            $return['status_message'] = 'Code Transferred';
                        }
                    }
                }
            }
        }

        if (!isset($return)) {
            $return['status'] = 'error';
            $return['status_code'] = 400;
            $return['status_message'] = 'An Error Occurred.';
        }
        return response()->json($return);
    }

    public function bulk_membership_use()
    {
        $data = Request::input('code_list');
        $sponsor_code = Request::input('sponsor');
        $owner_details = Request::input('owner');
        $validation = Self::code_validation($data['data']);
        if ($validation == 0) {
            foreach ($data['data'] as $key => $value) {
                if (isset($value['checked'])) {
                    if ($value['checked'] == true) {
                        $slot_info = Tbl_slot::where('slot_owner', Request::user()->id)->where('slot_id', $owner_details)->first();
                        if ($slot_info) {
                            $data['slot_owner'] = $slot_info->slot_owner;
                            $data['code'] = $value['code_activation'];
                            $data['pin'] = $value['code_pin'];
                            $data['slot_sponsor'] = $sponsor_code;

                            $slot_response = Slot::create_slot($data);
                            $return = $slot_response;
                        }
                    }
                }
            }
        } else {
            $return['status'] = 'error';
            $return['status_code'] = 400;
            $return['status_message'] = 'One of the codes is already used, please try to refresh your browser.';
        }

        return $return;
    }

    public function bulk_membership_add_member()
    {
        $code_list = Request::input('code_list');
        $sponsor_code = Request::input('sponsor');
        $owner_details = Request::input('owner');
        $owner_details['register_platform'] = 'system';
        $owner_details['country_id'] = 1;
        $validation = Self::code_validation($code_list['data']);
        if ($validation == 0) {
            $user_response = Member::add_member($owner_details);
            foreach ($code_list['data'] as $key => $value) {
                if (isset($value['checked'])) {
                    if ($value['checked'] == true) {
                        if ($user_response['status'] == 'success') {
                            $data['slot_owner'] = $user_response['status_data_id'];
                            $data['code'] = $value['code_activation'];
                            $data['pin'] = $value['code_pin'];
                            $data['slot_sponsor'] = $sponsor_code;

                            $slot_response = Slot::create_slot($data);

                            $return = $slot_response;
                        } else {
                            $return = $user_response;
                        }
                    }
                }
            }
        } else {
            $return['status'] = 'error';
            $return['status_code'] = 400;
            $return['status_message'] = 'One of the codes is already used, please try to refresh your browser.';
        }

        return response()->json($return);
    }

    public static function code_validation($data)
    {
        $user_id = Request::user()->id;
        $checked_code_ids = collect($data)->where('checked', true)->pluck('code_id')->unique()->filter();

        if ($checked_code_ids->isEmpty()) {
            return 0;
        }

        $owned_count = Tbl_codes::whereIn('code_id', $checked_code_ids)
            ->where('code_sold_to', $user_id)
            ->where('code_used', 0)
            ->count();

        return $checked_code_ids->count() - $owned_count;
    }

    public static function bulk_use_product_code()
    {
        $data = Request::input('code_list');
        $owner_details = Request::input('owner');
        $validation = Self::code_validation($data['data']);
        if ($validation == 0) {
            foreach ($data['data'] as $key => $value) {
                if (isset($value['checked'])) {
                    if ($value['checked'] == true) {
                        $slot_info = Tbl_slot::where('slot_owner', Request::user()->id)->where('slot_id', $owner_details)->first();
                        if ($slot_info) {
                            $data['pin'] = $value['code_pin'];
                            $data['code'] = $value['code_activation'];
                            $data['slot_id'] = $slot_info->slot_id;
                            $data['slot_owner'] = Request::user()->id;
                            $response = Product::activate_code($data);
                            $return = $response;
                        }
                    }
                }
            }
        } else {
            $return['status'] = 'error';
            $return['status_code'] = 400;
            $return['status_message'] = 'One of the codes is already used, please try to refresh your browser.';
        }

        return response()->json($return);
    }

    public function get_user_membership()
    {
        $slot_id = Request::input('slot_id');
        $response = Tbl_slot::where('slot_id', $slot_id)->leftJoin('tbl_membership', 'tbl_membership.membership_id', 'tbl_slot.slot_membership')->first();

        return $response;
    }

    public function get_own_slot_list()
    {
        $slot_owner = Request::user()->id;
        // $search                      = Request::input('search');
        $response = Tbl_slot::where('slot_owner', $slot_owner)->leftJoin('tbl_membership', 'tbl_membership.membership_id', 'tbl_slot.slot_membership');

        // if($search)
        // {
        //     $response = $response->where("slot_no", "like", "%". $search . "%");
        // }

        $response = $response->get();

        return $response;
    }
}
