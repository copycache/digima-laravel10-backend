<?php
namespace App\Globals;

use DB;
use Hash;
use Crypt;
use Validator;
use Carbon\Carbon;

use App\Models\Tbl_user_process;

class User_process
{
    public static function check($owner_id)
    {
    		$return = 0;
    		$user_process_level = 1;

			Tbl_user_process::where("user_id",$owner_id)->delete();
			$insert_user_process["level_process"] = $user_process_level;
			$insert_user_process["user_id"]       = $owner_id;
			Tbl_user_process::where("user_id",$owner_id)->where("level_process",$user_process_level)->insert($insert_user_process);
			while($user_process_level <= 4)
			{
				$user_process_level++;
				$insert_user_process["level_process"] = $user_process_level;
				$insert_user_process["user_id"]       = $owner_id;
				Tbl_user_process::where("user_id",$owner_id)->where("level_process",$user_process_level)->insert($insert_user_process);
				$count_process_before = Tbl_user_process::where("user_id",$owner_id)->where("level_process", ($user_process_level - 1) )->count();
				if($count_process_before != 1)
				{
					$return = 1;
				}
			}

			Tbl_user_process::where("user_id",$owner_id)->delete();

			return $return;
    }

    
}