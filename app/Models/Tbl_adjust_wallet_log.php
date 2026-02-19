<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_adjust_wallet_log extends Model
{
    use HasFactory;

	protected $table = 'tbl_adjust_wallet_log';
	protected $primaryKey = "adjust_wallet_id";
    public $timestamps = false;

    public function scopeSlot($query)
    {
    	 return $query->leftJoin('tbl_slot', 'tbl_slot.slot_id', '=', 'tbl_adjust_wallet_log.slot_id');
    }

    public function scopeOwner($query)
    {
    	 return $query->leftJoin('users', 'users.id', '=', 'tbl_slot.slot_owner');
    }
    

}
