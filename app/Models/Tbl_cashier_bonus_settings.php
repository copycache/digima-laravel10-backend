<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_cashier_bonus_settings extends Model
{
    use HasFactory;

	protected $table = 'tbl_cashier_bonus_settings';
	protected $primaryKey = "cashier_bonus_settings_id";
    public $timestamps = false;
}
