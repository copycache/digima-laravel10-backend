<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tbl_ai_marketing_tools;
use App\Models\Tbl_ebooks;
use App\Models\Tbl_marketing_tools_category;
use App\Models\Tbl_marketing_tools_subcategory;
use App\Models\Tbl_membership;


use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class AdminEbooksController extends Controller
{
    public function add()
    {
        $data                                               = Request::input('data');
        $rules["title"]                                     = "required|unique:tbl_ai_marketing_tools,title";
        $rules["membership_id"]                             = "required";
        $rules["category"]    	                            = "required";
        $rules["sub_category"]    	                        = "required";
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
            $insert['category']                             = $data['category'];
            $insert['sub_category']                         = $data['sub_category'];
            $insert['title']                                = $data['title'];
            $insert['details']                              = $data['details'];
            $insert['thumbnail']                            = $data['thumbnail'];
            $insert['image_link']                           = json_encode($data['image_link']);
            $insert['video_link']                           = $data['video_link'];
            $insert['file_link']                            = json_encode($data['file_link']);
            $insert['membership_id']                        = $data['membership_id'] == -1 ? null : $data['membership_id'];
            $insert['created_at']                           = Carbon::now();
            Tbl_ai_marketing_tools::insert($insert);

            $return["status"]                               = "Success"; 
            $return["status_code"]                          = 200; 
            $return["status_message"]                       = 'Successfully Saved!';
        }
        return $return;
    }
    public function get_data()
    {
        $filter   = Request::input('filter');
        $search   = Request::input('search');
        $response = Tbl_ai_marketing_tools::where('tbl_ai_marketing_tools.archived',$filter)
        ->where('title','LIKE','%' . $search . '%')
        ->category()
        ->SubCategory()
        ->select('tbl_ai_marketing_tools.*', 'tbl_marketing_tools_category.category_name', 'tbl_marketing_tools_subcategory.sub_category_name')
        ->paginate(15);

        return response()->json($response); 
    }
    public function edit()
    {
       $id       = Request::input('id');
       $response = Tbl_ai_marketing_tools::where('id',$id)->first();

       return $response;
    }
    public function update()
    {
        $data                                               = Request::input();
        $id                                                 = $data['id'];

        $rules["title"]                                     = "required";
        $rules["membership_id"]                             = "required";
        $rules["category"]    	                            = "required";
        $rules["sub_category"]    	                        = "required";
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
                    $return["status_message"][$i] = $val;
                    $i++;		
                }
            }
        }
        else
        {
            $update['category']                             = $data['category'];
            $update['sub_category']                         = $data['sub_category'];
            $update['title']                                = $data['title'];
            $update['details']                              = $data['details'];
            $update['thumbnail']                            = $data['thumbnail'];
            $update['image_link']                           = json_encode($data['image_link']);
            $update['video_link']                           = $data['video_link'];
            $update['file_link']                           = json_encode($data['file_link']);
            $update['membership_id']                        = $data['membership_id'] == -1 ? null : $data['membership_id'];
            $update['updated_at']                           = Carbon::now();
            Tbl_ai_marketing_tools::where('id',$id)->update($update);

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
        
        Tbl_ai_marketing_tools::where('id',$id)->update($update);

        $return["status"]                                   = "Success"; 
        $return["status_code"]                              = 200; 
        $return["status_message"]                           = "Deleted Successfully!";

        return $return;
    }
    public function restore()
    {
        $id                                                 = Request::input('id');

        $update['archived']                                 = 0;
        
        Tbl_ai_marketing_tools::where('id',$id)->update($update);

        $return["status"]                                   = "Success"; 
        $return["status_code"]                              = 200; 
        $return["status_message"]                           = "Restored Successfully!";

        return $return;
    }
    public function load_membership()
    {
        $response = Tbl_membership::where('archive',0)->get();

        return $response;
    }

    public function load_category()
    {
        $response = Tbl_marketing_tools_category::where("archived", 0)->get();

        return $response;
    }

    public function load_subcategory()
    {
        $category_list = Tbl_marketing_tools_category::where('archived', 0)->get();

        foreach($category_list as $index => $category) {
            $response[$index] = Tbl_marketing_tools_subcategory::where('category_id', $category->id)->where('archived', 0)->get();
        }

        return $response;
    }
    public function save_category()
    {
        $data = Request::input();
        $category_list = $data["category"];
        $subcategory_list = $data["subcategory"];
        foreach($category_list as $index => $category) {
            if(!isset($category['category_name'])) {
                $return["status"] = "Error"; 
                $return["status_code"] = 400; 
                $return["status_message"] = "Please input Category Name!";

                return $return;
            } else {
                if(isset($category['created_at'])) {
                    $update['category_name'] = $category['category_name'];
                    $update['image_required'] = $category['image_required'];
                    $update['video_required'] = $category['video_required'];
                    $update['file_required'] = $category['file_required'];
                    $update['updated_at'] = Carbon::now();
                    $update['archived'] = $category['archived'];
                    Tbl_marketing_tools_category::where('id',$category['id'])->update($update);
                    
                } else {
                    $insert['category_name'] = $category['category_name'];
                    $insert['image_required'] = $category['image_required'];
                    $insert['video_required'] = $category['video_required'];
                    $insert['file_required'] = $category['file_required'];
                    $insert['created_at'] = Carbon::now();
                    $insert['archived'] = $category['archived'];
                    Tbl_marketing_tools_category::insert($insert);
                }
            }
        }
        $category_list = Tbl_marketing_tools_category::where('archived', 0)->get();
   
        foreach($category_list as $index => $category) {
            foreach($subcategory_list[$index] as $subcategory) {
                if(!isset($subcategory['sub_category_name'])) {
                    $return["status"] = "Error"; 
                    $return["status_code"] = 400; 
                    $return["status_message"] = "Please input Subcategory Name!";

                    return $return;
                }
                else {
                    if(isset($subcategory['created_at'])) {
                        $update2['sub_category_name'] = $subcategory['sub_category_name'];
                        $update2['updated_at'] = Carbon::now();
                        $update2['archived'] = $subcategory['archived'];
                        Tbl_marketing_tools_subcategory::where('id',$subcategory['id'])->update($update2);
                    } else {
                        $insert2['category_id'] = $category['id'];
                        $insert2['sub_category_name'] = $subcategory['sub_category_name'];
                        $insert2['created_at'] = Carbon::now();
                        $insert2['archived'] = $subcategory['archived'];
    
                        Tbl_marketing_tools_subcategory::insert($insert2);
                    }
                }
                
            } 
            
        }

        $return["status"]                                   = "Success"; 
        $return["status_code"]                              = 200; 
        $return["status_message"]                           = "Successfully Save!";
        

        return $return;
    }
}
