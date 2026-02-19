<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_cashier_payment_method extends Model
{
    use HasFactory;

	protected $table = 'tbl_cashier_payment_method';
	protected $primaryKey = "cashier_payment_method_id";
    public $timestamps = false;
}
