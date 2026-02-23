<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_cash_out_settings_per_day extends Model
{
    use HasFactory;

	protected $table = 'tbl_cash_out_settings_per_day';
	protected $primaryKey = "cash_out_settings_per_day_id";
    public $timestamps = false;

	protected $guarded = [];

}
