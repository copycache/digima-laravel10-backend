<?php
namespace App\Globals;

use DB;
use Carbon\Carbon;
use Validator;

use App\Models\Tbl_membership_rank;


class MembershipRank
{
	public static function add($data)
	{
    	foreach($data as $key => $value)
    	{
	    	$insert[$key]["membership_name"]          = $data[$key]["membership_name"];
			$insert[$key]["membership_rank"]  		  = $data[$key]["membership_rank"];
		}
		Tbl_membership_rank::truncate();
		Tbl_membership_rank::insert($insert);

		$return["status"]         = "success"; 
		$return["status_code"]    = 201; 
		$return["status_message"] = "Plan Created";
       

        return $return;
	}

	public static function get()
	{
		$data = Tbl_membership_rank::orderBy('membership_rank', 'ASC')->get();

		return $data;
	}

	public static function add_item($params = null)
	{
		$params["item_date_created"] = Carbon::now();
		$data = Tbl_item::insert($params);
	}
}