<?php
namespace App\Globals;

use App\Models\Tbl_currency;
use App\Models\Tbl_currency_conversion;
use App\Globals\CashOut;
use App\Globals\Audit_trail;
use Request;
use App\Globals\Log;
use Carbon\Carbon;
use DB;


class Currency
{
	public static function module_settings_for_slot($is_active)
	{
		$get = DB::table('tbl_module')->where('module_type','member')->get();
        foreach ($get as $key => $value) 
        {
        	$response[$value->module_alias] = $is_active == 0 ? 0 : $value->slot_is_enable;
        }
        return $response;
	}
 	public static function module_settings($type = 'member')
	{
		$get = DB::table('tbl_module')->where('module_type',$type)->get();
        foreach ($get as $key => $value) 
        {
            $response[$value->module_alias] = $value->module_is_enable;
        }
        $response['replicated_member']            =  DB::table('tbl_replicated_settings')->where('replicated_name','membership')->value('replicated_sponsoring');
    	$response['send_wallet']                  =  DB::table('tbl_mlm_feature')->where('mlm_feature_name','send_wallet')->value('mlm_feature_enable');
        $response['conversion_wallet']            =  DB::table('tbl_mlm_feature')->where('mlm_feature_name','conversion_wallet')->value('mlm_feature_enable');
        $response['product_replicated']           =  DB::table('tbl_mlm_feature')->where('mlm_feature_name','product_replicated')->value('mlm_feature_enable');
    	$response['cashout_active'] 			  =  CashOut::cashout_settings();
        return $response;
	}
	
	public static function settings_currency()
	{
		$currency 				= Tbl_currency::where('currency_name','!=','');
		$check 					= Self::module_settings();

		$data['currency'] 				= $currency = $currency->get();

		foreach ($currency as $key => $value) 
		{
			foreach($currency as $key => $val)
			{
				$count = DB::table("tbl_currency_conversion")->where("currency_conversion_from",$value->currency_abbreviation)->where("currency_conversion_to",$val->currency_abbreviation)->count();
				if($count == 0)
				{
					$convert["currency_conversion_from"] = $value->currency_abbreviation;
					$convert["currency_conversion_to"] 	 = $val->currency_abbreviation;
					$convert["created_at"]				 = Carbon::now();
					DB::table("tbl_currency_conversion")->insert($convert);
				}
			}
			$from  = Tbl_currency_conversion::where('currency_conversion_from',$value->currency_abbreviation)->get();
			$data['currency_conversion'][$value->currency_abbreviation] 	= $from;
		}
		return $data;
	}

	public static function update_currency($data)
	{
		$user 		= Request::user()->id;
		$action     = "Update Currency"; 
		
		foreach ($data as $key => $value) 
		{
			$old_value  = Tbl_currency::where('currency_id',$value['currency_id'])->first();
			
			$update['currency_buying'] 		= $value['currency_buying'];
			$update['currency_enable'] 		= $value['currency_enable'];
			$update['currency_default'] 	= $value['currency_default'];
			Tbl_currency::where('currency_id',$value['currency_id'])->update($update);
			$new_value  = Tbl_currency::where('currency_id',$value['currency_id'])->first();
	
			Audit_trail::audit(serialize($old_value),serialize($new_value),$user,$action);
		}
	}

	public static function add_currency($currency_name,$currency_abbreviation)
	{
		$user 		= Request::user()->id;
		$action     = "Add Currency"; 
		$check = Tbl_currency::where('currency_abbreviation',$currency_abbreviation)->where('currency_name',$currency_name)->first();
		if(!$check)
		{
			Tbl_currency::insert(['currency_abbreviation'=>$currency_abbreviation,'currency_name'=>$currency_name]);
			$new_value = Tbl_currency::where('currency_abbreviation',$currency_abbreviation)->where('currency_name',$currency_name)->first();
		}

		Audit_trail::audit(null,serialize($new_value),$user,$action);

		return "ADDED";
	}

