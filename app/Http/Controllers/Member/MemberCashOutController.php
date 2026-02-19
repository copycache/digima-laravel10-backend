<?php
namespace App\Http\Controllers\Member;

use App\Models\Tbl_slot;
use App\Models\Tbl_wallet_log;
use App\Models\Tbl_wallet;
use App\Models\Tbl_currency;

use App\Globals\Slot;
use App\Globals\Wallet;
use App\Globals\CashOut;

use Illuminate\Support\Facades\Request;

class MemberCashOutController extends MemberController
{
    public function record_cash_out()
    {
        // dd(Request::input());
        $response = CashOut::record_cash_out(Request::input());

        return response()->json($response);
    }
    public function check_if_initial_payout()
    {
        $response = CashOut::check_if_initial_payout(Request::input());
        return response()->json($response);
    }

    public function get_slot_wallet()
    {
        // $currency = Tbl_currency::where('currency_abbreviation', Request::input('currency'))->first()->currency_id;

        // $wallet = Tbl_wallet::where('slot_id',Request::input('slot_id'))->where('currency_id', $currency)->first();

        // return response()->json($wallet);
        $data = Request::input('data');
        // dd($data);
        $currency = Tbl_currency::where('currency_abbreviation', Request::input('currency'))->first()->currency_id;

        // $wallet=[];
        // $wallet = Tbl_wallet::where('slot_id',Request::input('slot_id'))->where('currency_id', $currency)->first();

        $slots         = Tbl_slot::where("tbl_slot.slot_id",Request::input('slot_id'))->Owner()->wallet($currency)->get();

		// $wallet['current_slot_wallet'] = Tbl_slot::where("tbl_slot.slot_id",Request::input('slot_id'))->wallet($currency)->first()->wallet_amount;
        // $wallet['all_slot_wallet']     = Tbl_slot::where("tbl_slot.slot_owner",$owner_id->slot_owner)->wallet($currency)->sum('wallet_amount');
        $multi=[];
        $ctr = 0;
        $multi['total'] = 0;
        //check current slot
        // if($owner_id->initial_payout == 1) {

        //     if($owner_id->wallet_amount >= $data['initial_payout']) {

        //         $multi['total'] = $multi['total'] + $owner_id->wallet_amount;
        //         $multi['slots'][$ctr] = $owner_id;
        //         $ctr++;
        //     }
        // }
        // else {
            
        //     if($owner_id->wallet_amount >= $data['minimum_payout']) {

        //         $multi['total'] = $multi['total'] + $owner_id->wallet_amount;
        //         $multi['slots'][$ctr] = $owner_id;
        //         $ctr++;
        //     }
        // }
        // ->where("tbl_slot.slot_id",'!=',$owner_id->slot_id)
        // $slots = Tbl_slot::where("tbl_slot.slot_owner",$owner_id->slot_owner)->wallet($currency)->get();
        foreach ($slots as $key => $x) {
         
            if($x->initial_payout == 1) {

                if($x->wallet_amount < $data['initial_payout']) {
                    $x->is_initial_valid = false ;
                    $multi['slots'][$ctr] = $x;
                    $ctr++;
                    // $slots->forget($key);
                }
                else {
                    $x->is_initial_valid = true ;
                    $multi['total'] = $multi['total'] + $x->wallet_amount;
                    $multi['slots'][$ctr] = $x;
                    $ctr++;
                }

                // $x->slot_wallet = 
                
            }
            else {
                
                if($x->wallet_amount < $data['minimum_payout']) {
                    // $slots->forget($key);
                    $x->is_minimum_valid = false ;
                    $multi['slots'][$ctr] = $x;
                    $ctr++;
                    // $multi['slots'][$ctr] = $x;
                }
                else {
                    $x->is_minimum_valid = true ;
                    $multi['total'] = $multi['total'] + $x->wallet_amount;
                    $multi['slots'][$ctr] = $x;
                    $ctr++;
                }
            }
            // dd($x);
        }
        // $multi['all_slot'] = $slots;
        return response()->json($multi);

    }
}
