<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;

use App\Models\Tbl_product_category;
use App\Models\Tbl_product_subcategory;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\DB;

class AdminProductCategoryController extends Controller
{
    public function add_category()
    {
        $response[0]                                    = array('id'=>0,'category_id'=>0,'sub_category_name'=>"",'archive'=>0);

        return $response;
    }
    public function save_category()
    {
        $category_name['category_name']                        = Request::input('category_name');
        $sub_category                                          = Request::input('sub_category');


        $rules["category_name"]    		                       = "required|unique:tbl_product_category,category_name";

        $validator = Validator::make($category_name, $rules);

        if ($validator->fails())
        {
            $response['status_code']                            = 401;
            $i = 0;
            foreach ($validator->errors()->getMessages() as $key => $value)
			{
				foreach($value as $val)
				{
					$response["status_message"][$i]            = $val;
				  $i++;
				}
			}
        }
        else
        {
            $category_id                                        = Tbl_product_category::insertGetId($category_name);
            
            foreach ($sub_category as $key => $value) {
    
                if($value['archive'] == 0)
                {
                    if($value['sub_category_name'])
                    {
                        $insert['category_id']                  = $category_id;
                        $insert['sub_category_name']            = $value['sub_category_name'];
    
                        Tbl_product_subcategory::insert($insert);
                    }
                }
            }

            $response['status_code']                            = 200;
            $response['status_message']                         = 'Category is successfully added!';
        }
        return $response;
    }
    public function load_data()
    {
        $response = Tbl_product_category::where('archive',0)->get();

        return $response;
    }
    public function edit()
    {
        $category_id                                            = Request::input('id');


        $response['category']                                   = Tbl_product_category::where('id',$category_id)->pluck('category_name')->first(); 
        $response['sub_category']                               = Tbl_product_subcategory::where('category_id',$category_id)->get();     

        if(count($response['sub_category']) == 0)
        {
            $response['sub_category'][0]                       = array('id'=>0,'category_id'=>0,'sub_category_name'=>"",'archive'=>0);     
        }
        return $response;
    }
    public function update_category()
    {
        $id                                                     = Request::input('id');
        $sub_category                                           = Request::input('sub_category');
        $category_name['category_name']                         = Request::input('category_name');

        Tbl_product_category::where('id',$id)->update($category_name);

        foreach ($sub_category as $key => $value) {

            DB::table('tbl_product_subcategory')->updateOrInsert(
            [
                'id'							                => $value['id'] ?? 0,
            ],          
            [           
                'category_id'					                => $id,
                'sub_category_name'					            => $value['sub_category_name'],		
                'archive'					                    => $value['archive'],
            ]);
             
        }

        $response['status_message']                             = 'Category updated successfully';

        return $response;
    }
    public function delete_category()
    {
        $id                                                     = Request::input('id');

        Tbl_product_category::where('id',$id)->update(['archive' => 1]);

        $response['status_message']                             = 'Category deleted successfully';

        return $response;
    }
}
