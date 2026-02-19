<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_incentive_purchase_count extends Model
{
    use HasFactory;

	protected $table = 'tbl_incentive_purchase_count';
	protected $primaryKey = "id";
    public $timestamps = false;
}
