<?php

namespace App\Http\Controllers\Admin;


use App\Models\Users;
use App\Models\Tbl_cash_out_list;
use App\Models\Tbl_orders;
use App\Models\Tbl_slot;
use App\Models\Tbl_receipt;
use App\Models\Tbl_earning_log;
use App\Models\Tbl_mlm_plan;
use App\Models\Tbl_wallet_log;
use App\Models\Tbl_wallet;
use App\Models\Tbl_currency;
 
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Carbon\Carbon;
use App\Globals\Visitor;
use App\Globals\Seed;
use Analytics;
use Spatie\Analytics\Period;
class AdminDashboardController extends AdminController
{

	public function dashboard_figures() 
	{
		Seed::tab_module_seed();
		
		$response['member'] 								= Self::get_user_count();
		$response['slot'] 									= Self::get_slot_count();
	    $response['sales'] 									= Self::get_sales();
	    $response['payout'] 								= Self::get_cash_out();
	    $response['cashin_wallet']							= Self::get_available_cashin_wallet();
	    $response['pending_payout'] 						= Self::get_pending_cash_out();
	    $response['member_wallet'] 							= Self::get_available_wallet();
	    $response['total_direct_bonus'] 					= Self::get_total_direct_bonus();
	    $response['total_indirect_bonus'] 					= Self::get_total_indirect_bonus();
	    $response['active_slots'] 							= Self::get_total_active_slots();
	    $response['inactive_slots'] 						= Self::get_total_inactive_slots();

	    return response()->json($response);	
	}

	public function visit_chart_data()
	{
		$months = ['January','February',"March","April","May","June","July","August","September","October","November","December"];
		$return['visit']['chartType'] = 'ColumnChart';
		$calcview = 0;
		$return['visit']['dataTable'][0] = ['Visitors' , 'Visitors per Month'];
		foreach($months as $key => $value)
		{
			$month = Analytics::fetchTotalVisitorsAndPageViews(Period::create(new Carbon('first day of '.$value),new Carbon('last day of '.$value)));
			foreach($month as $key2 => $value2)
			{
				$calcview = $calcview + $value2['visitors'];
			}
			$return['visit']['dataTable'][$key + 1] = [$value , $calcview];
			$calcview = 0;
		}
		$return['visit']['options'] = ['title' => 'Visitors', 'height' => '500'];
		return response()->json($return);
	}


	public function member_chart_data()
	{
		$months = ['January','February',"March","April","May","June","July","August","September","October","November","December"];
		$return['member']['chartType'] = 'ColumnChart';
		$calcview = 0;
		$return['member']['dataTable'][] = ['Month' , 'New Members per Month'];
		$year = Carbon::now()->year;
		
		foreach($months as $key => $value)
		{
			$data = Users::whereYear('created_at', $year)
			->whereMonth('created_at', $key + 1)
			->where('type','member')
			->count();
			$return['member']['dataTable'][] = [$value , $data];
		}
		$return['member']['options'] = ['title' => 'New Members', 'height' => '500'];
		return response()->json($return);
	}

	public function sales_chart_data()
	{
		$months = ['January','February',"March","April","May","June","July","August","September","October","November","December"];
		$return['sales']['chartType'] = 'ColumnChart';
		$calcview = 0;
		$return['sales']['dataTable'][] = ['Month' , 'Sales per Month'];
		$year = Carbon::now()->year;

		foreach($months as $key => $value)
		{
			$data = Tbl_receipt::whereYear('receipt_date_created',$year)
			->whereMonth('receipt_date_created', $key + 1)
			->sum('grand_total');
			$return['sales']['dataTable'][] = [$value , $data];
		}
		$return['sales']['options'] = ['title' => 'Sales', 'height' => '500'];
		return response()->json($return);
	}
	public function visitor_data()
	{
		$week = Analytics::fetchTotalVisitorsAndPageViews(Period::days(7));
		$all = Analytics::fetchTotalVisitorsAndPageViews(Period::create(Carbon::create()->subYear(1), Carbon::now()));
		$holder = 0;
		foreach($week as $key => $value)
		{
			$return['week'] = $holder + $value['visitors'];
		}
		foreach($all as $key2 => $value2)
		{
			$return['all'] = $holder + $value2['visitors'];
		}
		return response()->json($return);
	}

	public static function get_slot_count()
	{
		$return['all'] 	= Tbl_slot::where('slot_no','!=','root')->where('membership_inactive',0)->where('slot_type','PS')->count();
		$return['week'] = Tbl_slot::where('slot_no','!=','root')->where('membership_inactive',0)->where('slot_type','PS')->GetWeekRegistered()->count();
		return $return;
	}

