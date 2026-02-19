<?php
namespace App\Http\Controllers;

use App\Http\Controllers\FrontController;
use Carbon\Carbon;
use App\Globals\Digima;
use App\Models\Tbl_slot;
use App\Models\Tbl_item;
use App\Models\Tbl_earning_log;
use App\Models\Users;
use App\Models\Tbl_cart;
use App\Models\Tbl_cash_out_schedule;
use App\Models\Tbl_cash_out_list;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

use App\Globals\Cart;

class DigimaController extends FrontController
{
    // ECOMMERCE WEBSITE - Rommel
    public function index()
    {
        $data["Page"]       = "Home";
        $data['services']   = Tbl_item::where('item_type','non_inventory')->where('archived',0)->where('item_category','services')->get();
        $data['property']   = Tbl_item::where('item_type','non_inventory')->where('archived',0)->where('item_category','property')->get();
        $data['other_product']   = Tbl_item::where('item_type','non_inventory')->where('archived',0)->where('item_category','other_product')->get();
        $data['product']    = Tbl_item::where('item_type','product')->where('archived',0)->get();
        return view ("front.home", $data);
    }

    public function digima()
    {
        $data["member_count"] = $member_count = Users::count();
        $data["slot_count"] = $slot_count = Tbl_slot::where("membership_inactive", 0)->count();
        $data["total_pay_in"] = $total_pay_in = Tbl_slot::join('tbl_codes','tbl_codes.code_id', '=', 'tbl_slot.slot_used_code')->join('tbl_inventory', 'tbl_inventory.inventory_item_id', '=', 'tbl_codes.code_inventory_id')->join('tbl_item', 'tbl_item.item_id', '=', 'tbl_inventory.inventory_item_id')->where('tbl_slot.slot_type', 'PS')->sum('item_price');
        $data["total_pay_out"] = $total_pay_out = Tbl_earning_log::sum('earning_log_amount');
        Digima::updateStatistic($member_count, $slot_count, $total_pay_in, $total_pay_out);
        dd($data);
    }
    public function resched()
    {
        $re_sched = tbl_cash_out_schedule::get();
        foreach($re_sched as $key => $sched) 
        {
            Tbl_cash_out_list::whereDate('cash_out_date','>=',$sched->schedule_date_from)
                                ->whereDate('cash_out_date','<=',$sched->schedule_date_to)
                                ->update(['schedule_id' => $sched->schedule_id]);
        }

    }
}
