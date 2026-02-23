<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_product_downline_discount extends Model
{
    use HasFactory;

	protected $table = 'tbl_product_downline_discount';

	protected $primaryKey = "id";

	protected $guarded = [];
    public $timestamps = false;
}
