<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_cashier_bonus extends Model
{
    use HasFactory;

	protected $table = 'tbl_cashier_bonus';
	protected $primaryKey = "cashier_bonus_id";
    public $timestamps = false;

	protected $guarded = [];

}
