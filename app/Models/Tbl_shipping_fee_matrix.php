<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_shipping_fee_matrix extends Model
{
    use HasFactory;

	protected $guarded = [];

	protected $table = 'tbl_shipping_fee_matrix';
	protected $primaryKey = "shipping_fee_matrix_id";
    public $timestamps = false;
}
