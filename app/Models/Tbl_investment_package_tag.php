<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tbl_investment_package_tag extends Model
{
    use HasFactory;

	protected $table = 'tbl_investment_package_tag';
	protected $primaryKey = "investment_package_tag_id";
    public $timestamps = false;

    public function scopePackage($query)
    {
    	$query->join('tbl_investment_package','tbl_investment_package.investment_package_id','=','tbl_investment_package_tag.investment_package_id');

    	return $query;
    }
}

