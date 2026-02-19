<?php
namespace App\Http\Controllers\Member;

use App\Models\Tbl_slot;
use App\Models\Tbl_wallet_log;
use App\Models\Tbl_wallet;
use App\Models\Tbl_currency;

use App\Globals\Slot;
use App\Globals\Wallet;
use App\Globals\CashIn;

use Illuminate\Support\Facades\Request;

class MemberCashInController extends MemberController
{
    public function get_transactions()
    {
        $response = CashIn::get_transactions(Request::input(), Request::input('slot_id'));

        return response()->json($response);
    }

    public function record_cash_in()
    {
        $response = CashIn::record_cash_in(Request::input());

        return response()->json($response);
    }

    public function get_method_list() 
    {
        if(Request::input())
        {
            $response = CashIn::get_method_list(Request::input('category'), Request::input('currency'));
        }
        else
        {
            $response = CashIn::get_method_list(null, null, true);
        }

        return response()->json($response);
    }
}
