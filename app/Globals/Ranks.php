<?php
namespace App\Globals;

use DB;
use App\Models\Tbl_mlm_unilevel_settings;
use App\Models\Tbl_membership_unilevel_level;
use App\Models\Tbl_membership;
use App\Models\Tbl_stairstep_rank;

class Ranks
{
	public static function add($data)
	{
		$insert["stairstep_rank_name"]         = $data["stairstep_rank_name"];
		$insert["stairstep_rank_override"]     = $data["stairstep_rank_override"];
		$insert["stairstep_rank_personal"]     = $data["stairstep_rank_personal"];
		$insert["stairstep_rank_group"]        = $data["stairstep_rank_group"];
		$insert["stairstep_rank_personal_all"] = $data["stairstep_rank_personal_all"];
		$insert["stairstep_rank_group_all"]    = $data["stairstep_rank_group_all"];
		$insert["archive"]                     = 0;
		$insert["stairstep_rank_date_created"] = Carbon::now();

		Tbl_stairstep_rank::insert($insert);
	}
}