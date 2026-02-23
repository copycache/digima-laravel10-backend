<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_achievers_rank_list extends Model
{
    use HasFactory;

    // Created By: Centy - 10-27-2023
	protected $table = 'tbl_achievers_rank_list';
	protected $primaryKey = "list_id";
    public $timestamps = false;
	protected $guarded = [];
	
	public function scopeSlot($query)
    {
    	 return $query->leftJoin('tbl_slot', 'tbl_slot.slot_id', '=', 'tbl_achievers_rank_list.slot_id');
    }
	public function scopeOwner($query)
    {
    	 return $query->leftJoin('users', 'users.id', '=', 'tbl_slot.slot_owner');
    }
	public function scopeAchieversRankAttribute($query)
    {
        return $query->join('tbl_achievers_rank', 'tbl_achievers_rank.achievers_rank_id', '=', 'tbl_achievers_rank_list.rank_id');
    }
}
