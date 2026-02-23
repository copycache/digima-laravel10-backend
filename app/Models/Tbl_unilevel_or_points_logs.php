<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use DB;
class Tbl_unilevel_or_points_logs extends Model
{
    use HasFactory;

	protected $guarded = [];

	protected $table = 'tbl_unilevel_or_points_logs';
	protected $primaryKey = "unilevel_or_points_id";
    public $timestamps = false;
}
