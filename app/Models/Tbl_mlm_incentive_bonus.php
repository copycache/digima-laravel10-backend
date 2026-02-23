<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_mlm_incentive_bonus extends Model
{
    use HasFactory;

	protected $guarded = [];

	protected $table = 'tbl_mlm_incentive_bonus';
	protected $primaryKey = "incentives_bonus_id";
    public $timestamps = false;
}
