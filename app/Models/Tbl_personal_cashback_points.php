<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_personal_cashback_points extends Model
{
    use HasFactory;

	protected $guarded = [];

	protected $table = 'tbl_personal_cashback_points';
	protected $primaryKey = "personal_cashback_points_id";
    public $timestamps = false;
}
