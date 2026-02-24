<?php

namespace App\Http\Controllers;

use App\Mail\EmailActivation;
use App\Models\Tbl_verification_codes;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

class AccountActivationController extends Controller
{
    public function resend_verification()
    {
        $user = Request::user();
        $code = Str::random(8);

        Tbl_verification_codes::insert([
            'user_id' => $user->id,
            'code' => $code,
            'date_generate' => Carbon::now(),
        ]);

        Mail::to($user->email)->send(new EmailActivation(Crypt::encryptString($user->id), $user->name, $code));

        return ['status_message' => 'Please check your email to verify your Account!'];
    }
    public function activate_account()
    {
        $user_id = Crypt::decryptString(Request::input('user_id'));
        User::where('id', $user_id)->update(['email_verified' => 1]);
        
        return ['verified' => Crypt::encryptString(1)];
    }
    public function verify_account()
    {
        $user_id = Crypt::decryptString(Request::input('user_id'));
        $code = Request::input('code');

        $verification = Tbl_verification_codes::where(['user_id' => $user_id, 'code' => $code])->first();

        if (!$verification) {
            return 0;
        }

        if ($verification->status != 0) {
            return 2;
        }

        Tbl_verification_codes::where('code', $code)->update(['date_used' => Carbon::now(), 'status' => 1]);
        User::where('id', $user_id)->update(['email_verified' => 1]);

        return 1;
    }
}
