<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use DB;
class Tbl_address extends Model
{
    use HasFactory;

	protected $table = 'tbl_address';
	protected $primaryKey = "address_id";
    public $timestamps = false;

	protected $guarded = [];

    public function scopeAddress($query)
    {
    	$query = $query->leftjoin('refregion',	'refregion.regCode',	 	'=','tbl_address.regCode');
    	$query = $query->leftjoin('refprovince','refprovince.provCode', 	'=','tbl_address.provCode');
    	$query = $query->leftjoin('refcitymun',	'refcitymun.citymunCode', 	'=','tbl_address.citymunCode');
    	$query = $query->leftjoin('refbrgy',	'refbrgy.brgyCode', 		'=','tbl_address.brgyCode');
        // $query = $query->select(DB::raw('refregion.regDesc as reg_desc'),'refregion.*','refprovince.*','refcitymun.*','refbrgy.*','tbl_address.*');

    	return $query;
    }
}