	public static function get_user_count()
	{
		$return['all'] 	= Users::where('type','member')->count();
		$return['week'] = Users::GetWeekRegistered()->count();
		return $return;
	}

	public static function get_cash_out()
	{
		$return['all']  = Tbl_cash_out_list::where('cash_out_status','processed')->sum('cash_out_amount_requested');
		$return['week'] = Tbl_cash_out_list::GetWeekCashOut()->where('cash_out_status','processed')->sum('cash_out_amount_requested');
		
		$return['all'] = Self::thousandsCurrencyFormat($return['all']);
		$return['week'] = Self::thousandsCurrencyFormat($return['week']);
		return $return;
	}

	public static function get_sales()
	{
		$return['all'] = Tbl_orders::sum('grand_total');
		$return['week'] = Tbl_orders::GetWeekOrder()->sum('grand_total');

		$return['all'] = Self::thousandsCurrencyFormat($return['all']);
		$return['week'] = Self::thousandsCurrencyFormat($return['week']);
		return $return;
	}

	public static function get_visitor()
	{
		$get = Visitor::get_all_visitors();
		$return['all'] 	= Self::thousandsCurrencyFormat($get['all']);
		$return['week'] = Self::thousandsCurrencyFormat($get['week']);

		return $return;
	}

	public static function thousandsCurrencyFormat($num) 
	{

	  	if($num>1000) 
	  	{

	        $x = round($num);
	        $x_number_format = number_format($x);
	        $x_array = explode(',', $x_number_format);
	        $x_parts = array('k', 'm', 'b', 't');
	        $x_count_parts = count($x_array) - 1;
	        $x_display = $x;
	        $x_display = $x_array[0] . ((int) $x_array[1][0] !== 0 ? '.' . $x_array[1][0] : '');
	        $x_display .= $x_parts[$x_count_parts - 1];

	        return $x_display;

	  	}
	  	return $num;
	}
	public static function get_top_list()
	{
		$plan_name = Request::input('plan_name');
		$data      = Request::input('date');
		if($data == null)
		{
			$today	= Carbon::now()->format('Y-m-d');
		}
		else 
		{
			$today = $data;
		}
		//$today = '2018-11-16';
		$data	=  Tbl_earning_log::groupBy('earning_log_slot_id')->where('earning_log_plan_type',$plan_name)->wheredate('earning_log_date_created',$today)->get(['earning_log_slot_id']);
		if(count($data) === 0)
		{
			$data = NULL;
		}
		else 
		{
			foreach ($data as $key => $new_data) 
			{
				$data[$key]->slot_code    = Tbl_slot::where('slot_id',$data[$key]->earning_log_slot_id)
														->Owner()
														->JoinMembership()
														->select('slot_id','slot_no','name','membership_name')
														->first();
				//$data[$key]->total_pairs  = Tbl_earning_log::where('earning_log_slot_id',$data[$key]->earning_log_slot_id)->count('earning_log_slot_id');
				$data[$key]->total_pairs  = Tbl_earning_log::where('earning_log_plan_type',$plan_name)->wheredate('earning_log_date_created',$today)->where('earning_log_slot_id',$data[$key]->earning_log_slot_id)->count('earning_log_slot_id');
			}
		
			$data = collect($data)->sortBy('total_pairs')->reverse()->toArray();
			$data = array_slice($data, 0, 20);
		}
		// $data['date_today'] = $today;
		return $data;
	}
	public static function loadplan_list()
	{
		$plan_name = Tbl_mlm_plan::get(['mlm_plan_code']);
		foreach ($plan_name as $key => $concat_plan_name) 
		{
			$new_plan_name = trim(preg_replace('/_/', ' ', $concat_plan_name->mlm_plan_code));
			$data[$key]['plan']    = $new_plan_name;
		}
		// dd($data);
		return $data;
	}
	public static function get_pending_cash_out()
	{
		$return['all']  = Tbl_cash_out_list::where('cash_out_status',"!=",'processed')->where('cash_out_status','!=','REJECTED')->sum('cash_out_amount_requested');
		$return['week'] = Tbl_cash_out_list::GetWeekCashOut()->where('cash_out_status',"!=",'processed')->where('cash_out_status','!=','REJECTED')->sum('cash_out_amount_requested');
		
		$return['all'] = Self::thousandsCurrencyFormat($return['all']);
		$return['week'] = Self::thousandsCurrencyFormat($return['week']);
		return $return;
	}
	public static function get_available_wallet()
	{
		$currency_id			= 1;
		//  Tbl_currency::where('currency_buying',1)->where('currency_default',1)->where('archive',0)->pluck('currency_id')->first();
		$return['all']			= Tbl_wallet_log::where('wallet_log_slot_id','!=',1)->where('currency_id',$currency_id)->sum('wallet_log_amount');				
		$return['week']			= Tbl_wallet_log::where('wallet_log_slot_id','!=',1)->GetWeekWalletIncome()->where('currency_id',$currency_id)->sum('wallet_log_amount');	
		
		$return['all'] = Self::thousandsCurrencyFormat($return['all']);
		$return['week'] = Self::thousandsCurrencyFormat($return['week']);
		return $return;
	}
	public static function get_total_direct_bonus()
	{
		$currency_id			= Tbl_currency::where('currency_buying',1)->where('currency_default',1)->where('archive',0)->pluck('currency_id')->first();
		$return['all']			= Tbl_wallet_log::where('currency_id',$currency_id)->where('wallet_log_details',"DIRECT")->sum('wallet_log_amount');				
		$return['week']			= Tbl_wallet_log::where('currency_id',$currency_id)->GetWeekWalletIncome()->where('wallet_log_details',"DIRECT")->sum('wallet_log_amount');				
		
		$return['all'] = Self::thousandsCurrencyFormat($return['all']);
		$return['week'] = Self::thousandsCurrencyFormat($return['week']);
		return $return;
	}
	public static function get_total_indirect_bonus()
	{
		$currency_id			= Tbl_currency::where('currency_buying',1)->where('currency_default',1)->where('archive',0)->pluck('currency_id')->first();
		$return['all']			= Tbl_wallet_log::where('currency_id',$currency_id)->where('wallet_log_details',"INDIRECT")->sum('wallet_log_amount');				
		$return['week']			= Tbl_wallet_log::where('currency_id',$currency_id)->GetWeekWalletIncome()->where('wallet_log_details',"INDIRECT")->sum('wallet_log_amount');				
		
		$return['all'] = Self::thousandsCurrencyFormat($return['all']);
		$return['week'] = Self::thousandsCurrencyFormat($return['week']);
		return $return;
	}
	public static function get_total_active_slots()
	{
		$return['all'] 	= Tbl_slot::where('slot_no','!=','root')->where('membership_inactive',0)->count();
		$return['week'] = Tbl_slot::GetWeekRegistered()->where('slot_no','!=','root')->where('membership_inactive',0)->count();
		return $return;
	}
	public static function get_total_inactive_slots()
	{
		$return['all'] 	= Tbl_slot::where('slot_no','!=','root')->where('membership_inactive',1)->count();
		$return['week'] = Tbl_slot::GetWeekRegistered()->where('slot_no','!=','root')->where('membership_inactive',1)->count();
		return $return;
	}
	public static function get_available_cashin_wallet()
	{
		$currency_id			= 15;
		//  Tbl_currency::where('currency_buying',1)->where('currency_default',1)->where('archive',0)->pluck('currency_id')->first();
		$return['all']			= Tbl_wallet_log::where('currency_id',$currency_id)->where('wallet_log_details', '!=', 'Shop/Purchased (COD)')->sum('wallet_log_amount');				
		$return['week']			= Tbl_wallet_log::GetWeekWalletIncome()->where('currency_id',$currency_id)->where('wallet_log_details', '!=', 'Shop/Purchased (COD)')->sum('wallet_log_amount');	
		
		$return['all'] = Self::thousandsCurrencyFormat($return['all']);
		$return['week'] = Self::thousandsCurrencyFormat($return['week']);
		return $return;
	}
	public function load_topearner()
    {
		$first_date             = Request::input('date_from') ?? date('Y-m-d',strtotime('first day of this month'));
		$today                  = Request::input('date_to') ?? date('Y-m-d',strtotime('today'));
        $currency_id            = Tbl_currency::where('currency_default',1)->first()->currency_id;
        
        $response               = Tbl_earning_log::where('tbl_earning_log.earning_log_currency_id',$currency_id)
                                ->whereDate('tbl_earning_log.earning_log_date_created','>=',$first_date)->whereDate('tbl_earning_log.earning_log_date_created','<=',$today)
		                        ->leftjoin('tbl_slot','tbl_slot.slot_id','=','tbl_earning_log.earning_log_slot_id')->leftjoin('tbl_membership', 'tbl_membership.membership_id', '=', 'tbl_slot.slot_membership')->where('tbl_membership.hierarchy', '!=', '19')->leftjoin('users','users.id','=','tbl_slot.slot_owner')->where('users.type','member')->where('users.top_earner_status',1)
                                ->select(DB::raw('sum(earning_log_amount) as sum_earn'),'tbl_slot.slot_owner')
								->groupby('tbl_slot.slot_owner')
								->get();

		foreach ($response as $key => $value) {
			$response[$key]['accumulated'] 			= Tbl_slot::where('slot_owner',$value->slot_owner)->leftjoin('tbl_earning_log','tbl_earning_log.earning_log_slot_id','tbl_slot.slot_id')->where('earning_log_currency_id',$currency_id)->sum('earning_log_amount');
			$response[$key]['name'] 	   			= Users::where('id',$value->slot_owner)->first()->name;
			$response[$key]['profile_picture'] 	   	= Users::where('id',$value->slot_owner)->first()->profile_picture;
		}
	
		$response               = collect($response)->sortBy('sum_earn')->reverse()->toArray();
		$response               = array_slice($response, 0, 30);

		if(empty($response))
		{
			$response           = null;
		}
		else
		{
			$response           = $response;
		}
		return $response;
    }
	public function load_topearner_accummulated()
    {
        $first_date             = date('Y-m-d',strtotime('first day of this month'));
        $today                  = date('Y-m-d',strtotime('today'));
        $currency_id            = Tbl_currency::where('currency_default',1)->first()->currency_id;
        
        $response               = Tbl_earning_log::where('tbl_earning_log.earning_log_currency_id',$currency_id)
		                        ->leftjoin('tbl_slot','tbl_slot.slot_id','=','tbl_earning_log.earning_log_slot_id')->leftjoin('tbl_membership', 'tbl_membership.membership_id', '=', 'tbl_slot.slot_membership')->where('tbl_membership.hierarchy', '!=', '19')->leftjoin('users','users.id','=','tbl_slot.slot_owner')->where('users.type','member')->where('users.top_earner_status',1)
                                ->select(DB::raw('sum(earning_log_amount) as sum_earn'),'tbl_slot.slot_owner')
								->groupby('tbl_slot.slot_owner')
								->get();

		foreach ($response as $key => $value) {
			$response[$key]['name'] 	   			= Users::where('id',$value->slot_owner)->first()->name;
			$response[$key]['profile_picture'] 	   	= Users::where('id',$value->slot_owner)->first()->profile_picture;
		}
	
		$response               = collect($response)->sortBy('sum_earn')->reverse()->toArray();
		$response               = array_slice($response, 0, 30);

		if(empty($response))
		{
			$response           = null;
		}
		else
		{
			$response           = $response;
		}
		return $response;
    }
	public function load_topdirect()
    {
		$first_date             = Request::input('date_from') ?? date('Y-m-d',strtotime('first day of this month'));
		$today                  = Request::input('date_to') ?? date('Y-m-d',strtotime('today'));
        $currency_id            = Tbl_currency::where('currency_default',1)->first()->currency_id;
        
        $response               = Tbl_earning_log::where('tbl_earning_log.earning_log_currency_id',$currency_id)
                                ->whereDate('tbl_earning_log.earning_log_date_created','>=',$first_date)->whereDate('tbl_earning_log.earning_log_date_created','<=',$today)
								->where('earning_log_plan_type','DIRECT')
		                        ->leftjoin('tbl_slot','tbl_slot.slot_id','=','tbl_earning_log.earning_log_slot_id')->leftjoin('tbl_membership', 'tbl_membership.membership_id', '=', 'tbl_slot.slot_membership')->where('tbl_membership.hierarchy', '!=', '19')->leftjoin('users','users.id','=','tbl_slot.slot_owner')->where('users.type','member')->where('users.top_earner_status',1)
                                ->select(DB::raw('count(earning_log_plan_type) as total_directs'),'tbl_slot.slot_owner')
								->groupby('tbl_slot.slot_owner')
								->get();

		foreach ($response as $key => $value) {
			$response[$key]['accumulated'] 			= Tbl_slot::where('slot_owner',$value->slot_owner)->leftjoin('tbl_earning_log','tbl_earning_log.earning_log_slot_id','tbl_slot.slot_id')->where('earning_log_currency_id',$currency_id)->sum('earning_log_amount');
			$response[$key]['name'] 	   			= Users::where('id',$value->slot_owner)->first()->name;
			$response[$key]['profile_picture'] 	   	= Users::where('id',$value->slot_owner)->first()->profile_picture;
		}
	
		$response               = collect($response)->sortBy('total_directs')->reverse()->toArray();
		$response               = array_slice($response, 0, 30);

		if(empty($response))
		{
			$response           = null;
		}
		return $response;
    }
}
