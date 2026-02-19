<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_product_share_link_logs extends Model
{
    use HasFactory;

	protected $table = 'tbl_product_share_link_logs';
	protected $primaryKey = "id";
    public $timestamps = false;
}
