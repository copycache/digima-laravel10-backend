<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;
use DB;
class Tbl_cash_out_list extends Model
{
    use HasFactory;

	protected $table = 'tbl_cash_out_list';
	protected $primaryKey = "cash_out_id";
    public $timestamps = false;

    public function scopeMethod($query)
    {
        $query->select(DB::raw('tbl_cash_out_list.cash_out_method_service_charge as service_charge, tbl_cash_out_list.gc_charge as cashout_gc_charge, tbl_cash_out_list.survey_charge as cashout_survey_charge, tbl_cash_out_list.product_charge as cashout_product_charge, tbl_cash_out_list.*,tbl_cash_out_method.*'));
        $query->join('tbl_cash_out_method', 'tbl_cash_out_method.cash_out_method_id', '=', 'tbl_cash_out_list.cash_out_method_id');
        return $query;
    }

    public function scopeSlot($query)
    {
    	return $query->join('tbl_slot', 'tbl_slot.slot_no', '=', 'tbl_cash_out_list.cash_out_slot_code');
    }
    
    public function scopeGetWeekCashOut($query)
    {
        $query  ->where('cash_out_date', '>', Carbon::now()->startOfWeek())
                ->where('cash_out_date', '<', Carbon::now()->endOfWeek());

        return $query;
    }
}
