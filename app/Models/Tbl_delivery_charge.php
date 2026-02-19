<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_delivery_charge extends Model
{
    use HasFactory;

	protected $table = 'tbl_delivery_charge';
	protected $primaryKey = "method_id";
    public $timestamps = false;
}
