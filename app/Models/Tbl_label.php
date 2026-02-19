<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_label extends Model
{
    use HasFactory;

	protected $table = 'tbl_label';
	protected $primaryKey = "label_id";
    public $timestamps = false;

	public function scopeMlmPlan($query)
	{
		return $query->leftJoin('tbl_mlm_plan', 'tbl_mlm_plan.mlm_plan_code', '=', 'tbl_label.plan_code')
			->where('tbl_mlm_plan.mlm_plan_enable', 1);
	}

}
