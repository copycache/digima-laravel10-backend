<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_leveling_bonus_points extends Model
{
    use HasFactory;

	protected $guarded = [];

	protected $table = 'tbl_leveling_bonus_points';
	protected $primaryKey = "slot_id";
    public $timestamps = false;
}
