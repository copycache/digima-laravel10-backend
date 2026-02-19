<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_orders_for_approval extends Model
{
    use HasFactory;

	protected $table = 'tbl_orders_for_approval';
	protected $primaryKey = "id";
    public $timestamps = false;
}
