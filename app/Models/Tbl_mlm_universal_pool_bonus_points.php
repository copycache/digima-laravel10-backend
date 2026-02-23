<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_mlm_universal_pool_bonus_points extends Model
{
    use HasFactory;

	protected $guarded = [];

	protected $table = 'tbl_mlm_universal_pool_bonus_points';
	protected $primaryKey = "universal_pool_bonus_points_id";
    public $timestamps = false;
}
