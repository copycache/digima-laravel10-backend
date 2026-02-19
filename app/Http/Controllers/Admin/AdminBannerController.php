<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tbl_banner;


use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class AdminBannerController extends Controller
{
    public function add()
    {
        $data                                               = Request::input('data');
        $rules["filename"]                                  = "required|unique:tbl_banner,name";
        $rules["place"]                                     = "required";
        $rules["description"]                               = "required";
        $rules["thumbnail"]    	                            = "required";
        
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
            $insert['name']                                 = $data['filename'];
            $insert['description']                          = $data['description'];
            $insert['place']                                = $data['place'];
            $insert['thumbnail']                            = $data['thumbnail'];
            $insert['created_at']                           = Carbon::now();
            Tbl_banner::insert($insert);

            $return["status"]                               = "Success"; 
            $return["status_code"]                          = 400; 
            $return["status_message"]                       = 'Successfully Saved!';
        }
        return $return;
    }
    public function get_data()
    {
        $filter   = Request::input('filter');
        $response = Tbl_banner::where('archived',$filter['filter'])->where('name','LIKE','%' . $filter['search'] . '%')->get();

        return $response;
    }
    public function edit()
    {
       $id       = Request::input('id');
       $response = Tbl_banner::where('id',$id)->first();

       return $response;
    }
    public function update()
    {
        $data                                               = Request::input();
        $id                                                 = $data['id'];

        $rules["filename"]                                  = "required|unique:tbl_banner,name,$id";
        $rules["description"]                               = "required";
        $rules["place"]                                     = "required";
        $rules["dft_thumbnail"]                             = "required";
        
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
            $update['name']                                 = $data['filename'];
            $update['description']                          = $data['description'];
            $update['place']                                = $data['place'];
            $update['thumbnail']                            = $data['dft_thumbnail'];
            $update['created_at']                           = Carbon::now();
            $update['updated_at']                           = Carbon::now();

            Tbl_banner::where('id',$id)->update($update);

            $return["status"]                               = "Success"; 
            $return["status_code"]                          = 200; 
            $return["status_message"]                       = "Updated Successfully!";
        }
        return $return;
    }
    public function delete()
    {
        $id                                                 = Request::input('id');

        $update['archived']                                 = 1;
        
        Tbl_banner::where('id',$id)->update($update);

        $return["status"]                                   = "Success"; 
        $return["status_code"]                              = 200; 
        $return["status_message"]                           = "Deleted Successfully!";

        return $return;
    }
    public function restore()
    {
        $id                                                 = Request::input('id');

        $update['archived']                                 = 0;
        
        Tbl_banner::where('id',$id)->update($update);

        $return["status"]                                   = "Success"; 
        $return["status_code"]                              = 200; 
        $return["status_message"]                           = "Restored Successfully!";

        return $return;
    }
}
