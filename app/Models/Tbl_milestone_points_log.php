<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_milestone_points_log extends Model
{
    use HasFactory;

	protected $table = 'tbl_milestone_points_log';
	protected $primaryKey = "points_log_id";
    public $timestamps = false;
}
