<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_stairstep_points extends Model
{
    use HasFactory;

	protected $guarded = [];

	protected $table = 'tbl_stairstep_points';
	protected $primaryKey = "stairstep_points_id";
    public $timestamps = false;
}
