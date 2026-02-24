<?php

namespace App\Http\Controllers;

use App\Globals\Digima;
use App\Models\Tbl_cash_out_list;
use App\Models\Tbl_cash_out_schedule;
use App\Models\Tbl_earning_log;
use App\Models\Tbl_item;
use App\Models\Tbl_slot;
use App\Models\User;

class DigimaController extends FrontController
{
    public function index()
    {
        return view('front.home', [
            'Page' => 'Home',
            'services' => Tbl_item::where(['item_type' => 'non_inventory', 'archived' => 0, 'item_category' => 'services'])->get(),
            'property' => Tbl_item::where(['item_type' => 'non_inventory', 'archived' => 0, 'item_category' => 'property'])->get(),
            'other_product' => Tbl_item::where(['item_type' => 'non_inventory', 'archived' => 0, 'item_category' => 'other_product'])->get(),
            'product' => Tbl_item::where(['item_type' => 'product', 'archived' => 0])->get(),
        ]);
    }

    public function digima()
    {
        $member_count = User::count();
        $slot_count = Tbl_slot::where('membership_inactive', 0)->count();
        $total_pay_in = Tbl_slot::join('tbl_codes', 'tbl_codes.code_id', '=', 'tbl_slot.slot_used_code')
            ->join('tbl_inventory', 'tbl_inventory.inventory_item_id', '=', 'tbl_codes.code_inventory_id')
            ->join('tbl_item', 'tbl_item.item_id', '=', 'tbl_inventory.inventory_item_id')
            ->where('tbl_slot.slot_type', 'PS')
            ->sum('item_price');
        $total_pay_out = Tbl_earning_log::sum('earning_log_amount');
        
        Digima::updateStatistic($member_count, $slot_count, $total_pay_in, $total_pay_out);
        dd(compact('member_count', 'slot_count', 'total_pay_in', 'total_pay_out'));
    }
    public function resched()
    {
        Tbl_cash_out_schedule::each(function ($sched) {
            Tbl_cash_out_list::whereBetween('cash_out_date', [$sched->schedule_date_from, $sched->schedule_date_to])
                ->update(['schedule_id' => $sched->schedule_id]);
        });
    }
}
