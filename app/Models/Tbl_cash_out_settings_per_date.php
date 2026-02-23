<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_cash_out_settings_per_date extends Model
{
    use HasFactory;

	protected $table = 'tbl_cash_out_settings_per_date';
	protected $primaryKey = "cash_out_settings_per_date_id";
    public $timestamps = false;

	protected $guarded = [];

}
