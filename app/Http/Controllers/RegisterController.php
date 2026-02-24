<?php

namespace App\Http\Controllers;

use App\Globals\Code;
use App\Globals\Slot;
use App\Mail\EmailActivation;
use App\Globals\Country;
use App\Globals\Member;
use App\Models\Tbl_codes;
use Carbon\Carbon;
use Illuminate\Support\Facades\Request;
use App\Models\Tbl_slot;
use App\Models\Tbl_other_settings;
use App\Models\User;
use App\Models\Tbl_dealer;
use App\Models\Tbl_retailer;
use App\Models\Tbl_item;
use App\Models\Tbl_verification_codes;

use Crypt;
use Mail;
class RegisterController extends Controller
{
    public function get_country()
    {
        $response = Country::get();

        return response()->json($response, 200);
    }

    public function check_dealers_code()
    {
        $dealer_code  = Request::input("dealer_code");
        $dealer       = Tbl_dealer::where("dealer_code",$dealer_code )->first();
        if($dealer)
        {
            $response["status"]  = 1;
            $response["message"] = "Success";
        }
        else
        {
            $response["status"]  = 0;
            $response["message"] = "Invalid dealers link.";
        }

        return $response;
    }

    public function new_register()
    {
        $register_activation = Tbl_other_settings::where("key", "registration_with_activation")->first()->value;
        $response = ["status" => "error", "status_code" => 400];
        
        if ($register_activation) {
            $code_activation = Request::input('code_activation');
            $code_pin = Request::input('code_pin');
            $check_activation = Tbl_codes::where('code_activation', $code_activation)->first();
            $check_pin = Tbl_codes::where('code_pin', $code_pin)->first();

            if (!$check_activation) {
                $response["status_message"][0] = "Invalid Code Activation";
            } elseif (!$check_pin) {
                $response["status_message"][0] = "Invalid Code Pin";
            } elseif ($check_activation != $check_pin) {
                $response["status_message"][0] = "Invalid combination of Code Activation and Pin";
            } else {
                $get_code = Tbl_codes::where('code_activation', $code_activation)->where('code_pin', $code_pin)->first();
                if (!$get_code->code_used_by) {
                    $register_area = Request::input("register_platform") == "system" ? "register_area" : "social";
                    $response = Member::add_member(request()->all(), $register_area);

                    if ($response['status'] == 'success') {
                        $slot_info = Tbl_slot::where('slot_no', Request::input('username'))->first();
                        $pass = [
                            "pin" => $get_code->code_pin,
                            "code" => $get_code->code_activation,
                            "slot_sponsor" => Request::input('slot_referral'),
                            "slot_owner" => $slot_info->slot_owner,
                            'slot_id' => $slot_info->slot_id,
                        ];
                        $register_your_slot = Tbl_other_settings::where("key", "register_your_slot")->first()->value ?? 1;
                        $register_on_slot = Tbl_other_settings::where("key", "register_on_slot")->first()->value ?? 1;

                        if ($register_your_slot == 0 && $register_on_slot == 1) {
                            $check_code = Code::get_membership_code_details($pass["code"], $pass["pin"]);
                            if ($check_code && $check_code->slot_qty == 1) {
                                $count_activated_slot = Tbl_slot::where("slot_owner", $slot_info->slot_owner)->where("membership_inactive", 0)->count();
                                if ($count_activated_slot != 0) {
                                    $response["status_message"][0] = "You can only use bundled kit for yourself...";
                                    $error = 1;
                                }
                            }
                        }
                        if (empty($error)) {
                            $response = Slot::create_slot($pass);
                        }
                    }
                } else {
                    $response["status_message"][0] = "This code is already used";
                }
            }
        } else {
            $register_area = Request::input("register_platform") == "system" ? "register_area" : "social";
            $response = Member::add_member(request()->all(), $register_area);
        }

        if (!isset($response["status_message"])) {
            $response["status_message"][0] = "Invalid Code Activation and Code Pin";
        }
		return $response;
    }

    public function check_credentials()
    {
        $response = Member::check_credentials(Request::input('member'));
        return json_encode($response);
    }

    public function get_register_settings()
    {
        $keys = ["register_facebook", "register_google", "registration_with_activation"];
        $settings = Tbl_other_settings::whereIn("key", $keys)->get()->keyBy('key');
        
        $response["facebook"] = $settings->get("register_facebook");
        $response["google"]   = $settings->get("register_google");
        $response["registration_activation"]   = $settings->get("registration_with_activation");
        
        return json_encode($response);
    }

    public function slot_check()
    {
        $_slot_no                = Request::input('check_referral');

        $slot_owner             = Tbl_slot::where('slot_no',$_slot_no)->where('slot_type','PS')->pluck('slot_owner')->first();
        if($slot_owner)
        {
            $slot_no                = Tbl_slot::where("slot_owner",$slot_owner)->where("slot_status","!=","blocked")->orderBy('slot_date_created')->pluck('slot_no')->first() ?? null;
            
            if($slot_no != null)
            {
                $return             = Crypt::encryptString($slot_no);
                // $return             = Crypt::encryptString($_slot_no);
                // Other slots with referal link

            }
            else
            {
                $return = 0;
            }
        }
        else
        {
            $return = 0;
        }
        return json_encode($return);
    }
    public function check_sponsor()
    {

        try 
        {
            $error_status = 0;
            $slot_no = Crypt::decryptString(Request::input('slot_no'));
        } 
        catch (DecryptException $e) 
        {
            $error_status = 1;
        }
        if($error_status == 0)
        {
            $response = Tbl_slot::where('slot_no',$slot_no)
                      ->leftjoin('users','users.id','=','tbl_slot.slot_owner')
                      ->select('slot_no','first_name','middle_name','last_name', 'email')
                      ->first();
        }
        else
        {
            $response = "invalid";
        }
            return json_encode($response);
    }
    public function new_register_check()
    {
        $register_area = Request::input("register_platform") == "system" ? "register_area" : "social";
		$response = Member::new_register_check(request()->all(),$register_area);
		return $response;
    }
}
