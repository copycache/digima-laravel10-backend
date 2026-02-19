<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_override_points extends Model
{
    use HasFactory;

	protected $table = 'tbl_override_points';
	protected $primaryKey = "override_points_id";
    public $timestamps = false;
}
