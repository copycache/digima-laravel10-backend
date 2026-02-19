<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_infinity_bonus_log extends Model
{
    use HasFactory;

	protected $table = 'tbl_infinity_bonus_log';
	protected $primaryKey = "log_id";
    public $timestamps = false;
}
