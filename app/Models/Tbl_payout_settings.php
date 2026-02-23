<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_payout_settings extends Model
{
    use HasFactory;

	protected $guarded = [];

	protected $table = 'tbl_payout_settings';
	protected $primaryKey = "payout_settings_id";
    public $timestamps = false;
}
