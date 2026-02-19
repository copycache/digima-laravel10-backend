<?php
namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Tbl_survey_answer;
use App\Models\Tbl_survey_choices;
use App\Models\Tbl_survey_question;
use App\Models\Tbl_survey_settings;


class AdminSurveyController extends AdminController
{
    public function get()
    {
        $data = Request::input();
        return Tbl_survey_question::where("survey_archived",$data["archived"] ?? 0)->paginate(10);
    }

    public function settings()
    {
        $response = Tbl_survey_settings::first();

        if(!$response) 
        {
            $insert['survey_limit'] = 20;
            $insert['survey_limit_per_day'] = 4;
            $insert['survey_amount'] = 20;
            $insert['survey_limit_convertion'] = 1000;
            Tbl_survey_settings::insert($insert);
        }
        $response = Tbl_survey_settings::first();
        return $response;
    }

    public function update_settings()
    {
        $data = Request::input();
        if(($data['survey_limit'] ?? null) != null || ($data['survey_limit_per_day'] ?? null) != null || ($data['survey_amount'] ?? null) != null || ($data['survey_limit_convertion'] ?? null) != null) {

            $update['survey_limit'] = $data['survey_limit'] ?? null;
            $update['survey_limit_per_day'] = $data['survey_limit_per_day'] ?? null;
            $update['survey_amount'] = $data['survey_amount'] ?? null;
            $update['survey_limit_convertion'] = $data['survey_limit_convertion'] ?? null;
            Tbl_survey_settings::where('id',$data['id'] ?? null)->update($update);

            $return['status'] = "success";
            $return['message'] = "updated";
        }
        else 
        {
            $return['error'] = "error";
            $return['message'] = "Empty field/s";
        }
        return $return;
    }

    public function id()
    {
        $_survey_id = Request::input("survey_id");

        $response['question_detail'] = Tbl_survey_question::where('id',$_survey_id)->first();
        $response['choices_detail']  = Tbl_survey_choices::where("survey_question_id",$response['question_detail']->id)->where('survey_choices_status',0)->get();

        return $response;
    }

    public function archived()
    {
        // dd(Request::input());
        $_v = Tbl_survey_question::where("id",Request::input())->first()->survey_archived == 0 ? 1 : 0;
        
        Tbl_survey_question::where("id",Request::input())->update(["survey_archived"=>$_v]);

        $response['status'] = 'success';
        $response['status_message'] = 'Updated';

        return $response;
        
    }

    public function add()
    {
        $data = Request::input();
        if(isset($data['question']) && $data['question'] != "" && $data['question'] != 'undefined')
        {
            $passed = true; 
            foreach (($data['choices_fix'] ?? []) as $key => $_choices) 
            {
                if($_choices['survey_choices_details'] == null || $_choices['survey_choices_details'] == "" || $_choices['survey_choices_details'] == 'undefined')
                {
                    $passed = false; 
                }
            }
            if($passed)
            {
                $_question_id = Tbl_survey_question::insertGetId(["survey_question"=>$data['question'],"survey_created_date"=>Carbon::now()]);
                foreach ($data['choices_fix'] as $key => $choices) 
                {
                    
                    $insert['survey_question_id'] = $_question_id;
                    $insert['survey_choices_details'] = $choices['survey_choices_details'];
                    $insert['survey_created_date'] = Carbon::now();
                    Tbl_survey_choices::insert($insert);
                }
                $response['status'] = "success";
                $response['status_message'] = "Question Added";
            }
            else
            {
                $response['status'] = "error";
                $response['status_message'] = "Don't leave choices blank";
            }
        }
        else 
        {
            $response['status'] = "error";
            $response['status_message'] = "Empty input field";
        }
        return $response;
    }

  public function edit()
  {
      $data = Request::input();
    //   dd($data);
        if(isset($data['question']) && $data['question'] != "" && $data['question'] != 'undefined')
        {
            $passed = true; 
            foreach (($data['choices_fix'] ?? []) as $key => $_choices) 
            {
                if($_choices['survey_choices_details'] == null || $_choices['survey_choices_details'] == "" || $_choices['survey_choices_details'] == 'undefined')
                {
                    $passed = false; 
                }
            }
            if($passed)
            {
                if(isset($data['question']) && $data['question'] != '')
                {
                    Tbl_survey_question::where('id',$data['id'])->update(["survey_question"=>$data['question']]);
                    $_old_choices = Tbl_survey_choices::where('survey_question_id',$data['id'])->get();
                    $max = max(count($data['choices_fix']),count($_old_choices));
                    $max = $max;
                    for ($i=0; $i < $max; $i++) 
                    { 
                       if(isset($_old_choices[$i]) && isset($data['choices_fix'][$i]))
                       {
                            $update['survey_question_id']     = $data['id'];
                            $update['survey_choices_details'] = $data['choices_fix'][$i]['survey_choices_details'];
                            $update['survey_choices_status']  = $data['choices_fix'][$i]['survey_choices_status'];
                            Tbl_survey_choices::where('id',$_old_choices[$i]['id'])->update($update);
                       }
                       elseif (isset($_old_choices[$i]) && !isset($data['choices_fix'][$i])) 
                       {
                            Tbl_survey_choices::where('id',$_old_choices[$i]['id'])->update(["survey_choices_status"=>1]);
                       }
                       else 
                       {
                            $insert['survey_question_id']     = $data['id'];
                            $insert['survey_choices_details'] = $data['choices_fix'][$i]['survey_choices_details'];
                            $insert['survey_choices_status']  = $data['choices_fix'][$i]['survey_choices_status'];
                            $insert['survey_created_date']    = Carbon::now();
                            Tbl_survey_choices::insert($insert);
                       }
                    }
                    $response['status'] = "success";
                    $response['status_message'] = "Question Edited";
                }
                else
                {
                    $response['status'] = "error";
                    $response['status_message'] = "Don't leave question blank";
                }
            }
            else
            {
                $response['status'] = "error";
                $response['status_message'] = "Don't leave choices blank";
            }
        }
        else 
        {
            $response['status'] = "error";
            $response['status_message'] = "Empty input field";
        }
        return $response;
  }
}
