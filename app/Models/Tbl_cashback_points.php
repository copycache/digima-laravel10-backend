<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_cashback_points extends Model
{
    use HasFactory;

	protected $table = 'tbl_cashback_points';
	protected $primaryKey = "cashback_points_id";
    public $timestamps = false;
}
