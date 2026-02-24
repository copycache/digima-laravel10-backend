<?php
namespace App\Globals;

use App\Models\Tbl_investment_package;
use App\Models\Tbl_investment_package_logs;
use App\Models\Tbl_investment_package_tag;
use App\Globals\Log;
use Carbon\Carbon;
use Request;
use DB;

class InvestMent
{
	public static function load_package()
    {
		// $logs 		= Tbl_investment_package_logs::whereDate('investment_package_logs_date',Carbon::now()->format('Y-m-d'))->whereTime('investment_package_logs_date','>=',Carbon::now()->format('H:i:s'))->where('investment_package_logs_amount',0)->get();
		
		$logs 		= Tbl_investment_package_logs::whereDate('investment_package_logs_date','<=',Carbon::now()->format('Y-m-d'))->where('investment_package_logs_amount',0)->get();
		
		$volume 	= 20;
		if(count($logs)!= 0)
		{
			foreach($logs as $key=>$log)
			{
				if(date("Y-m-d",strtotime($log->investment_package_logs_date)) < Carbon::now()->format('Y-m-d'))
				{
					$package                					= Tbl_investment_package_tag::where('investment_package_tag_id',$log['investment_package_tag_id'])->Package()->first();
					$interest               					= (($package->investment_package_max_interest - $package->investment_package_min_interest) * $volume )/ 100 + $package->investment_package_min_interest;
					$interest_amount        					= $package->investment_amount * ($interest/100);
					$update['investment_package_logs_amount'] 	= $interest_amount;
					
					sleep(2);
					Log::insert_wallet($package->slot_id,$interest_amount,"INTEREST");
					Log::insert_earnings($package->slot_id,$interest_amount,"INVESTMENT","INTEREST",$package->slot_id,"details");

					Tbl_investment_package_logs::where('investment_package_logs_id',$log['investment_package_logs_id'])->update($update);

					$last_logs                                  = Tbl_investment_package_logs::where('investment_package_tag_id',$log['investment_package_tag_id'])->orderBy('investment_package_logs_date','DESC')->first();
					if($last_logs->investment_package_logs_date <= Carbon::now()->format('Y-m-d H:i:s') && $last_logs->investment_package_logs_amount != 0)
					{
						sleep(2);
						Log::insert_wallet($package->slot_id,$package->investment_amount,"PRINCIPAL AMOUNT");
					}
				}
				else if(date("Y-m-d",strtotime($log->investment_package_logs_date)) == Carbon::now()->format('Y-m-d') && date("H:m:i",strtotime($log->investment_package_logs_date)) <= Carbon::now()->format('H:i:s') )
				{
					$package                					= Tbl_investment_package_tag::where('investment_package_tag_id',$log['investment_package_tag_id'])->Package()->first();
					$interest               					= (($package->investment_package_max_interest - $package->investment_package_min_interest) * $volume )/ 100 + $package->investment_package_min_interest;
					$interest_amount        					= $package->investment_amount * ($interest/100);
					$update['investment_package_logs_amount'] 	= $interest_amount;
					
					sleep(2);
					Log::insert_wallet($package->slot_id,$interest_amount,"INTEREST");
					Log::insert_earnings($package->slot_id,$interest_amount,"INVESTMENT","INTEREST",$package->slot_id,"details");

					Tbl_investment_package_logs::where('investment_package_logs_id',$log['investment_package_logs_id'])->update($update);

					$last_logs                                  = Tbl_investment_package_logs::where('investment_package_tag_id',$log['investment_package_tag_id'])->orderBy('investment_package_logs_date','DESC')->first();
					if($last_logs->investment_package_logs_date <= Carbon::now()->format('Y-m-d H:i:s') && $last_logs->investment_package_logs_amount != 0)
					{
						sleep(2);
						Log::insert_wallet($package->slot_id,$package->investment_amount,"PRINCIPAL AMOUNT");
					}
				}
			}
        }       
    }  
}
