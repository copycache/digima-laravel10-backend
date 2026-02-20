<?php
namespace App\Globals;

use DB;
use Carbon\Carbon;


use Validator;

class Payout
{
	public static function add_settings($data)
	{
		$insert['payout_charges_charge']			= $data['payout_charges'];
		$insert['payout_charges_tax']				= $data['payout_tax'];
		$insert['payout_charges_giftcard']			= $data['giftcard'];

		DB::table('tbl_payout_charges')->insert($insert);

		$return["status"]         = "success"; 
		$return["status_code"]    = 200; 
		$return["status_message"] = "Charges Successfully Updated!";
		return $return;
	}

	public static function get_settings()
	{
		$return['data'] 	= DB::table('tbl_payout_charges')->first();

		$return["status"]         = "success"; 
		$return["status_code"]    = 206;

		return $return;
	}

	public static function payout_configuration($data)
	{
		$check_payout_settings = DB::table('tbl_payout_settings')->first();

		if(count($check_payout_settings) == 0)
		{
			inserting:

			$insert_payout_settings['minimum_encashment']		=	$data['config']['minimum_encashment'];

			//convert trues to 1
			foreach($data['method'] as $key => $value)
			{
				if($value == true)
				{
					$method[$key] = 1;
				}
			}

			//check if there's a method
			if(isset($method))
			{
				//insert array concatenation + value from earlier foreach
				foreach($method as $key => $value)
				{
					$insert_payout_settings[$key.'_enable_payout'] = $value;
				}
			}
			

			DB::table('tbl_payout_settings')->insert($insert_payout_settings);
		}
		else
		{
			DB::table('tbl_payout_settings')->truncate();

			goto inserting;
		}
	}
}