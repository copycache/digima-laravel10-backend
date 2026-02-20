<?php
namespace App\Globals;

use DB;
use App\Models\Tbl_eloading_product;
use App\Models\Tbl_wallet;
use App\Models\Tbl_wallet_log;
use App\Models\Tbl_eloading_settings;

use App\Models\Tbl_eloading_tab_settings;
use App\Models\Tbl_currency;
use App\Models\Tbl_user_process;
use App\Models\Tbl_slot;
use App\Globals\Log;

class Eloading
{
    public static function get_settings()
    {
        $response['eload'] = Tbl_eloading_settings::first();
        $response['module'] = Tbl_eloading_tab_settings::get();
        return $response;
    }
    public static function get_wallet($slot_id)
    {
        $currency_id = Tbl_currency::where('currency_name','LOAD WALLET')->value('currency_id');

        $wallet_amount           = Tbl_wallet::where('currency_id',$currency_id)->where('slot_id',$slot_id)->value('wallet_amount');
        $return['wallet_log']    = Tbl_wallet_log::where('currency_id',$currency_id)->where('wallet_log_slot_id',$slot_id)->orderBy('wallet_log_id','ASC')->get();
        
        $return['wallet_amount'] = $wallet_amount==null ? 0:$wallet_amount;

        return $return;   
    }

    public static function get_eload_logs()
    {
        $currency_id = Tbl_currency::where('currency_name','LOAD WALLET')->value('currency_id');
        $logs        = Tbl_wallet_log::EloadLogs()->where('currency_id',$currency_id)->Eload()->orderBy('tbl_wallet_log.wallet_log_id','ASC');
        $return['eload_log']    = $logs->get();
        $return['total_log']    = $logs->sum('wallet_log_amount');

        
        return $return;   
    }

	public static function getInnerSubstring($string,$delim)
    {
    	$nstring = str_replace('/', '', $string);
    	
        $string = explode($delim, $nstring, 3);
        $return = isset($string[1]) ? $string[1] : '';

        return $return;
    }

    public static function phone_number_checking($phone_number)
    {
    	$error = "Valid";
    	if(!preg_match('/^(09|\+639)\d{9}$/', $phone_number))
	    {
	      $error = 'Invalid';
	    }
	    return $error;
    }

    public static function get_pcode_amount($pcode)
    {
        $strrev = strrev($pcode);
        $strlen = strlen($pcode);

        $value = "";
        for($i = 1;$i <= $strlen ;$i++)
        {
            if(is_numeric(substr($strrev,0,$i)))
            {
                $value = substr($strrev,0,$i);
            }
            else
            {
                break;
            }
        }

        return strrev($value);
    }

