<?php
namespace App\Http\Controllers;


use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Tbl_slot;
use App\Models\Tbl_user_process;
use App\Globals\Log;

class ApiController extends Controller
{
    
    public function recaptcha_link(Request $request)
    {
        $data = $request->all();
 
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
           
            $validator = Validator::make($data, [
                'slot_id'     => 'required',
                'send_amount' => 'required',
            ]);

            if($validator->fails())
            {
                return Self::sendError('Validation Error.', $validator->errors());       
            }

            $currency_default = DB::table('tbl_currency')->where('currency_default',1)->first();
            $slot = Tbl_slot::Owner()->where('slot_id',$data['slot_id'])->first();

            if(isset($data['wallet_receiver']))
            {
            	$currency 			= DB::table('tbl_currency')->where('currency_abbreviation',$data['wallet_receiver'])->first();
            	$currency_id 		= $currency == null ? 	$currency_default->currency_id : $currency->currency_id;
            	$wallet_receiver 	= $currency == null ? 	$currency_default->currency_abbreviation : $currency->currency_abbreviation;
            }
            else
            {
            	$currency_id 		= $currency_default->currency_id;
            	$wallet_receiver 	= $currency_default->currency_abbreviation;
            }

            if(!$slot)
            {
            	return Self::sendError('Validation Error.', 'Slot no. does not exist to any account');
            }

            Log::insert_wallet($slot->slot_id, $data['send_amount'],"SENDING FUNDS", $currency_id);

            $return['slot_owner'] 		= $slot->name;
            $return['amount_send'] 		= $data['send_amount'];
            $return['wallet_receiver'] 	= $wallet_receiver;

            return Self::sendResponse($return, 'FUNDS DISTRIBUTED SUCCESSFULLY.');
        }
        else
        {
            $message['status_message']  = "Please try again...";
            $message['status']          = "error";   
        }

        return $message;
    }
    public static function sendResponse($result, $message)
    {
        $response = [
            'success' => true,
            'data'    => $result,
            'message' => $message,
        ];


        return response()->json($response, 200);
    }
    public static function sendError($error, $errorMessages = [], $code = 404)
    {
        $response = [
            'success' => false,
            'message' => $error,
        ];


        if(!empty($errorMessages)){
            $response['data'] = $errorMessages;
        }


        return response()->json($response, $code);
    }
    public function main(Request $request)
    {
        $data = $request->all();
        $user_info_process_id    = Tbl_slot::where("slot_id",$data['slot_id'])->first() ? Tbl_slot::where("slot_id",$data['slot_id'])->first()->slot_owner : null;
        if($user_info_process_id)
        {
            $slot_id = DB::table('tbl_slot')->where("slot_owner",$user_info_process_id)->first();
            $response = [
                'success' => true,
                'data'    => $slot_id,
            ];
            return response()->json($response, 200);
        }
        else 
        {
            $slot_id = null;
            $response = [
                'success' => false,
                'data'    => $slot_id,
            ];
            return response()->json($response, 200);
        }
    }
}
