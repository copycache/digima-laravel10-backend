<?php
namespace App\Http\Controllers\Admin;

use App\Globals\Payout;
use App\Models\Tbl_cash_out_list;
use App\Models\Tbl_tree_sponsor;

use Illuminate\Support\Facades\Request;

class AdminPayoutController extends AdminController
{
    public function charge_settings()
    {
    	$response = Payout::add_settings(Request::input());

    	return response()->json($response);
    }

    public function get_charge_settings()
    {
    	$response = Payout::get_settings();

    	return response()->json($response);
    }

    public function payout_configuration()
    {
    	$response = Payout::payout_configuration(Request::input());

    	return response()->json($response);
    }

    public function cashout_receipt_data()
    {
        $cashout_id = Request::input('cashout_id');

        $return['cashout_details'] = Tbl_cash_out_list::where('cash_out_id', $cashout_id)->Method()->Slot()->first();
        $slot               = Tbl_cash_out_list::where('cash_out_id', $cashout_id)->Slot()->first();
        $return['direct']    = Tbl_tree_sponsor::where('sponsor_parent_id',$slot->slot_id)->where('sponsor_level', 1)->count();
        $return['indirect']    = Tbl_tree_sponsor::where('sponsor_parent_id',$slot->slot_id)->where('sponsor_level', '>', 1)->count();

        return response()->json($return);
    }
}
