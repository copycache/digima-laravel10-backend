<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_milestone_pairing_points_setup extends Model
{
    use HasFactory;

	protected $guarded = [];

	protected $table = 'tbl_milestone_pairing_points_setup';
	protected $primaryKey = "points_setup_id";
    public $timestamps = true;
}