	public static function eloading_submit($data)
    {

        $user_process_level      = 1;
        $user_process_proceed    = 1;
        $user_info_process_id    = Tbl_slot::where("slot_id",$data['slot_id'])->first() ? Tbl_slot::where("slot_id",$data['slot_id'])->first()->slot_owner : null;

        if($user_info_process_id != null)
        {
            /* PREVENTS MULTIPLE PROCESS AT ONE TIME */
            Tbl_user_process::where("user_id",$user_info_process_id)->delete();

            $insert_user_process["level_process"] = $user_process_level;
            $insert_user_process["user_id"]       = $user_info_process_id;
            Tbl_user_process::where("user_id",$user_info_process_id)->where("level_process",$user_process_level)->insert($insert_user_process);

            while($user_process_level <= 4)
            {
                $user_process_level++;
                $insert_user_process["level_process"] = $user_process_level;
                $insert_user_process["user_id"]       = $user_info_process_id;
                Tbl_user_process::where("user_id",$user_info_process_id)->where("level_process",$user_process_level)->insert($insert_user_process);

                $count_process_before = Tbl_user_process::where("user_id",$user_info_process_id)->where("level_process", ($user_process_level - 1) )->count();

                if($count_process_before != 1)
                {
                   $user_process_proceed = 0;
                   break;
                }
            }

            Tbl_user_process::where("user_id",$user_info_process_id)->delete();
        }


        if($user_process_proceed == 1)
        {       
            $eloading_product 	= Tbl_eloading_product::where('eloading_product_id',$data['eloading_product_id'])->first();
            $user_name          = env('ELOADING_USERNAME');
        	$user_password 	    = env('ELOADING_PASSWORD');
            $rrn 				= "DIGI".mt_rand().rand();
            $auth 				= md5(md5($rrn)."".md5($user_name."".$user_password));
            $phone_number   	= $data['phone_number'];
            $pcode          	= $eloading_product->eloading_product_code;
            $pcode_price        = Self::get_pcode_amount($pcode);
            $slot_id            = $data['slot_id'];
            $wallet             = Self::get_wallet($slot_id);
            $ret                = Self::phone_number_checking($phone_number);
            $currency_id        = Tbl_currency::where('currency_name','LOAD WALLET')->value('currency_id');
            if($pcode_price > $wallet['wallet_amount'])
            {
                $message['status_message']  = "Insuficient Wallet Balance!";
                $message['status']          = "ERROR";
            }
            else
            {
                $settings   = Self::get_settings();
                $discount   = $pcode_price - ($pcode_price *($settings['eload']->eloading_discount_wallet_percentage/100));

                $reference  = "ELOAD - Product Code :".$pcode;
                $log_id     = Log::insert_wallet($slot_id,"-".$discount,$reference,$currency_id);
            	$sell_product       = "https://loadcentral.net/sellapi.do?uid=".$user_name."&auth=".$auth."&pcode=".$pcode."&to=".$phone_number."&rrn=".$rrn;
                $balance_inquire    = "https://loadcentral.net/sellapiinq.do?uid=".$user_name."&auth=".$auth."&rrn=".$rrn;
                
                $eloading_json = file_get_contents($sell_product);

                $RRN        = Self::getInnerSubstring($eloading_json,"<RRN>");
                $RESP       = Self::getInnerSubstring($eloading_json,"<RESP>");
                $TID        = Self::getInnerSubstring($eloading_json,"<TID>");
                $BAL        = Self::getInnerSubstring($eloading_json,"<BAL>");
                $EPIN       = Self::getInnerSubstring($eloading_json,"<EPIN>");
                $ERR        = Self::getInnerSubstring($eloading_json,"<ERR>");


                $insert_log['eloading_log_rrn']     = $RRN;
                $insert_log['eloading_log_resp']    = $RESP;
                $insert_log['eloading_log_tid']     = $TID;
                $insert_log['eloading_log_bal']     = $BAL;
                $insert_log['eloading_log_epin']    = $EPIN;
                $insert_log['eloading_log_err']     = $ERR;
                $insert_log['eloading_log_phone']   = $phone_number;
                $insert_log['eloading_log_amount']  = $pcode_price;
                $insert_log['wallet_log_id']        = $log_id;

                
                if($RESP == 0)
                {
                    $message['status_message']  = $ERR;
                    $message['status']          = "SUCCESS";
                    $update["wallet_log_details"]             = "(SUCCESS) ".$reference;
                    DB::table('tbl_eloading_log')->insert($insert_log);
                }
                else
                {
                    $message['status_message']  = $ERR;
                    $message['status']          = "ERROR";
                    $update["wallet_log_details"]             = "(INVALID) ".$reference;

                    $count_log = DB::table('tbl_eloading_log')->where('eloading_log_tid',$TID)->count();
                    if($count_log == 0)
                    {
                        DB::table('tbl_eloading_log')->insert($insert_log);
                        Log::insert_wallet($slot_id,$discount,"(REFUND) " .$reference,$currency_id);
                    }
                }
                
                Tbl_wallet_log::where('wallet_log_id',$log_id)->update($update);
            }
        }
        else
        {
            $message['status_message']  = "Please try again...";
            $message['status']          = "error";   
        }

        return $message;
    }
}