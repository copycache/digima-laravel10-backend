<?php
namespace App\Http\Controllers\Member;

use Carbon\Carbon;
use Illuminate\Support\Facades\Request;

use App\Globals\Slot;
use App\Globals\User_process;
use App\Globals\Log;

use App\Models\Tbl_slot;
use App\Models\Tbl_survey_answer;
use App\Models\Tbl_survey_choices;
use App\Models\Tbl_survey_question;
use App\Models\Tbl_survey_settings;
use App\Models\Tbl_currency;

class MemberSurveyController extends MemberController
{
    private  $survey_limit = 20;
    private  $survey_limit_per_day = 4;
    private  $survey_amount = 20;
    private  $survey_limit_convertion = 1000;
    private  $today = null;
    private  $from = null;
    private  $to = null;

    public function __construct()
    {
        $this->today = Carbon::now();
        $this->from = Carbon::now()->startofDay();
        $this->to = Carbon::now()->endofDay();
        $settings = Tbl_survey_settings::first();
        if($settings) 
        {
            $this->survey_limit = $settings->survey_limit;
            $this->survey_limit_per_day = $settings->survey_limit_per_day;
            $this->survey_amount = $settings->survey_amount;
            $this->survey_limit_convertion = $settings->survey_limit_convertion;
        }


    }

    public function survey_init()
    {
        $_slot_id = Request::input('slot_id');
        $_owner_id = Request::user()->id;
        $_check_ownership = Tbl_slot::where('slot_id',$_slot_id)->where('slot_owner',$_owner_id)->first();
        if($_check_ownership)
        {
            $_count_survey_answered = Tbl_survey_answer::where('slot_id',$_slot_id)->count();
            if($_count_survey_answered < $this->survey_limit)
            {
                $_remaining_attempt = $this->survey_limit - $_count_survey_answered;
                $_count_survey_answered_today = Tbl_survey_answer::where('slot_id',$_slot_id)->whereDate('survey_created_date',">=",$this->from)->whereDate('survey_created_date',"<=",$this->to)->count();
                // dd($_count_survey_answered_today);
                if ($_count_survey_answered_today < $this->survey_limit_per_day) 
                {
                    $_remaining_attempt_today = $this->survey_limit_per_day - $_count_survey_answered_today;
                    if ($_remaining_attempt_today <= $_remaining_attempt) 
                    {
                        $response['status'] = 'success';
                        $response['status_message'] = 'Survey Question Available, Count: '.$_remaining_attempt_today;
                    } 
                    else 
                    {
                        $response['status'] = 'success';
                        $response['status_message'] = 'Survey Question Available, Count: '.$_remaining_attempt;
                    }
                    
                } 
                else 
                {
                    $response['status'] = 'warning';
                    $response['status_message'] = 'Limit Reached';
                }
                
            }
            else 
            {
                $response['status'] = 'warning';
                $response['status_message'] = 'Limit Reached';    
            }
        }
        else 
        {
            $response['status'] = 'error';
            $response['status_message'] = 'Something went wrong!';    
        }
        return $response;
    }
    
    public function survey_question()
    {
        $_slot_id = Request::input('slot_id');
        $_answer_questions = Tbl_survey_answer::where('slot_id',$_slot_id)->get(['survey_question_id']);
        $response['questions'] = Tbl_survey_question::wherenotIn('id',$_answer_questions)->inRandomOrder()->first();
        if($response['questions'] != null )
        {
            $response['choices']   = Tbl_survey_choices::where('survey_question_id',$response['questions']['id'])->where('survey_choices_status',0)->get();
            foreach ($response['choices'] as $key => $value) 
            {
                $response['choices'][$key]->selected = 0;
            }         
        }
        else 
        {
            $response = null;
        }
    
          
        return $response;
    }

    public function survey_answer()
    {
        $_data = Request::input();
        $owner_id = Request::user()->id;
        $dup_check = User_process::check($owner_id);
        $details = "";
        if($dup_check == 0) 
        {
            $_count_survey_answered = Tbl_survey_answer::where('slot_id',$_data['slot_id'])->count();
            if($_count_survey_answered < $this->survey_limit)
            {
                $_count_survey_answered_today = Tbl_survey_answer::where('slot_id',$_data['slot_id'])->whereDate('survey_created_date',$this->today)->count();
                if ($_count_survey_answered_today < $this->survey_limit_per_day) 
                {
                    $_is_empty = 1;
                    foreach ($_data['choices'] as $key => $_datum) 
                    {
                        if($_datum['selected'] == 1)
                        {
                            $insert['survey_question_id']   = $_datum['survey_question_id'];
                            $insert['survey_choices_id']    = $_datum['id'];
                            $insert['slot_id']              = $_data['slot_id'];
                            $insert['survey_created_date']  = $this->today;

                            Tbl_survey_answer::insert($insert);

                            $_is_empty = 0;
                        }
                    }

                    if ($_is_empty == 0) 
                    {
                        $_SP = Tbl_currency::where("currency_abbreviation","SP")->first();

                        Log::insert_wallet($_data['slot_id'],$this->survey_amount,"SURVEY",$_SP->currency_id);
                        Log::insert_earnings($_data['slot_id'],$this->survey_amount,"SURVEY","SPECIAL PLAN",$_data['slot_id'],$details,0,$_SP->currency_id);

                        $_SP_wallet = Tbl_slot::where("tbl_slot.slot_id",$_data['slot_id'])->Wallet($_SP->currency_id)->first();
                        if ($_SP_wallet->wallet_amount >= $this->survey_limit_convertion) 
                        {
                            Log::insert_wallet($_data['slot_id'],$_SP_wallet->wallet_amount * -1,"SURVEY",$_SP->currency_id);
                            Log::insert_earnings($_data['slot_id'],$_SP_wallet->wallet_amount * -1,"SURVEY","SPECIAL PLAN",$_data['slot_id'],$details,0,$_SP->currency_id);

                            Log::insert_wallet($_data['slot_id'],$_SP_wallet->wallet_amount,"SURVEY_POINTS_CONVERSION");
                            Log::insert_earnings($_data['slot_id'],$_SP_wallet->wallet_amount,"SURVEY_POINTS_CONVERSION","SPECIAL PLAN",$_data['slot_id'],$details,0);
                        }

                        $response['status'] = 'success';
                        $response['status_message'] = "Success";
                    }
                    else 
                    {
                        $response['status'] = 'error';
                        $response['status_message'] = "Please choose an answer.";
                    }
                } 
                else 
                {
                    $response['status'] = 'warning';
                    $response['status_message'] = 'Limit Reached';
                }
            }
            else 
            {
                $response['status'] = 'warning';
                $response['status_message'] = 'Limit Reached';    
            }
        }
        else 
        {
            $response['status'] = 'error';
            $response['status_message'] = 'Oops something went wrong!'; 
        }
        return $response;
    }
}
