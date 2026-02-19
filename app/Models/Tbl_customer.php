<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_customer extends Model
{
    use HasFactory;

	protected $table = 'tbl_customer';
	protected $primaryKey = "customer_id";
    public $timestamps = false;
}
