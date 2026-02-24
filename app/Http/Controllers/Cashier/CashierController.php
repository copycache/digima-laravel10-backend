<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Tbl_codes;
use App\Models\Tbl_receipt;
use App\Models\Tbl_slot;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CashierController extends Controller
{

    public function get_cashier_info(Request $request)
    {
        $user = $request->user();
        $user->decrypted = Crypt::decryptString($user->crypt);
        return response()->json($user);
    }
    public function update_cashier_info(Request $request)
    {
        $user = $request->user();
        $message = 'success';
        $update = [];

        if ($request->input('email') != $user->email) {
            if (User::where('email', $request->input('email'))->exists()) {
                return response()->json('EMAIL ALREADY EXIST');
            }
            $update['email'] = $request->input('email');
        }

        $decrypted = Crypt::decryptString($user->crypt);
        if ($request->input('decrypted') != $decrypted) {
            $update['password'] = Hash::make($request->input('decrypted'));
            $update['crypt'] = Crypt::encryptString($request->input('decrypted'));
        }

        $update['first_name'] = $request->input('first_name');
        $update['last_name'] = $request->input('last_name');
        $update['gender'] = $request->input('gender');
        $update['birthdate'] = $request->input('birthdate');
        $update['name'] = $request->input('first_name') . ' ' . $request->input('last_name');

        User::where('id', $user->id)->update($update);

        return response()->json($message);
    }

    public function load_company_info()
    {
        return response()->json(DB::table('tbl_company_details')->first());
    }

    public function sales_receipt(Request $request)
    {
        $receipt_id = $request->input('id');

        $receipts = Tbl_receipt::where('receipt_id', $receipt_id)
            ->join('tbl_receipt_rel_item', 'tbl_receipt_rel_item.rel_receipt_id', '=', 'tbl_receipt.receipt_id')
            ->join('tbl_orders', 'tbl_orders.order_id', '=', 'tbl_receipt.receipt_order_id')
            ->join('tbl_item', 'tbl_item.item_id', '=', 'tbl_receipt_rel_item.item_id')
            ->join('tbl_cashier_payment_method', 'tbl_cashier_payment_method.cashier_payment_method_id', '=', 'tbl_receipt.payment_method')
            ->get();

        foreach ($receipts as $key => $receipt) {
            $slot_info = Tbl_slot::where('slot_id', $receipt->buyer_slot_id)->Owner()->first();
            $items = json_decode($receipt->items);
            $code_info = [];

            foreach ($items as $i => $item) {
                if ($key == $i) {
                    $codes = Tbl_codes::where('code_inventory_id', $item->item_id)
                        ->where('code_sold_to', $slot_info->id)
                        ->where('code_date_sold', $receipt->receipt_date_created)
                        ->get();

                    foreach ($codes as $j => $code) {
                        $code_info[$j] = [
                            'code' => $code->code_activation,
                            'pin' => $code->code_pin
                        ];
                    }
                }
            }

            $discounted_price = json_decode($receipt->discount);
            $receipts[$key]['code_info'] = $code_info;
            $receipts[$key]['slot'] = $slot_info;
            $receipts[$key]['discounted_price'] = $discounted_price[$key]->original_price - $discounted_price[$key]->percentage;
        }

        return response()->json($receipts);
    }
    
    public function get_receipt_details()
    {
        return response()->json(DB::table('tbl_receipt_details')->first());
    }
}
