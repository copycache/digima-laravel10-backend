<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use DB;
class Tbl_module_access extends Model
{
    use HasFactory;

	protected $guarded = [];

	protected $table = 'tbl_module_access';
	protected $primaryKey = "module_access_id";
    public $timestamps = false;

    public function scopeModule($query)
    {
    	$query ->join('tbl_module',	'tbl_module.module_id','=','tbl_module_access.module_id');
    	return $query;
    }
}
