<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\DB;
use App\Mail\EmailActivation;
use App\Models\Users;
use App\Models\Tbl_verification_codes;
use Carbon\Carbon;

use Mail;
use Crypt;

class AccountActivationController extends Controller
{
    public function resend_verification()
    {
        $random                             = str_shuffle('abcdefghjklmnopqrstuvwxyzABCDEFGHJKLMNOPQRSTUVWXYZ1234567890');
        $code                               = substr($random, 0, 8);
        $insert['user_id']                  = Request::user()->id;
        $insert['code']                     = $code;
        $insert['date_generate']            = Carbon::now();

        Tbl_verification_codes::insert($insert);

        $id                                     = Crypt::encryptString(Request::user()->id);
        $name                                   = Request::user()->name;
        $email                                  = Request::user()->email;

        Mail::to($email)->send(new EmailActivation($id, $name, $code));

        $response['status_message']             = 'Please check your email to verify your Account!';
        return $response;

    }
    public function activate_account()
    {
        
        $user_id                                = Crypt::decryptString(Request::input('user_id'));
        $response['verified']                   = Crypt::encryptString(1);
        
        $update['email_verified']               = 1;
        Users::where('id', $user_id)->update($update);
        
        return $response;
    }
    public function verify_account()
    {
        $user_id                                = Crypt::decryptString(Request::input('user_id'));
        $code                                   = Request::input('code');

        $check_if_exist                         = Tbl_verification_codes::where('user_id',$user_id)->where('code',$code)->first();

       if($check_if_exist)
       {
           if($check_if_exist->status == 0)
           {
               $update['date_used']             = Carbon::now(); 
               $update['status']                = 1;
               
               Tbl_verification_codes::where('code',$code)->update($update);
               Users::where('id',$user_id)->update(['email_verified' => 1]);

               $response                        = 1;
           }
           else
           {
               $response                        = 2;
           }
       }
       else
       {
           $response                            = 0;
       }

       return $response;
    }
}
