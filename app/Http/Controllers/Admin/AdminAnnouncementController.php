<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tbl_announcement;


use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class AdminAnnouncementController extends Controller
{
    public function add()
    {
        $data                                               = Request::input('data');
        $rules["title"]                                     = "required|unique:tbl_announcement,title";
        $rules["description"]                               = "required";
        
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
            $insert['title']                                = $data['title'];
            $insert['description']                          = $data['description'];
            $insert['status']                               = $data['status'];
            $insert['created_at']                           = Carbon::now();
            Tbl_announcement::insert($insert);

            $return["status"]                               = "Success"; 
            $return["status_code"]                          = 200; 
            $return["status_message"]                       = 'Successfully Saved!';
        }
        return $return;
    }
    public function get_data()
    {
        $filter   = Request::input('filter');
        $response = Tbl_announcement::where('archived',$filter['filter'])->where('title','LIKE','%' . $filter['search'] . '%')->get();

        return $response;
    }
    public function edit()
    {
       $id       = Request::input('id');
       $response = Tbl_announcement::where('id',$id)->first();

       return $response;
    }
    public function update()
    {
        $data                                               = Request::input();
        $id                                                 = $data['id'];

        $rules["title"]                                     = "required|unique:Tbl_announcement,title,$id";
        $rules["description"]                               = "required";
        
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
            $update['title']                                = $data['title'];
            $update['description']                          = $data['description'];
            $update['status']                               = $data['status'];
            $update['updated_at']                           = Carbon::now();

            Tbl_announcement::where('id',$id)->update($update);

            $return["status"]                               = "Success"; 
            $return["status_code"]                          = 200; 
            $return["status_message"]                       = "Updated Successfully!";
        }
        return $return;
    }
    public function delete()
    {
        $id                                                 = Request::input('id');
        $update['status']                                   = 0;
        $update['archived']                                 = 1;
        
        Tbl_announcement::where('id',$id)->update($update);

        $return["status"]                                   = "Success"; 
        $return["status_code"]                              = 200; 
        $return["status_message"]                           = "Deleted Successfully!";

        return $return;
    }
    public function restore()
    {
        $id                                                 = Request::input('id');
        $update['archived']                                 = 0;
        
        Tbl_announcement::where('id',$id)->update($update);

        $return["status"]                                   = "Success"; 
        $return["status_code"]                              = 200; 
        $return["status_message"]                           = "Restored Successfully!";

        return $return;
    }
}
