<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;
use DB;
class Tbl_slot extends Model
{
    use HasFactory;

protected $guarded = [];

protected $table = 'tbl_slot';
    protected $primaryKey = "slot_id";
    protected $appends  = ['income_receive'=>'total_income_receive','paid_out'=>'amount_paid_out','direct'=>'direct_income','indirect'=>'indirect_income','binary'=>'binary_income','stairstep'=>'stairstep_income','unilevel_bonus'=>'unilevel_bonus_income'];
    public $timestamps = false;

    public function scopeOwner($query)
    {
     return $query->join('users', 'users.id', '=', 'tbl_slot.slot_owner');
    }

    public function scopeWallet($query, $currency)
    {
     return $query->join('tbl_wallet', 'tbl_wallet.slot_id', '=', 'tbl_slot.slot_id')->where('tbl_wallet.currency_id', $currency);
    }

    public function scopeJoinWallet($query)
    {
    return $query->join('tbl_wallet', 'tbl_wallet.slot_id', '=', 'tbl_slot.slot_id');
    }

    public function scopeJoinDiscountByMembership($query)
    {
    return $query->join('tbl_item_membership_discount', 'tbl_item_membership_discount.membership_id', '=', 'tbl_slot.slot_membership');
    }

    public function scopeJoinDiscountByRank($query)
    {
        return $query->join('tbl_item_stairstep_rank_discount', 'tbl_item_stairstep_rank_discount.stairstep_rank_id', '=', 'tbl_slot.slot_stairstep_rank');
    }

    public function scopeJoinMonolinePoints($query)
    {
        return $query->join('tbl_monoline_points', 'tbl_monoline_points.slot_id', '=', 'tbl_slot.slot_id');
    }

    public function scopeJoinMembership($query)
    {
        return $query->join('tbl_membership', 'tbl_membership.membership_id', '=', 'tbl_slot.slot_membership');
    }

    public function scopeJoinUniversalPoolBonusPoints($query)
    {
        return $query->join('tbl_mlm_universal_pool_bonus_points', 'tbl_mlm_universal_pool_bonus_points.slot_id', '=', 'tbl_slot.slot_id');
    }
    public function scopeTreeSponsor($query)
    {
        return $query->join('tbl_tree_sponsor', 'tbl_tree_sponsor.sponsor_child_id', '=', 'tbl_slot.slot_id');
    }

    public function scopeGetWeekRegistered($query)
    {
        $query  ->where('slot_date_created', '>', Carbon::now()->startOfWeek())
                ->where('slot_date_created', '<', Carbon::now()->endOfWeek());

        return $query;
    }

    public function scopeJoinCurrency($query)
    {
        return $query->join('tbl_currency', 'tbl_currency.currency_id', '=', 'tbl_wallet.currency_id');
    }

    public function scopeJoinTopRecruiter($query)
    {
    return $query->leftjoin('tbl_top_recruiter', 'tbl_top_recruiter.slot_id', '=', 'tbl_slot.slot_id');
    }

    public function getTotalIncomeReceiveAttribute()
    {
        return DB::table('tbl_earning_log')->where('earning_log_slot_id',$this->slot_id)->sum('earning_log_amount');
    }
    public function getAmountPaidOutAttribute()
    {
        return DB::table('tbl_wallet_log')->where('wallet_log_slot_id',$this->slot_id)->where('wallet_log_type','=','CREDIT')->sum('wallet_log_amount');
    }
    public function getDirectIncomeAttribute()
    {
        return DB::table('tbl_earning_log')->where('earning_log_slot_id',$this->slot_id)->where('earning_log_plan_type','=','DIRECT')->sum('earning_log_amount');
    }
    public function getIndirectIncomeAttribute()
    {
        return DB::table('tbl_earning_log')->where('earning_log_slot_id',$this->slot_id)->where('earning_log_plan_type','=','INDIRECT')->sum('earning_log_amount');
    }
    public function getBinaryIncomeAttribute()
    {
        return DB::table('tbl_earning_log')->where('earning_log_slot_id',$this->slot_id)->where('earning_log_plan_type','=','BINARY')->sum('earning_log_amount');
    }
    public function getStairstepIncomeAttribute()
    {
        return DB::table('tbl_earning_log')->where('earning_log_slot_id',$this->slot_id)->where('earning_log_plan_type','=','STAIRSTEP')->sum('earning_log_amount');
    }
    public function getUnilevelBonusIncomeAttribute()
    {
        return DB::table('tbl_earning_log')->where('earning_log_slot_id',$this->slot_id)->where('earning_log_plan_type','=','UNILEVEL')->sum('earning_log_amount');
    }
    // Created By: Centy - 10-27-2023
    public function scopeAchieversRankAttribute($query)
    {
        return $query->join('tbl_achievers_rank', 'tbl_achievers_rank.achievers_rank_id', '=', 'tbl_slot.slot_achievers_rank');
    }

    public function scopeLivewellRankAttribute($query)
    {
        return $query->join('tbl_livewell_rank', 'tbl_livewell_rank.livewell_rank_id', '=', 'tbl_slot.slot_livewell_rank');
    }
}
