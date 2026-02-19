<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_cashier_sales extends Model
{
    use HasFactory;

	protected $table = 'tbl_cashier_sales';
	protected $primaryKey = "cashier_sales_id";
    public $timestamps = false;
}
