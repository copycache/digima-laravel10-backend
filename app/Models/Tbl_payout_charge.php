<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_payout_charge extends Model
{
    use HasFactory;

	protected $guarded = [];

	protected $table = 'tbl_payout_charge';
	protected $primaryKey = "payout_charge_id";
    public $timestamps = false;
}
