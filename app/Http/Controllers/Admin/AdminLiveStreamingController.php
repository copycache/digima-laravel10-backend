<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tbl_live_streaming_settings;


use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class AdminLiveStreamingController extends Controller
{
    public function add()
    {
        $data                                               = Request::input('data');
        $rules["title"]                                     = "required|unique:tbl_live_streaming_settings,title";
        $rules["description"]                               = "required";
        $rules["code"]     	                                = "required";
        
        $validator = Validator::make($data, $rules);

        if ($validator->fails()) 
        {
            $return["status"]                               = "Error"; 
            $return["status_code"]                          = 400; 
            $return["status_message"]                       = [];

            $i = 0;
            foreach ($validator->errors()->getMessages() as $key => $value) 
            {
                foreach($value as $val)
                {
                    $return["status_message"][$i]           = $val;
                    $i++;		
                }
            }
        }
        else
        {
            if(($data['live_status'] ?? null) == 1)
            {
                $check_live_status = Tbl_live_streaming_settings::where('live_status',1)->count();

                if($check_live_status > 0)
                {
                    $return["status"]                        = "Error"; 
                    $return["status_code"]                   = 500; 
                    $return["status_message"]                = 'Already have an active Live Streaming at this time';
                }
                else
                {
                    goto save_info;
                }
            }
            else
            {
                save_info:
                $insert['title']                             = $data['title'];
                $insert['description']                       = $data['description'];
                $insert['code']                              = $data['code'];
                $insert['live_status']                       = $data['live_status'];
                $insert['created_at']                        = Carbon::now();
                Tbl_live_streaming_settings::insert($insert);
    
                $return["status"]                            = "Success"; 
                $return["status_code"]                       = 200; 
                $return["status_message"]                    = 'Successfully Saved!';
            }
        }
        return $return;
    }
    public function get_data()
    {
        $filter   = Request::input('filter');
        $response = Tbl_live_streaming_settings::where('archived',$filter['filter'])->where('title','LIKE','%' . $filter['search'] . '%')->get();

        return $response;
    }
    public function edit()
    {
       $id       = Request::input('id');
       $response = Tbl_live_streaming_settings::where('id',$id)->first();

       return $response;
    }
    public function update()
    {
        $data                                               = Request::input();
        $id                                                 = $data['id'];

        $rules["title"]                                     = "required|unique:Tbl_live_streaming_settings,title,$id";
        $rules["description"]                               = "required";
        $rules["code"]                                      = "required";
        
        $validator = Validator::make($data, $rules);

        if ($validator->fails()) 
        {
            $return["status"]                               = "Error"; 
            $return["status_code"]                          = 400; 
            $return["status_message"]                       = [];

            $i = 0;
            foreach ($validator->errors()->getMessages() as $key => $value) 
            {
                foreach($value as $val)
                {
                    $return["status_message"][$i] = $val;
                    $i++;		
                }
            }
        }
        else
        {
            if(($data['live_status'] ?? null) == 1)
            {
                $check_live_status = Tbl_live_streaming_settings::where('live_status',1)->where('id','!=',$id)->count();

                if($check_live_status > 0)
                {
                    $return["status"]                        = "Error"; 
                    $return["status_code"]                   = 500; 
                    $return["status_message"]                = 'Already have an active Live Streaming at this time';
                }
                else
                {
                    goto update_info;
                }
            }
            else
            {
                update_info:
                $update['title']                                = $data['title'];
                $update['description']                          = $data['description'];
                $update['code']                                 = $data['code'];
                $update['live_status']                          = $data['live_status'];
                $update['updated_at']                           = Carbon::now();

                Tbl_live_streaming_settings::where('id',$id)->update($update);

                $return["status"]                               = "Success"; 
                $return["status_code"]                          = 200; 
                $return["status_message"]                       = "Updated Successfully!";
            }
        }
        return $return;
    }
    public function delete()
    {
        $id                                                 = Request::input('id');
        $update['live_status']                              = 0;
        $update['archived']                                 = 1;
        
        Tbl_live_streaming_settings::where('id',$id)->update($update);

        $return["status"]                                   = "Success"; 
        $return["status_code"]                              = 200; 
        $return["status_message"]                           = "Deleted Successfully!";

        return $return;
    }
    public function restore()
    {
        $id                                                 = Request::input('id');
        $update['archived']                                 = 0;
        
        Tbl_live_streaming_settings::where('id',$id)->update($update);

        $return["status"]                                   = "Success"; 
        $return["status_code"]                              = 200; 
        $return["status_message"]                           = "Restored Successfully!";

        return $return;
    }
}

