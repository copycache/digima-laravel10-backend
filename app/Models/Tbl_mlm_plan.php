<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_mlm_plan extends Model
{
    use HasFactory;

	protected $table = 'tbl_mlm_plan';
	protected $primaryKey = "mlm_plan_id";
    public $timestamps = false;
}
