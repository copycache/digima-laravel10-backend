<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_achievers_rank_wallet_earnings extends Model
{
    use HasFactory;

	// Created By: Centy - 10-27-2023
	protected $table = 'tbl_achievers_rank_wallet_earnings';
	protected $primaryKey = "id";
    public $timestamps = false;

	public function scopeSlot($query)
    {
    	 return $query->leftJoin('tbl_slot', 'tbl_slot.slot_id', '=', 'tbl_achievers_rank_wallet_earnings.slot_id');
    }
}
