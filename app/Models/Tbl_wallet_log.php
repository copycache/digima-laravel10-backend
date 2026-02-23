<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;
use DB;
class Tbl_wallet_log extends Model
{
    use HasFactory;

	protected $guarded = [];

	protected $table = 'tbl_wallet_log';
	protected $primaryKey = "wallet_log_id";
    public $timestamps = false;

    public function scopeCashIn($query)
    {
    	 return $query->rightJoin('tbl_cash_in_proofs', 'tbl_cash_in_proofs.cash_in_proof_id', '=', 'tbl_wallet_log.transaction_id')->where("tbl_wallet_log.wallet_log_details", "CASH IN");
    }

    public function scopeEloadLogs($query)
    {
         return $query->leftJoin('tbl_eloading_log', 'tbl_eloading_log.wallet_log_id', '=', 'tbl_wallet_log.wallet_log_id');
    }

    public function scopeCashOut($query)
    {
    	 return $query->join('tbl_cash_out_list', 'tbl_cash_out_list.cash_out_id', '=', 'tbl_wallet_log.transaction_id')->where("tbl_wallet_log.wallet_log_details", "CASH OUT");
    }

    public function scopeWalletLog($query,$slot_id)
    {
        $query -> where("wallet_log_slot_id",$slot_id);
        // $query -> select("*",DB::raw("DATE_FORMAT(tbl_wallet_log.wallet_log_date_created, '%m/%d/%Y') as wallet_log_date_created"));

    }
    public function scopeLabel($query)
    {
        return $query->leftJoin("tbl_label","plan_code","=",DB::raw("REPLACE(wallet_log_details, ' ', '_')"));
    }
    public function scopeEload($query)
    {
        $query ->where(function($query)
        {
            $query->where('wallet_log_details','like','%ELOAD%');
        });

        return $query;
    }
    public function scopeGetWeekWalletIncome($query)
    {
        $query  ->where('wallet_log_date_created', '>', Carbon::now()->startOfWeek())
                ->where('wallet_log_date_created', '<', Carbon::now()->endOfWeek());

        return $query;
    }
}
