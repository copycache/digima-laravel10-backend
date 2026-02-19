<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_cash_out_settings extends Model
{
    use HasFactory;

	protected $table = 'tbl_cash_out_settings';
	protected $primaryKey = "cash_out_settings_id";
    public $timestamps = false;
}
