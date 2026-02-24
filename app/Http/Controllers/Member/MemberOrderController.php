<?php
namespace App\Http\Controllers\Member;

use App\Globals\Item;
use App\Models\Cart;
use App\Models\Tbl_address;
use App\Models\Tbl_branch;
use App\Models\Tbl_inventory;
use App\Models\Tbl_item;
use App\Models\Tbl_receipt;
use App\Models\Tbl_slot;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class MemberOrderController extends MemberController
{
    public function latestOrder()
    {
        $latest = \DB::table('tbl_orders')->orderBy('order_id', 'desc')->first();
        return response()->json($latest);
    }

    public function get_orders()
    {
        $slot_id = Request::input('slot_id');
        $statuses = ['pending', 'delivered', 'completed', 'cancelled', 'pickup'];

        // Fetch buyer and address once
        $buyer = Tbl_slot::where('slot_id', $slot_id)->join('users', 'users.id', '=', 'tbl_slot.slot_owner')->first();
        $address = $buyer ? Tbl_address::Address()->where('user_id', $buyer->id)->where('is_default', 1)->first() : null;
        $default_address = $address ?? 'INVALID';

        // Fetch all orders for this slot
        $all_orders = DB::table('tbl_orders')
            ->where('buyer_slot_id', $slot_id)
            ->where('order_from', 'ecommerce')
            ->whereNotNull('order_status')
            ->orderBy('order_date_created', 'DESC')
            ->get();

        $order_ids = $all_orders->pluck('order_id');
        $receipts_map = Tbl_receipt::whereIn('receipt_id', $order_ids)->get()->keyBy('receipt_id');
        $branch_ids = $receipts_map->pluck('retailer')->unique()->filter();
        $branches_map = Tbl_branch::whereIn('branch_id', $branch_ids)->pluck('branch_location', 'branch_id');

        $all_item_ids = collect();
        foreach ($all_orders as $order) {
            $order_items = json_decode($order->items);
            if (is_array($order_items)) {
                foreach ($order_items as $it) {
                    $all_item_ids->push($it->item_id);
                }
            }
        }
        $items_map = Tbl_item::whereIn('item_id', $all_item_ids->unique())->get()->keyBy('item_id');

        // Pre-fetch ratings in bulk
        $ratings_map = DB::table('tbl_item_rating')
            ->whereIn('item_id', $all_item_ids->unique())
            ->where('user_id', $buyer->id)
            ->whereIn('item_rate_order_number', $all_orders->map(fn($o) => sprintf('%08d', $o->order_id)))
            ->get()
            ->groupBy(fn($r) => $r->item_id . '_' . $r->item_rate_order_number);

        $processed_orders = $all_orders->map(function ($value) use ($buyer, $default_address, $receipts_map, $branches_map, $items_map, $ratings_map) {
            $value->order_number = sprintf('%08d', $value->order_id);
            $value->buyer_info = $buyer;
            $value->default_address = $default_address;

            $receipt = $receipts_map->get($value->order_id);
            if ($receipt) {
                $receipt->branch_address = $branches_map->get($receipt->retailer);
            }
            $value->receipt = $receipt;

            $value->order_date_created_formatted = date('F j, Y g:ia', strtotime($value->order_date_created));
            $value->order_date_delivered_formatted = $value->order_date_delivered == null ? null : date('F j, Y g:ia', strtotime($value->order_date_delivered));

            $items = json_decode($value->items);
            $final_items = [];
            if (is_array($items)) {
                foreach ($items as $key2 => $value2) {
                    $item_data = $items_map->get($value2->item_id);
                    if ($item_data) {
                        $item_copy = clone $item_data;
                        $item_copy->item_price = $value2->discounted_price > 0 ? $value2->discounted_price : $item_data->item_price;
                        $item_copy->quantity = $value2->quantity;

                        $rating_key = $value2->item_id . '_' . $value->order_number;
                        $rating_info = $ratings_map->get($rating_key, collect())->first();

                        $item_copy->ratings = $rating_info ?: [
                            'item_rate' => 0,
                            'item_id' => $value2->item_id,
                            'user_id' => $buyer->id,
                            'item_review' => '',
                            'item_rate_order_number' => null,
                            'item_is_disabled' => 0
                        ];
                        $final_items[$key2] = $item_copy;
                    }
                }
            }
            $value->item = $final_items;
            return $value;
        });

        $orders = [];
        $orders['all'] = $processed_orders;
        $orders['all_count'] = $processed_orders->count();

        foreach ($statuses as $status) {
            $filtered = $processed_orders->where('order_status', $status)->values();
            $orders[$status] = $filtered;
            $orders[$status . '_count'] = $filtered->count();
        }

        return response()->json($orders, 200);
    }

    public function claim_code_claimed()
    {
        $receipt_id = Request::input('receipt_id');
        $claim_code = Request::input('claim_code');
        $response = Self::select_claim_codes($receipt_id, $claim_code);
        return response()->json($response);
    }

    public static function select_claim_codes($receipt_id, $claim_code = null)
    {
        if (isset($claim_code)) {
            $check_receipt = Tbl_receipt::where('receipt_id', $receipt_id)->where('claimed', 0)->first();

            if ($check_receipt) {
                $update['claimed'] = 1;

                Tbl_receipt::where('receipt_id', $receipt_id)->update($update);

                $update2['order_status'] = 'claimed';
                $update2['date_status_changed'] = Carbon::now();
                DB::table('tbl_orders')->where('order_id', $check_receipt->receipt_order_id)->update($update2);

                $return['status'] = 'success';
                $return['status_code'] = 200;
                $return['status_message'] = 'Order Received!';
            } else {
                $return['status'] = 'error';
                $return['status_code'] = 400;
                $return['status_message'] = 'Claim code either used or invalid!';
            }
        } else {
            $return = Tbl_receipt::where('receipt_id', $receipt_id)->first();

            $items = json_decode($return->items);
            $item_ids = collect($items)->pluck('item_id')->unique()->filter();
            $skus_map = Tbl_item::whereIn('item_id', $item_ids)->pluck('item_sku', 'item_id');

            $final_items = [];
            foreach ($items as $key => $value) {
                $final_items[$key] = (object) [
                    'item_sku' => $skus_map->get($value->item_id),
                    'quantity' => $value->quantity
                ];
            }

            $return['items'] = $final_items;
        }

        return $return;
    }

    public function addToCart(Request $request)
    {
        try {
            $items = Request::input('checkout_items');

            foreach ($items as $item) {
                // check if already exists
                $record = Cart::where('item_sku', $item['item_sku'])->first();

                if ($record) {
                    $cart = Cart::where('item_sku', $item['item_sku'])->first();
                } else {
                    $cart = new Cart;
                }

                $cart->slot_owner = Request::input('slot_owner');
                $cart->added_days = $item['added_days'];
                $cart->archived = $item['archived'];
                $cart->bind_membership_id = $item['bind_membership_id'];
                $cart->cashback_points = $item['cashback_points'];
                $cart->cashback_wallet = $item['cashback_wallet'];
                $cart->code_user = $item['code_user'];
                $cart->direct_cashback = $item['direct_cashback'];
                $cart->direct_cashback_membership = $item['direct_cashback_membership'];
                $cart->discounted_price = $item['discounted_price'];
                $cart->inclusive_gc = $item['inclusive_gc'];
                $cart->inventory_branch_id = $item['inventory_branch_id'];
                $cart->inventory_id = $item['inventory_id'];
                $cart->inventory_item_id = $item['inventory_item_id'];
                $cart->inventory_quantity = $item['inventory_quantity'];
                $cart->inventory_sold = $item['inventory_sold'];
                $cart->inventory_status = $item['inventory_status'];
                $cart->inventory_total = $item['inventory_total'];
                $cart->is_kit_upgrade = $item['is_kit_upgrade'];
                $cart->item_availability = $item['item_availability'];
                $cart->item_barcode = $item['item_barcode'];
                $cart->item_category = $item['item_category'];
                $cart->item_date_created = $item['item_date_created'];
                $cart->item_description = $item['item_description'];
                $cart->item_gc_price = $item['item_gc_price'];
                $cart->item_id = $item['item_id'];
                $cart->item_inventory_id = $item['item_inventory_id'];
                $cart->item_points_currency = $item['item_points_currency'];
                $cart->item_points_incetives = $item['item_points_incetives'];
                $cart->item_price = $item['item_price'];
                $cart->item_pv = $item['item_pv'];
                $cart->item_qty = $item['item_qty'];
                $cart->item_sku = $item['item_sku'];
                $cart->item_sub_category = $item['item_sub_category'];
                $cart->item_thumbnail = $item['item_thumbnail'];
                $cart->item_type = $item['item_type'];
                $cart->item_vortex_token = $item['item_vortex_token'];
                $cart->membership_id = $item['membership_id'];
                $cart->org_shipping_fee_lalamove = $item['org_shipping_fee_lalamove'];
                $cart->org_shipping_fee_ninja = $item['org_shipping_fee_ninja'];
                $cart->product_id = $item['product_id'];
                $cart->qty_charged = $item['qty_charged'];
                $cart->qty_fee_lalamove = $item['qty_fee_lalamove'];
                $cart->qty_fee_ninja_van = $item['qty_fee_ninja_van'];
                $cart->shipping_fee_lalamove = $item['shipping_fee_lalamove'];
                $cart->shipping_fee_ninja = $item['shipping_fee_ninja'];
                $cart->slot_qty = $item['slot_qty'];
                $cart->tag_as = $item['tag_as'];
                $cart->upgrade_own = $item['upgrade_own'];
                $cart->item_charged = $item['item_charged'];

                if ($cart->save()) {
                    return response()->json($item);
                }
            }
        } catch (\Exception $e) {
            return response()->json($e->getMessage());
        }
    }

    public function getCartItems()
    {
        $data = Request::input();
        $items = Cart::where('slot_owner', $data['slot_owner'])->leftjoin('tbl_branch', 'tbl_branch.branch_id', 'inventory_branch_id')->get();
        return response()->json($items);
    }

    public function deleteItem()
    {
        $item = Cart::where('id', Request::input('id'))->delete();
        return response()->json($item);
    }

    public function deleteAllItem()
    {
        $return = Cart::where('slot_owner', Request::input('slot_owner'))->delete();
        return response()->json($return);
    }

    /**
     * Simplified addToCart - accepts { product_id, quantity, slot_owner }
     * Auto-fills item details from tbl_item and tbl_inventory.
     */
    public function simpleAddToCart()
    {
        try {
            $product_id = Request::input('product_id');
            $quantity = Request::input('quantity') ?? 1;
            $slot_owner = Request::input('slot_owner');

            if (!$product_id || !$slot_owner) {
                return response()->json(['status' => 'error', 'message' => 'product_id and slot_owner are required.'], 400);
            }

            // Look up item details
            $item = Tbl_item::where('item_id', $product_id)->first();
            if (!$item) {
                return response()->json(['status' => 'error', 'message' => 'Product not found.'], 404);
            }

            // Look up inventory
            $inventory = Tbl_inventory::where('inventory_item_id', $product_id)->first();

            // Check if already in cart by item_sku
            $existing = Cart::where('item_sku', $item->item_sku)->where('slot_owner', $slot_owner)->first();
            $cart = $existing ? $existing : new Cart;

            $cart->slot_owner = $slot_owner;
            $cart->product_id = $product_id;
            $cart->item_id = $item->item_id;
            $cart->item_sku = $item->item_sku ?? '';
            $cart->item_barcode = $item->item_barcode ?? '';
            $cart->item_description = $item->item_description ?? '';
            $cart->item_price = $item->item_price ?? 0;
            $cart->item_gc_price = $item->item_gc_price ?? 0;
            $cart->item_pv = $item->item_pv ?? 0;
            $cart->item_thumbnail = $item->item_thumbnail ?? '';
            $cart->item_type = $item->item_type ?? 'product';
            $cart->item_category = $item->item_category ?? 0;
            $cart->item_sub_category = $item->item_sub_category ?? 0;
            $cart->item_availability = $item->item_availability ?? 1;
            $cart->item_date_created = $item->item_date_created ?? now();
            $cart->item_points_currency = $item->item_points_currency ?? 0;
            $cart->item_points_incetives = $item->item_points_incetives ?? 0;
            $cart->item_vortex_token = $item->item_vortex_token ?? 0;
            $cart->item_qty = $existing ? $existing->item_qty + $quantity : $quantity;
            $cart->item_charged = $item->item_price ?? 0;
            $cart->discounted_price = $item->discounted_price ?? 0;

            // Inventory fields
            $cart->inventory_id = $inventory->inventory_id ?? 0;
            $cart->inventory_item_id = $inventory->inventory_item_id ?? $product_id;
            $cart->inventory_branch_id = $inventory->inventory_branch_id ?? 1;
            $cart->inventory_quantity = $inventory->inventory_quantity ?? 0;
            $cart->inventory_sold = $inventory->inventory_sold ?? 0;
            $cart->inventory_status = $inventory->inventory_status ?? 'active';
            $cart->inventory_total = $inventory->inventory_total ?? 0;
            $cart->item_inventory_id = $inventory->inventory_id ?? 0;

            // Default values for optional fields
            $cart->added_days = $item->added_days ?? 0;
            $cart->archived = 0;
            $cart->bind_membership_id = $item->bind_membership_id ?? 0;
            $cart->cashback_points = $item->cashback_points ?? 0;
            $cart->cashback_wallet = $item->cashback_wallet ?? 0;
            $cart->code_user = 0;
            $cart->direct_cashback = $item->direct_cashback ?? 0;
            $cart->direct_cashback_membership = $item->direct_cashback_membership ?? 0;
            $cart->inclusive_gc = $item->inclusive_gc ?? 0;
            $cart->is_kit_upgrade = 0;
            $cart->membership_id = 0;
            $cart->org_shipping_fee_lalamove = 0;
            $cart->org_shipping_fee_ninja = 0;
            $cart->qty_charged = $quantity;
            $cart->qty_fee_lalamove = 0;
            $cart->qty_fee_ninja_van = 0;
            $cart->shipping_fee_lalamove = 0;
            $cart->shipping_fee_ninja = 0;
            $cart->slot_qty = $quantity;
            $cart->tag_as = $item->tag_as ?? '';
            $cart->upgrade_own = 0;

            $cart->save();

            return response()->json(['status' => 'success', 'message' => 'Item added to cart.', 'cart' => $cart]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Update cart item quantity
     */
    public function updateCartItem()
    {
        try {
            $id = Request::input('id');
            $quantity = Request::input('quantity');

            if (!$id || !$quantity) {
                return response()->json(['status' => 'error', 'message' => 'id and quantity are required.'], 400);
            }

            $cart = Cart::find($id);
            if (!$cart) {
                return response()->json(['status' => 'error', 'message' => 'Cart item not found.'], 404);
            }

            $cart->item_qty = $quantity;
            $cart->qty_charged = $quantity;
            $cart->slot_qty = $quantity;
            $cart->save();

            return response()->json(['status' => 'success', 'message' => 'Quantity updated.', 'cart' => $cart]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
