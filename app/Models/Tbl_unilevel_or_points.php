<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_unilevel_or_points extends Model
{
    use HasFactory;

	protected $table = 'tbl_unilevel_or_points';
	protected $primaryKey = "unilevel_or_points_id";
    public $timestamps = false;
}
