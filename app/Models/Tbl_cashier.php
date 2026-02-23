<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_cashier extends Model
{
    use HasFactory;

	protected $table = 'tbl_cashier';
	protected $primaryKey = "cashier_id";
    public $timestamps = false;

	protected $guarded = [];

}