	public static function update_currency_conversion($data,$abbreviation)
	{
		$user 		= Request::user()->id;
		$action     = "Update Currency Conversion"; 
		foreach ($data[$abbreviation] as $key => $value) 
		{
			$old_value  = Tbl_currency_conversion::where('currency_conversion_id',$value['currency_conversion_id'])->first();
			$update['currency_conversion_from'] 	= $value['currency_conversion_from'];
			$update['currency_conversion_to'] 		= $value['currency_conversion_to'];
			$update['currency_conversion_rate'] 	= $value['currency_conversion_rate'];
			$update['currency_conversion_enable'] 	= $value['currency_conversion_enable'];
			$update['currency_system_conversion'] 	= $value['currency_system_conversion'];
			$update['updated_at'] 					= Carbon::now();
			Tbl_currency_conversion::where('currency_conversion_id',$value['currency_conversion_id'])->update($update);
			$new_value  = Tbl_currency_conversion::where('currency_conversion_id',$value['currency_conversion_id'])->first();
			Audit_trail::audit(serialize($old_value),serialize($new_value),$user,$action);
		}

		return "UPDATED";
	}

	public static function convert_wallet($data)
	{
		$sender_wallet      = $data['sender_wallet'];
        $receiver_wallet    = $data['receiver_wallet'];
        $sender_amount      = $data['sender_amount'];

        $settings           = Tbl_currency_conversion::where('currency_conversion_from',$sender_wallet)->where('currency_conversion_to',$receiver_wallet)->first();
		
		$decimal            = $sender_wallet    ==  "BTC" ? 8 : 2;
		$decimals            = $receiver_wallet  ==  "BTC" ? 8 : 2;
        $currency_convert   = $sender_wallet."_".$receiver_wallet;
        $currency_value     = round(0,$decimal);
        $data['status']     = 'error';

        $data['sender_amount']      = round($sender_amount,$decimal);
        
        if($settings->currency_conversion_enable == 0)
        {
            if($settings && $settings->currency_system_conversion == 0)
            {
                $currency_value             = round($settings->currency_conversion_rate,$decimal);
                $data['receiver_amount']    = round($sender_amount * $settings->currency_conversion_rate,$decimals);
                $data['status']             = 'success';
            }
            else
            {
                $response                   = file_get_contents("http://free.currencyconverterapi.com/api/v3/convert?q=".$currency_convert."&compact=ultra");
                $json                       = json_decode($response, true, 512, JSON_BIGINT_AS_STRING);
                if(isset($json[$currency_convert]))
                {
                    $currency_value         = round($json[$currency_convert],$decimal);
                    $data['receiver_amount']=  round($sender_amount * floatval($json[$currency_convert]),$decimals);
                    $data['status']         = 'success';
                }
            }
            $data['exchange_rate']   = $sender_wallet." ~ ".$receiver_wallet." ".$currency_value;
        }

        return $data;
	}

	public static function convert_submit($data)
	{
		$wallet             = Tbl_currency::where('currency_abbreviation',$data['sender_wallet'])->where('tbl_wallet.slot_id',$data['slot_id'])->Wallet()->first();
        $currency_id        = Tbl_currency::where('currency_abbreviation',$data['receiver_wallet'])->value('currency_id');
        $transaction_to     = "WALLET TRANSFERED - " .$data['sender_wallet']." ~ ".$data['receiver_wallet'];
        $transaction_from   = "TRANSFER WALLET - " .$data['sender_wallet']." ~ ".$data['receiver_wallet'];
        
        $decimal            = $data['sender_wallet']  ==  "BTC" ? 8 : 2;
        $decimals            = $data['receiver_wallet']  ==  "BTC" ? 8 : 2;

        if($wallet->wallet_amount < $data['sender_amount'] || $wallet->wallet_amount <= 0)
        {
            $return['status'] = "error";
            $return['message'] = "Insuficient Balance";
        }
        else
        {
        	$sender_amount = round($data['sender_amount'],$decimal);
        	$receiver_amount = round($data['receiver_amount'],$decimals);


            Log::insert_wallet($data['slot_id'], $sender_amount * (-1),$transaction_from, $wallet->currency_id);
            Log::insert_earnings($data['slot_id'], $sender_amount * (-1),$transaction_from,$transaction_from, $data['slot_id'],$transaction_from, $level = 0,$wallet->currency_id);

            Log::insert_wallet($data['slot_id'], $receiver_amount,$transaction_to, $currency_id);
            Log::insert_earnings($data['slot_id'],$receiver_amount,$transaction_to,$transaction_to, $data['slot_id'],$transaction_to, $level = 0,$currency_id);

            $return['status'] = "success";
            $return['message'] = "Wallet Converted Successfully";
        }

        return $return;
	} 
}
