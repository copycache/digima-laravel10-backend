<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_mlm_lockdown_plan extends Model
{
    use HasFactory;

	protected $guarded = [];

	protected $table = 'tbl_mlm_lockdown_plan';
	protected $primaryKey = "mlm_lockdown_plan_id";
	public $timestamps = false;
	
	public function scopePlan($query)
	{
		return $query->leftJoin("tbl_mlm_plan","tbl_mlm_plan.mlm_plan_id","=","tbl_mlm_lockdown_plan.mlm_plan_code_id");
	}
}
