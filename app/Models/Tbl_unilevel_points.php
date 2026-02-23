<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_unilevel_points extends Model
{
    use HasFactory;

	protected $guarded = [];

	protected $table = 'tbl_unilevel_points';
	protected $primaryKey = "unilevel_points_id";
    public $timestamps = false;
}
