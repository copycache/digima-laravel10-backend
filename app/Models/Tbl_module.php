<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use DB;
class Tbl_module extends Model
{
    use HasFactory;

	protected $table = 'tbl_module';
	protected $primaryKey = "module_id";
    public $timestamps = false;

     public function scopeModule($query)
    {
    	$query ->leftJoin('tbl_module_access',	'tbl_module_access.module_id',	 	'=','tbl_module.module_id');
    	return $query;
    }
    
}
