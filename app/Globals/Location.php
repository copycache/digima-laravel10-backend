<?php
namespace App\Globals;

use DB;
use App\Models\Refregion;
use App\Models\Refprovince;
use App\Models\Refcitymun;
use App\Models\Refbrgy;
use App\Models\Tbl_island_group;

class Location
{
	public static function ISLAND_GROUP($data)
	{
		return Tbl_island_group::where('id','>=',2)->get();
	}
	public static function REGION_LIST($data)
	{
		if($data['code'] == 2)
		{
			
			$response =  Refregion::where(function ($query) {
				$query->where('id','>=',1)->where('id','<=',6)
					  ->orWhere('id',14)->orWhere('id',15);
	
			})->get();
		}
		else if($data['code'] == 3)
		{
			$response =  Refregion::where(function ($query) {
				$query->where('id','>=',7)->where('id','<=',9);
	
			})->get();
		}
		else
		{
			$response =  Refregion::where(function ($query) {
				$query->where('id','>=',10)->where('id','<=',13)
					  ->orWhere('id',16)->orWhere('id',17);
	
			})->get();
		}

		return $response;
	}
	public static function PROVINCE($data)
	{
		return Refprovince::where('regCode',$data['code'])->get();
	}
	public static function CITY($data)
	{
		return Refcitymun::where('provCode',$data['code'])->get();
	}
	public static function BRGY($data)
	{
		return Refbrgy::where('citymunCode',$data['code'])->get();
	}
	

}
