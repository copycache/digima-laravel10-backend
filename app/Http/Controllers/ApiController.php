<?php

namespace App\Http\Controllers;

use App\Globals\Log;
use App\Models\Tbl_slot;
use App\Models\Tbl_user_process;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ApiController extends Controller
{
    
    public function recaptcha_link(Request $request)
    {
        $data = $request->all();
        $slot = Tbl_slot::where('slot_id', $data['slot_id'])->first();
        $user_info_process_id = $slot?->slot_owner;

        if ($user_info_process_id) {
            Tbl_user_process::where('user_id', $user_info_process_id)->delete();

            for ($level = 1; $level <= 4; $level++) {
                Tbl_user_process::insert(['level_process' => $level, 'user_id' => $user_info_process_id]);
                
                if ($level > 1 && Tbl_user_process::where('user_id', $user_info_process_id)->where('level_process', $level - 1)->count() != 1) {
                    Tbl_user_process::where('user_id', $user_info_process_id)->delete();
                    return ['status_message' => 'Please try again...', 'status' => 'error'];
                }
            }

            Tbl_user_process::where('user_id', $user_info_process_id)->delete();
        }

        $validator = Validator::make($data, [
            'slot_id' => 'required',
            'send_amount' => 'required',
        ]);

        if ($validator->fails()) {
            return self::sendError('Validation Error.', $validator->errors());
        }

        if (!$slot) {
            return self::sendError('Validation Error.', 'Slot no. does not exist to any account');
        }

        $currency_default = DB::table('tbl_currency')->where('currency_default', 1)->first();
        $currency = isset($data['wallet_receiver']) ? DB::table('tbl_currency')->where('currency_abbreviation', $data['wallet_receiver'])->first() : null;
        
        $currency_id = $currency?->currency_id ?? $currency_default->currency_id;
        $wallet_receiver = $currency?->currency_abbreviation ?? $currency_default->currency_abbreviation;

        Log::insert_wallet($slot->slot_id, $data['send_amount'], 'SENDING FUNDS', $currency_id);

        return self::sendResponse([
            'slot_owner' => $slot->name,
            'amount_send' => $data['send_amount'],
            'wallet_receiver' => $wallet_receiver
        ], 'FUNDS DISTRIBUTED SUCCESSFULLY.');
    }
    public static function sendResponse($result, $message)
    {
        return response()->json([
            'success' => true,
            'data' => $result,
            'message' => $message,
        ]);
    }
    public static function sendError($error, $errorMessages = [], $code = 404)
    {
        $response = ['success' => false, 'message' => $error];
        
        if (!empty($errorMessages)) {
            $response['data'] = $errorMessages;
        }

        return response()->json($response, $code);
    }
    public function main(Request $request)
    {
        $slot = Tbl_slot::where('slot_id', $request->input('slot_id'))->first();
        $user_info_process_id = $slot?->slot_owner;
        
        $slot_id = $user_info_process_id ? DB::table('tbl_slot')->where('slot_owner', $user_info_process_id)->first() : null;
        
        return response()->json([
            'success' => (bool)$user_info_process_id,
            'data' => $slot_id,
        ]);
    }
}
