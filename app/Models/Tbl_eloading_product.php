<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use DB;
class Tbl_eloading_product extends Model
{
    use HasFactory;

	protected $table = 'tbl_eloading_product';
	protected $primaryKey = "eloading_product_id";
    public $timestamps = false;
    
	public function scopeSearch($query,$key)
    {
        $query  ->where(function($query)use($key)
                {
                    $query->where('eloading_product_name','like','%'.$key.'%');
                    $query->orWhere('eloading_product_code','like','%'.$key.'%');
                    $query->orWhere('eloading_product_validity','like','%'.$key.'%');
                    $query->orWhere('eloading_product_description','like','%'.$key.'%');
                    $query->orWhere('eloading_product_guide','like','%'.$key.'%');
                    $query->orWhere('eloading_product_subscriber','like','%'.$key.'%');
                });
        return $query;
    }

    public function scopeDistinctSubscriber($query)
    {
        $query->select([DB::RAW('DISTINCT(tbl_eloading_product.eloading_product_subscriber)'),'tbl_eloading_product.eloading_product_subscriber']);
        return $query;
    }
}
