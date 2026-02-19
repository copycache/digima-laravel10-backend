<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_product_downline_discount_logs extends Model
{
    use HasFactory;

	protected $table = 'tbl_product_downline_discount_logs';
	protected $primaryKey = "id";
    public $timestamps = false;
}
