<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tbl_slot;
use App\Models\Tbl_user_process;
use App\Mail\ResetPassword;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Mail;
use Hash;
use Carbon\Carbon;
use App\Globals\Log;
use Illuminate\Support\Facades\Crypt;
class ForgotPasswordController extends Controller
{
     public function send_mail(Request $request)
    {
        $email                       = $request->email;
        $data                        = DB::table('users')->where('email',$email)->first(['id','name']);
        
        if($data != null && $email != null)	
        {
        	$random = str_shuffle('abcdefghjklmnopqrstuvwxyzABCDEFGHJKLMNOPQRSTUVWXYZ1234567890');
			$OTP = substr($random, 0, 8);

            $id                       = Crypt::encrypt($data->id);
            $decryt_id                = $data->id;
            $name                     = $data->name;
            
            $check = DB::table('tbl_reset_password_timeout')->where('user_id',$decryt_id)->first();

            if($check != null)
            {

            	if ($check->counter <= 2) 
            	{
	                Mail::to($email)->send(new ResetPassword($id, $name, $OTP));
		            $return["status"]         = "success";
		            $return["status_code"]    = 200;
		            $return["status_message"] = "Visit your email to reset your password";

	                $update['created_at'] = Carbon::now();
	                $update['counter']    = $check->counter+1;
	                $update['OTP']   	  = $OTP;
	                $update['status']     = 0;
	                DB::table('tbl_reset_password_timeout')->where('user_id',$decryt_id)->update($update);
            	}
            	else
            	{
            		$date 	  	= Carbon::parse($check->created_at)->format('Y m d');
        			$date_now 	= Carbon::parse(Carbon::now())->format('Y m d');
        			if($date ==  $date_now)
        			{
        				$return["status"]         = "error";
			            $return["status_code"]    = 500;
			            $return["status_message"] = "You've reach the maximum attempts for resetting your password today. Try again later!";
        			}
        			else
        			{	
	            		Mail::to($email)->send(new ResetPassword($id, $name, $OTP));
			            $return["status"]         = "success";
			            $return["status_code"]    = 200;
			            $return["status_message"] = "Visit your email to reset your password";

		                $update['created_at'] 	  = Carbon::now();
		                $update['counter']    	  = 1;
		                $update['OTP']   	  	  = $OTP;
		                $update['status']     	  = 0;
		                DB::table('tbl_reset_password_timeout')->where('user_id',$decryt_id)->update($update);
        			}
            	}
            }
            else
            {
            	Mail::to($email)->send(new ResetPassword($id, $name, $OTP));
	            $return["status"]         = "success";
	            $return["status_code"]    = 200;
	            $return["status_message"] = "Visit your email to reset your password";

                $insert['user_id']    = $decryt_id;
                $insert['created_at'] = Carbon::now();
                $insert['counter']    = 1;
                $insert['OTP']   	  = $OTP;
                $insert['status']     = 0;
                DB::table('tbl_reset_password_timeout')->insert($insert);
            }
        }
        else
        {
            $return["status"]         = "error";
            $return["status_code"]    = 500;
            $return["status_message"] = "Invalid Email address";
        }     
        return response()->json($return);
    }
    public function create_new_pass(Request $request)
    {

        $id       = $request->id;
        $password = $request->password;
        $confirm  = $request->confirm;
        $id       = Crypt::decrypt($id);

        if($password == null && $confirm == null)
        {
            $return["status"]         = "error";
            $return["status_code"]    = 500;
            $return["status_message"] = "Password  is required";
        }
        else
        {
        	$data = DB::table('tbl_reset_password_timeout')->where('user_id',$id)->first();
	        $date = Carbon::parse($data->created_at)->format('U');
	        $date_now = Carbon::parse(Carbon::now())->format('U');

	        if ($date_now - $date > 15 * 60) 
	        {
	        	$return["status"]         = "error";
		        $return["status_code"]    = 500;
		        $return['request_timeout']= 1;
		        $return["status_message"] = "You've reach the maximum time for resetting your password. Send email verification again!";   
	        }
	        else
	        {
	        	if($password == $confirm)
	            {
	                $update["password"]       = Hash::make($confirm);
	                $update["crypt"]          = Crypt::encryptString($confirm);
	                DB::table('users')->where('id',$id)->update($update);
	                $return["status"]         = "success";
	                $return["status_code"]    = 200;
	                $return["status_message"] = "Password updated successfully";
	            }
	            else
	            {
	                $return["status"]         = "error";
	                $return["status_code"]    = 500;
	                $return["status_message"] = "Password didn't match";
	            }
	        }
            
        }
        return response()->json($return,200);
    }

    public function check_timeout(Request $request)
    {
    	$id       	= $request->id;
        $id       	= Crypt::decrypt($id);
        $data 		= DB::table('tbl_reset_password_timeout')->where('user_id',$id)->first();
        $date 		= Carbon::parse($data->created_at)->format('U');
        $date_now 	= Carbon::parse(Carbon::now())->format('U');

        if ($date_now - $date > 15 * 60) 
        {
            $return["status_code"]    = 500;
        }
        else
        {
            $return["status_code"]    = 200;
            
        }
        return response()->json($return,200);
    }
    public function OTP_check(Request $request)
    {
    	$id       	= $request->id;
    	$OTP 	  	= $request->OTP;
        $id       	= Crypt::decrypt($id);
        $data 	  	= DB::table('tbl_reset_password_timeout')->where('user_id',$id)->first();
        $date 	  	= Carbon::parse($data->created_at)->format('U');
        $date_now 	= Carbon::parse(Carbon::now())->format('U');

        if ($date_now - $date > 15 * 60) 
        {
            $return["status"]         		  = "error";
		    $return["status_code"]    		  = 500;
		    $return['request_timeout']		  = 1;
		    $return["status_message"] 		  = "OTP (One time password) has been expired. Send email verification again!";   
        }
        else
        {
    		if ($OTP == $data->OTP) 
	        {
	        	if($data->status == 1)
	        	{
	        		$return["status"]         	  = "error";
				    $return["status_code"]    	  = 500;
				    $return["status_message"] 	  = "This code is already used!";
	        	}
	        	else
	        	{
	        		$update['status']		  = 1;
		        	DB::table('tbl_reset_password_timeout')->where('user_id',$id)->update($update);

		            $return["status"]         = "success";
		            $return["status_code"]    = 200;
		            $return["status_message"] = "Success";
	        	}
	        }
	        else
	        {
	    		$return["status"]         = "error";
	            $return["status_code"]    = 500;
	            $return["status_message"] = "Invalid Code";
	        }
        }
        return response()->json($return,200);
    }
}
