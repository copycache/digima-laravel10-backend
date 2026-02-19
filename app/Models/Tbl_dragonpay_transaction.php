<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_dragonpay_transaction extends Model
{
    use HasFactory;

	protected $table = 'tbl_dragonpay_transaction';
	protected $primaryKey = "id";
    public $timestamps = false;
}
