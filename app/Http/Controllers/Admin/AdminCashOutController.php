<?php
namespace App\Http\Controllers\Admin;

use App\Globals\CashOut;

use Illuminate\Support\Facades\Request;
use Hash;
use Excel;
use App\Globals\Slot;
use App\Globals\Log;
use App\Globals\Audit_trail;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
class AdminCashOutController extends AdminController
{
	public function get_settings()
	{
		$response = CashOut::get_settings();
		return response()->json($response);
	}

	public function update_settings()
	{
		$response = CashOut::update_settings(Request::all());
		return response()->json($response);
	}
	public function get_transactions()
    {
        $response = CashOut::get_transactions(Request::input(), Request::input('slot_id'),null,"member_cashout");

        return response()->json($response);
    }

	public function get_method_list() 
    {
        if(Request::input())
        {
            $response = CashOut::get_method_list(Request::input('category'), Request::input('currency'));
        }
        else
        {
            $response = CashOut::get_method_list(null, null, true);
        }

        return response()->json($response);
    }

    public function update_method() 
	{
	    $response = CashOut::update_method(Request::input());

	    return response()->json($response);
	}

	public function add_new_method() 
	{

	    $response = CashOut::add_new_method(Request::input());

	    return response()->json($response);
	}

	public function archive_method() 
	{
	    $response = CashOut::archive_method(Request::input('cash_out_method_id'), Request::input('archive'));

	    return response()->json($response);
	}

	public function check_schedule() 
	{
	    $response = CashOut::check_schedule(Request::input());

	    return response()->json($response);
	}

	public function check_schedule_details() 
	{
	    $response = CashOut::check_schedule_details(Request::input());

	    return response()->json($response);
	}

	public function process_payout()
	{
		$response = CashOut::process_payout(Request::input());

		return response()->json($response);
	}

	public function get_schedules()
	{
		$response = CashOut::get_schedules(Request::input());

		return response()->json($response);
	}

	public function update_transaction()
	{
		$response = CashOut::update_transaction(Request::input());

		return response()->json($response);
	}

	public function update_message()
	{
		$response = CashOut::update_message(Request::input());

		return response()->json($response);
	}

	// public function process_transaction()
	// {
	// 	$response = CashOut::process_transaction(Request::input('cash_out_id'), Request::input('sched_id'));

	// 	return response()->json($response);
	// }

	public function process_transactions()
	{
		$response = CashOut::process_transactions(Request::input('sched_id'),Request::input('type'));
		return response()->json($response);
	}

	public function check_negatives()
	{
		$response = CashOut::check_negatives(Request::input());
		return response()->json($response);
	}

	public function get_actual_schedule_transactions()
	{
		$response = CashOut::get_actual_schedule_transactions(Request::input());

		return response()->json($response);
	}

	public function get_method_list_raw() 
    {
		$response = CashOut::get_method_list_raw();

        return response()->json($response);
	}
	
	public function import_payout()
	{
		$rowCount 			= Request::input('row_count');
		if($rowCount == 'null')
		{
			$file         					= Request::file('file_data')->getRealPath();
			$check_rows = $_data        	= Excel::selectSheetsByIndex(0)->load($file, function($reader){})->all();
			$return['total'] = $check_rows->where('slot_no', '!=', null)->count();
			$return['current'] = 0;
			return response()->json($return);
		}
		else
		{
			$row_count 		= intval($rowCount);
			$file         	= Request::file('file_data')->getRealPath();
			$data        	= Excel::selectSheetsByIndex(0)->load($file, function($reader){})->all();
			if(isset($data[$row_count]))
			{
				$return['response'] = Cashout::import_cash_out($data[$row_count]);
				$row_count = $row_count + 1;
				$return['current'] = $row_count;
				return response()->json($return);
			}
			else
			{
				$user   	= Request::user()->id;
				$action 	= "Import Payout";
				$new_value 	= $value;
				Audit_trail::audit(null,serialize($new_value),$user,$action);

				$return["status"]         = "success"; 
				$return["status_code"]    = 200; 
				$return["status_message"] = "IMPORTED SUCCESSFULLY";
			}
		}
	}

	public function trigger_gc()
	{

		$rowCount = Request::input('count');
		$get_gc_settings = DB::table('tbl_gc_maintenance')->first();
		if($get_gc_settings->status == "enabled")
		{
			$default_currency = DB::table('tbl_currency')->where('currency_default', 1)->value('currency_id');
			$qualifying_slots = DB::table('tbl_wallet')
			->where('wallet_amount', '>=', $get_gc_settings->amount_required)
			->where('currency_id', $default_currency)->get();
			// ->where(function($q) 
			// {
			// 	$q->where('date_gc_triggered','!=',date_format(Carbon::now(),"Y/m/d"))->where('date_gc_triggered',null);
			// })->get();
			
			if($rowCount == null)
			{
				$return['total'] = count($qualifying_slots);
				$return['current'] = 0;
				$return["status"]         = "processing";
				return response()->json($return);
			}
			else
			{

				$row_count 		= intval($rowCount);
				if($row_count < count($qualifying_slots))
				{
					if(Carbon::today() != $qualifying_slots[$row_count]->date_gc_triggered || $qualifying_slots[$row_count]->date_gc_triggered == null)
					{
						Log::insert_wallet($qualifying_slots[$row_count]->slot_id, $get_gc_settings->amount_deducted * -1 , 'GC MAINTENANCE', $default_currency);
						Log::insert_wallet($qualifying_slots[$row_count]->slot_id, $get_gc_settings->amount_given, 'GC MAINTENANCE', 4);
						$update_wallet['date_gc_triggered'] = Carbon::today();
						DB::table('tbl_wallet')->where('slot_id', $qualifying_slots[$row_count]->slot_id)->update($update_wallet);
					}
					
				}
				else
				{
					$user   = Request::user()->id;
					$action = "Trigger GC";
					Audit_trail::audit(null,null,$user,$action);

					$return['current'] = $row_count;
					$return['total'] = count($qualifying_slots);
					$return["status"]         = "success"; 
					$return["status_code"]    = 200; 
					$return["status_message"] = "PROCESSED SUCCESSFULLY";

					return response()->json($return);
				}
				
				$return['current'] = $row_count;
				$return['total'] = count($qualifying_slots);

				$return["status"]         = "processing";
			}
			
		}
		else
		{
			$return["status"]         = "error"; 
			$return["status_code"]    = 400; 
			$return["status_message"] = "GC Maintenance is currently disabled";
		}
		return response()->json($return);
	}
}
