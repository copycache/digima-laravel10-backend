<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_prime_refund_points_log extends Model
{
    use HasFactory;

	protected $table = 'tbl_prime_refund_points_log';
	protected $primaryKey = "log_id";
    public $timestamps = false;
}
