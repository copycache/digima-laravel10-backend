<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_points_log extends Model
{
    use HasFactory;

	protected $guarded = [];

	protected $table = 'tbl_points_log';
	protected $primaryKey = "points_log_id";
    public $timestamps = false;
}
