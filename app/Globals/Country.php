<?php
namespace App\Globals;

use DB;
use App\Models\Tbl_country;

class Country
{
	public static function get()
	{
		return Tbl_country::where("archive",0)->get();
	}
}