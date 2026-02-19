<?php
namespace App\Http\Controllers\Admin;

use App\Globals\Eloading;
use App\Globals\Audit_trail;
use App\Models\Tbl_eloading_product;
use App\Models\Tbl_eloading_settings;
use App\Models\Tbl_eloading_tab_settings;
use Illuminate\Support\Facades\Request;
use Hash;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use Excel;
class AdminEloadingController extends AdminController
{
	public function get_eload_product()
	{
		// if(Request::input('action')=="all")
		// {
		// 	$response = Tbl_eloading_product::get();
		// }	
		// else
		// {
			$response = Tbl_eloading_product::where('eloading_product_code','!=','');
			if(Request::input('filter')!="all")
			{
				$response = $response->where('eloading_product_type',Request::input('filter'));
			}
			if(Request::input('search')!=null)
			{
				$response = $response->Search(Request::input('search'));
			}
			$response = $response->get();
		// }
		return response()->json($response);
	}

	public function get_settings()
	{
		$user = Request::user()->id;
		if(Request::input('settings')=="get")
		{
			$response = Eloading::get_settings();
		}
		else
		{
			//audit trail
			$old_value['eload'] = Tbl_eloading_settings::where('eloading_settings_id',1)->first();
			$old_value['module'] = Tbl_eloading_tab_settings::get();
			//
			$eload = Request::input('eload');
			$module = Request::input('module');
			$update['eloading_additional_wallet_percentage'] 	= $eload['eloading_additional_wallet_percentage'];
			$update['eloading_discount_wallet_percentage'] 		= $eload['eloading_discount_wallet_percentage'];
			$update['eloading_is_active'] 						= $eload['eloading_is_active'];

			Tbl_eloading_settings::where('eloading_settings_id',1)->update($update);
			foreach ($module as $key => $value) 
			{
				Tbl_eloading_tab_settings::where('eloading_tab_id',$value['eloading_tab_id'])->update($value);
			}
			//audit trail
			$new_value['eload'] = Tbl_eloading_settings::where('eloading_settings_id',1)->first();
			$new_value['module'] = Tbl_eloading_tab_settings::get();
			//
			$action = 'Update eloading settings';
			Audit_trail::audit(serialize($old_value['eload']),serialize($new_value['eload']),$user,$action);
			$action = 'Update eloading settings tab';
			Audit_trail::audit(serialize($old_value['module']),serialize($new_value['module']),$user,$action);

			$response = "SETTINGS UPDATED";
		}
		
		return response()->json($response);
	}

	public function reset_product()
	{
		$user = Request::user()->id;
		Tbl_eloading_product::truncate();
		$action = 'Reset Eloading Items';
		$old_value = null;
		$new_value = null;
		Audit_trail::audit($old_value,$new_value,$user,$action);
		$response  = "TABLE RESET";
		return response()->json($response);
	}

	public function get_eload_logs()
	{
		$response = Eloading::get_eload_logs();

		return $response;
	}

	
	public function import_excel()
	{
		$user           = Request::user();
		$stat           = "success";
		$file         	= Request::file('file_data')->getRealPath();
		$_data        	= Excel::selectSheetsByIndex(0)->load($file, function($reader){})->all();
		$first        	= $_data[0]; 
		
		if(isset($first['product_name'])&&isset($first['product_code']))
		{  
		    $count_success 		= 0;
			$count_error        = 0;
			
			foreach($_data as $key_row =>$data)
			{
				$product_check    = Tbl_eloading_product::where('eloading_product_code',$data['product_code'])->count();
				if($product_check!=0||$data['product_code']==null||$data['product_name']==null)
				{
					Self::excel_error($data,'Product code exist',$key_row);
					$count_error++;
				}
				else
				{
					$insert['eloading_product_name']        =   $data['product_name'];
					$insert['eloading_product_code']       	=   $data['product_code'];
					$insert['eloading_product_description'] =   $data['product_description']; 
					$insert['eloading_product_validity']    =   $data['product_validity'];
					$insert['eloading_product_guide']    	=   $data['product_guide'];
					$insert['eloading_product_subscriber']  =   $data['product_subscriber'];
					$insert['eloading_product_type']        =   $data['product_type'];
					
					Tbl_eloading_product::insert($insert);
					
					$count_success++;
				}
			}
		}
		else
		{
			$stat         = "error";
		}

		$action = 'Importation Eloading Items';
		$old_value = null;
		$count['success'] =  $count_success;
		$count['error']   =  $count_error;
		$new_value = $count;
		Audit_trail::audit($old_value,serialize($new_value),$user->id,$action);

		$return["message"]        		= "DONE IMPORTATION";
		$return["status"]         		= $stat;
		$return["status_code"]    		= 404;
		$return["count_success"]     	= $count_success;
		$return["count_error"]     		= $count_error;
		
		return response()->json($return);
	
	}

	public static function excel_error($data,$type,$row)
    {
        $excel_error    = array();
        $error['type']  = $type;
        $error['row'] 	= $row + 1;
        $error['date']  = Carbon::now();
        $error['type']  = $type;
        array_push($excel_error,$error);
        if(count($excel_error)>0)
        {
            Session::put('excel_error',$excel_error);
        }
    }
}
