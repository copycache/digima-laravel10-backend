<?php
namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Request;
use PDF;
use Maatwebsite\Excel\Facades\Excel;
use ZipArchive;
use App\Globals\Slot;
use App\Globals\CashIn;
use App\Globals\CashOut;
use App\Globals\Item;
use Carbon\Carbon;
use App\Models\Tbl_cash_out_schedule;
use App\Models\Tbl_branch;
use App\Models\Tbl_receipt;
use App\Models\Users;
use App\Models\Tbl_inventory;
use App\Models\Tbl_cash_out_list;
use App\Models\Tbl_cash_out_method;
use App\Models\Tbl_item;
use App\Models\Tbl_address;
use App\Models\Tbl_slot;
use App\Models\Tbl_earning_log;
use App\Models\Tbl_currency;
use App\Models\Tbl_wallet_log;
use App\Models\Tbl_adjust_wallet_log;
use App\Http\Controllers\Cashier\CashierItemController;
use App\Models\Tbl_membership;
use App\Models\Tbl_unilevel_points;
use App\Models\Tbl_code_transfer_logs;
use App\Models\Tbl_dynamic_compression_record;
use App\Models\Tbl_mlm_unilevel_settings;
use App\Models\Tbl_unilevel_distribute;

// Excel v3 Export Classes
use App\Exports\BaseExport;
use App\Exports\ViewExport;
use App\Exports\QuoteRequestExport;
use App\Exports\CashInExport;
use App\Exports\ItemCodeExport;
use App\Exports\CashoutPayoutExport;
use App\Exports\TopPairsExport;
use App\Exports\TopRecruiterExport;
use App\Exports\CashflowReportExport;
use App\Exports\BonusSummaryExport;
use App\Exports\AdminSalesReportExport;
use App\Exports\OrderListExport;
use App\Exports\SlotNetworkListExport;
use App\Exports\SlotPayoutHistoryExport;
use App\Exports\SlotWalletHistoryExport;
use App\Exports\InventoryExport;
use App\Exports\DragonpayOrdersExport;
use App\Exports\PayoutExport;
use App\Exports\TopSellerReportExport;
use App\Exports\DynamicExport;
use App\Exports\QueryExport;
use App\Exports\PayoutScheduleExport;
use App\Exports\PromoReportExport;
use App\Exports\DragonpayPayoutExport;

use App\Globals\Code;
use Illuminate\Support\Facades\DB;



class AdminExportController extends AdminController
{

	public function export_qoute_request_csv()
	{
		$response = DB::table('tbl_qoute_request')->where('qoute_request_status',0)
					->join('tbl_item','tbl_item.item_id','=','tbl_qoute_request.qoute_request_item_id')
					->get();

		$headings = ['Item Name','Name','Email','Phone','Message'];
		
		$export = new DynamicExport(
			$response,
			$headings,
			function($list) {
				return [
					$list->item_sku,
					$list->qoute_request_name,
					$list->qoute_request_email,
					$list->qoute_request_phone,
					$list->qoute_request_message,
				];
			},
			'Quote Request'
		);
		
		return Excel::download($export, 'QOUTE_REQUEST.xlsx');
	}
	public function slot_wallet_history_pdf_ctr()
	{
		// dd(Request::input());
		$prime['id'] 		= Request::input('id');
		$prime['from'] 	= Request::input('from');
		$prime['to'] 		= Request::input('to');
		$key            = 0;

		// -------------------------------------------------------------------------
		$query = Tbl_wallet_log::where("tbl_wallet_log.wallet_log_slot_id", $prime["id"]);
		$query = $query->leftJoin("tbl_slot", "tbl_slot.slot_id", "=", "tbl_wallet_log.wallet_log_slot_id");

		if ($prime["from"] && $prime["from"] != "null" && $prime["to"] && $prime["to"] != "null")
		{
			$query = $query->whereBetween("tbl_wallet_log.wallet_log_date_created", [$prime["from"], $prime["to"]]);
		}
		// -------------------------------------------------------------------------
		$query->chunk(500,function($wallet) use(&$key) {
				$key++;
		});
		return $key;
	}
	public function slot_wallet_history_pdf()
	{
		// dd(Request::input());
		$prime['id'] 		= Request::input('id');
		$prime['key'] 	= Request::input('key');
		$prime['from'] 	= Request::input('from');
		$prime['to'] 		= Request::input('to');
		$key            = 0;
		$pdf            = null;
		// $data["_wallet"] = Slot::get_slot_wallet($prime, 500);
		$data["total_wallet"] = Slot::get_slot_total_wallet($prime['id']);

		// -------------------------------------------------------------------------
		$query = Tbl_wallet_log::where("tbl_wallet_log.wallet_log_slot_id", $prime["id"]);
		$query = $query->leftJoin("tbl_slot", "tbl_slot.slot_id", "=", "tbl_wallet_log.wallet_log_slot_id");

		if ($prime["from"] && $prime["from"] != "null" && $prime["to"] && $prime["to"] != "null")
		{
			$query = $query->whereBetween("tbl_wallet_log.wallet_log_date_created", [$prime["from"], $prime["to"]]);
		}
		// -------------------------------------------------------------------------
		$query->chunk(500,function($wallet) use($data,&$key,$prime,&$pdf)
		{
				if($key == $prime['key'])
				{
					$data2["_wallet"]			  = $wallet;
					$data2["total_wallet"] 	= $data["total_wallet"];
					$pdf 										= PDF::loadView('export.pdf.slot_wallet_history_pdf', $data2);
				}
				$key++;
		});
		$part = $prime['key'] + 1;
		return $pdf->stream('WalletHistory_part_'.$part.'.pdf');
	}
	public function slot_wallet_history_csv()
	{
		// dd(Request::input());
		$prime['id'] = Request::input('id');
		$prime['from'] = Request::input('from');
		$prime['to'] = Request::input('to');

		$data["_wallet"] = Slot::get_slot_wallet($prime);
		$data["total_wallet"] = Slot::get_slot_total_wallet($prime['id']);

		$export = new ViewExport('export.csv.slot_wallet_history_csv', $data, 'Wallet History');
		
		return Excel::download($export, 'slot_wallet_history.csv');
	}
	public function slot_payout_history_pdf_ctr()
	{
		// dd(Request::input());
		$prime['id'] 		= Request::input('id');
		$prime['from'] 	= Request::input('from');
		$prime['to'] 		= Request::input('to');
		$key            = 0;

		// -------------------------------------------------------------------------
		$query = Tbl_wallet_log::where("tbl_wallet_log.wallet_log_slot_id", $prime["id"]);
		$query = $query->leftJoin("tbl_slot", "tbl_slot.slot_id", "=", "tbl_wallet_log.wallet_log_slot_id");

		if ($prime["from"] && $prime["from"] != "null" && $prime["to"] && $prime["to"] != "null")
		{
			$query = $query->whereBetween("tbl_wallet_log.wallet_log_date_created", [$prime["from"], $prime["to"]]);
		}
		// -------------------------------------------------------------------------
		$query->chunk(500,function($wallet) use(&$key) {
				$key++;
		});
		return $key;
	}
	public function slot_payout_history_pdf()
	{
		$prime['id'] 		= Request::input('id');
		$prime['key'] 	= Request::input('key');
		$prime['from'] 	= Request::input('from');
		$prime['to'] 		= Request::input('to');
		$key            = 0;
		$pdf            = null;
		// $data["_wallet"] = Slot::get_slot_wallet($prime, 500);
		$data["total_payout"] = Slot::get_slot_total_payout($prime['id']);

		// -------------------------------------------------------------------------
		$query = Tbl_wallet_log::where("tbl_wallet_log.wallet_log_slot_id", $prime["id"]);
		$query = $query->leftJoin("tbl_slot", "tbl_slot.slot_id", "=", "tbl_wallet_log.wallet_log_slot_id");

		if ($prime["from"] && $prime["from"] != "null" && $prime["to"] && $prime["to"] != "null")
		{
			$query = $query->whereBetween("tbl_wallet_log.wallet_log_date_created", [$prime["from"], $prime["to"]]);
		}
		// -------------------------------------------------------------------------
		$query->chunk(500,function($wallet) use($data,&$key,$prime,&$pdf)
		{
				if($key == $prime['key'])
				{
					$data2["_payout"]			  = $wallet;
					$data2["total_payout"] 	= $data["total_payout"];
					$pdf 										= PDF::loadView('export.pdf.slot_payout_history_pdf', $data2);
				}
				$key++;
		});
		$part = $prime['key'] + 1;
		return $pdf->stream('Slot_payout_history'.$part.'.pdf');
	}

	public function slot_payout_history_csv()
	{
		$prime['id'] = Request::input('id');
		$prime['from'] = Request::input('from');
		$prime['to'] = Request::input('to');

		$data["_payout"] = Slot::get_slot_payout($prime);
		$data["total_payout"] = Slot::get_slot_total_payout($prime['id']);
		
		$filename = "slot_payout_history_" . (isset($data["_payout"][0]->slot_no) ? $data["_payout"][0]->slot_no : "export") . ".csv";
		$export = new ViewExport('export.csv.slot_payout_history_csv', $data, 'Payout History');
		
		return Excel::download($export, $filename);
	}

	public function slot_payout_history()
	{
		$data["_pairs"] = Slot::get_slot_payout(Request::input());
		$data["total_payout"] = 0;

		foreach ($data["_payout"] as $key => $value)
		{
			$data["total_payout"] += $value->wallet_log_amount;
		}

		return $data;
	}

	public function export_cashin($ref)
	{
		$data 	= CashIn::get_transactions(Request::input());
		if($ref=="pdf")
		{
			$pdf['_list']  = $data;
			$pdf = PDF::loadView('export.pdf.exportCashinList', $pdf);
			return $pdf->stream('cashin.pdf');
		}
		else
		{
			$headings = ['Slot Code','Member Name','Method','Currency','Amount Required','Cash In Charge','Cash In Status','Member will receive'];

			$export = new DynamicExport(
				$data,
				$headings,
				function($list) {
					return [
						$list['cash_in_slot_code'],
						$list['cash_in_member_name'],
						$list['cash_in_method_name'],
						$list['cash_in_currency'],
						$list['cash_in_payable'],
						$list['cash_in_charge'],
						$list['cash_in_status'],
						$list['cash_in_receivable']
					];
				},
				'CASH IN'
			);

			return Excel::download($export, 'CASH_IN.xlsx');
		}
	}

	public function export_item_code_csv()
	{
		$response = Code::get(Request("branch_id"), Request(), Request("item_id"),0);
		$item     = Tbl_item::where('item_id',request('item_id'))->first();
		
		$headings = ['Code','Pin','Sold to','Transfer to','Used by'];
		$filename = 'ITEM CODE of '.strtoupper($item->item_sku);
		
		$export = new DynamicExport(
			$response,
			$headings,
			function($list) {
				return [
					$list['code_activation'],
					$list['code_pin'],
					$list['code_org_buyer'] != null ? $list['code_org_buyer'] : "Unused",
					$list['code_buyer'] == null ? "Unused" : ($list['code_buyer']['name'] == $list['code_org_buyer'] ? '--' : $list['code_buyer']['name']),
					$list['code_user'] != null ? $list['code_user']['name'] : "Unused"
				];
			},
			$filename
		);

		return Excel::download($export, $filename.'.xlsx');
	}

	public function export_payout_schedule_csv()
	{
		$schedule = Tbl_cash_out_schedule::where('schedule_id',Request::get('schedule_id'))->first();
		if($schedule->schedule_method_id != 0)
		{
			$data["methods"] = Tbl_cash_out_method::where('cash_out_method_id',$schedule->schedule_method_id)->get();
		}
		else
		{
			$data["methods"] = Tbl_cash_out_method::get();
		}
		foreach ($data["methods"] as $key => $value)
		{
			$data["methods"][$key]["transactions"] = Tbl_cash_out_list::where('schedule_id',$schedule->schedule_id)->where("tbl_cash_out_list.cash_out_method_id", $value->cash_out_method_id)->get();
		}
		
        $headingsBank = ['Slot Code','Account Name','Account Number','Account Type','Email','Phone Number','TIN','Tax','Method Fee','Service Charge', 'Survey Charge', 'Product Charge','GC Charge','Savings','Amount Due','Net Payout','Date'];
		$headingsRemit = ['Slot Code','Full Name','Full Address','Other Info','Email','Phone Number','TIN','Tax','Method Fee','Service Charge','Savings','Amount Due','Net Payout','Date'];

        $export = new PayoutScheduleExport($data["methods"], $headingsBank, $headingsRemit);
        return Excel::download($export, 'Cashout.xlsx');
	}

	public function top_pairs_csv()
	{
		$data            = $this->top_pairs();
		if($data)
		{
            $excels['_list'] = $data['_list'] ?? null;
			$date_today      = $data['date'];
            $filename        = 'Top Slot as of '.$date_today;
            
			if($excels['_list'])
			{
				$headings = ['Rank no','Slot ID','Slot no','Slot Owner','Membership','Total Pairs','Date'];
                
                $ctr = 0;
                $mapper = function($list) use (&$ctr) {
                    $ctr++;
                    return [
                        $ctr,
                        $list['slot_code']['slot_id'],
                        $list['slot_code']['slot_no'],
                        $list['slot_code']['name'],
                        $list['slot_code']['membership_name'],
                        $list['total_pairs'],
                        Carbon::now()->format('Y-m-d')
                    ];
                };

                $export = new DynamicExport($excels['_list'], $headings, $mapper, 'template');
                return Excel::download($export, $filename.'.xlsx');
			}
			else {
                $headings = ['Rank no','Slot ID','Slot no','Slot Owner','Membership','Total Pairs','Date'];
                $export = new DynamicExport([], $headings, function($row){ return []; }, 'template');
                return Excel::download($export, 'Top Pairs Today.xlsx');
			}
		}
		else
		{
			dd("No data");
		}
	}

	public function top_pairs()
	{
		$date = Request::input('date');

		$plan_name = Request::input('plan_name');
		if($date == null)
		{
			$today	= Carbon::now()->format('Y-m-d');
		}
		else
		{
			$today = $date ;
		}

		//dd($today);
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
			$data['_list'] = array_slice($data, 0, 20);
			$data['date'] = $today;
		}

		return $data;
	}

	public function top_recruiter_csv()
	{
		$search      	= Request::input('search');
		$type		= Request::input('type');
		$month		= Request::input('month');
		if($search == 'undefined')
		{
			$search = null;
		}
		if($type == 'month')
		{
			if($month == '' || $month == null || $month == 'undefined')
			{
				$date_from = Carbon::now()->startofMonth();
				$date_to   = Carbon::now()->endofMonth();
			}
			else 
			{
				$date_from = Carbon::parse($month)->startofMonth();
				$date_to   = Carbon::parse($month)->endofMonth();
			}
		}
		else 
		{
			if($month == '' || $month == null || $month == 'undefined')
			{
				$date_from = Carbon::now()->startofYear();
				$date_to   = Carbon::now()->endofYear();
			}
			else 
			{
				$date_from = Carbon::parse($month)->startofYear();
				$date_to   = Carbon::parse($month)->endofYear();
			}
		}
		ini_set('memory_limit', '1000M');
		set_time_limit ( 7200 );
        
        $query = Tbl_slot::JoinTopRecruiter()
                    ->Owner()
                    ->select('tbl_top_recruiter.slot_id',DB::raw('sum(total_recruits) as total_recruits'),DB::raw('sum(total_leads) as total_leads'));

        if($search != '' || $search != null)
        {
            $query->where("tbl_slot.slot_no", "like", "%". $search . "%")->orWhere("users.name", "like", "%". $search . "%");
        }
        if($date_from != '' || $date_from != null)
        {
            $query->whereDate("tbl_top_recruiter.date_from",">=",$date_from);
        }
        if($date_to != '' || $date_to != null)
        {
            $query->whereDate("tbl_top_recruiter.date_to", "<=",$date_to);
        }

        $query = $query->groupBy('tbl_top_recruiter.slot_id','total_recruits');
        $query = $query->orderBy('total_recruits','DESC');

        $headings = ['Slot Code','Member Name','Member Contact #','Member Email','Total Recruits','Total Leads'];

        $mapper = function($list) {
            $details = Tbl_slot::where('slot_id',$list->slot_id)->Owner()->select('slot_no','name','contact','email')->first();
            return [
                $details ? $details->slot_no : '',
                $details ? $details->name : '',
                $details ? $details->contact : '',
                $details ? $details->email : '',
                $list->total_recruits,
                $list->total_leads
            ];
        };

        $export = new QueryExport($query, $headings, $mapper, 'Top Recruiter');
        return Excel::download($export, 'Top Recruiters of '.$date_from.' to '.$date_to.'.xlsx');
	}

	public function cashflow_report_csv()
	{
		ini_set('memory_limit', '1000M');
		set_time_limit ( 7200 );

		$search = null;
		$date_today =  Carbon::Now()->format('Y/d/m');
        
        $currency_id = Tbl_currency::where('currency_default',1)->first()->currency_id;
        $query 		 = Tbl_slot::where("tbl_slot.archive",0)
                                ->Owner()
                                ->Wallet($currency_id)
                                ->select('tbl_slot.slot_id','slot_no','name','contact','email','wallet_amount')
                                ->orderBy('wallet_amount','Desc');

        if($search != '' || $search != null)
        {
            $query->where("tbl_slot.slot_no", "like", "%". $search . "%")->orWhere("users.name", "like", "%". $search . "%");
        }

        $headings = ['Slot Code','Member Name','Member Contact #','Member Email','Total Income Recieves','Total Amount Paid Out','Current Remaining Balance'];
        
        $mapper = function($list) {
            return [
                $list->slot_no,
                $list->name,
                $list->contact,
                $list->email,
                $list->total_income_receive,
                $list->amount_paid_out,
                $list->wallet_amount
            ];
        };

        $export = new QueryExport($query, $headings, $mapper, 'Top Recruiter');
        return Excel::download($export, 'Cashflow as of '.$date_today.'.xlsx');
	}

	public function bonus_summary_csv()
	{
		ini_set('memory_limit', '1000M');
		set_time_limit ( 7200 );

		$search = null;
		$date_today =  Carbon::Now()->format('Y/d/m');
        
        $query = Tbl_slot::where("tbl_slot.archive",0)
                          ->Owner()
                          ->select('tbl_slot.slot_id','slot_no','name','contact','email');
        if($search != '' || $search != null)
        {
            $query->where("tbl_slot.slot_no", "like", "%". $search . "%")->orWhere("users.name", "like", "%". $search . "%");
        }
        
        $headings = ['Slot Code','Member Name','Member Contact #','Member Email','Direct Referral Income','Indirect Referral Income','Binary Income','Stairstep Bonus','Unilevel Bonus'];
        
        $mapper = function($list) {
            return [
                $list->slot_no,
                $list->name,
                $list->contact,
                $list->email,
                $list->direct_income,
                $list->indirect_income,
                $list->binary_income,
                $list->stairstep_income,
                $list->unilevel_bonus_income
            ];
        };

        $export = new QueryExport($query, $headings, $mapper, 'Top Recruiter');
        return Excel::download($export, 'Bonus summary as of '.$date_today.'.xlsx');
	}

	public function export_sales_report($ref)
	{
		$data 	= AdminReportController::load_sales_report(1);
		if($ref=="pdf")
		{
			$pdf['_list']  = $data;
			$pdf = PDF::loadView('export.pdf.adminSalesReport', $pdf);
			return $pdf->stream('salesreport.pdf');
		}
		else
		{
            $xls['_list'] = $data;
            $export = new ViewExport('export.excel.adminSalesReportxls', $xls, 'Sales Report');
            return Excel::download($export, 'AdminSalesReport.xlsx');
		}
	}

	public function eloading_report_xlxs()
	{
		$search      	= Request::input('search');
		$date_from      = Request::input('date_from');
		$date_to        = Request::input('date_to');
		if($search === "undefined")
		{
			$search = null;
		}
		ini_set('memory_limit', '1000M');
		set_time_limit ( 7200 );
		$date_today =  Carbon::Now()->format('Y/d/m');
        
        $currency_id = Tbl_currency::where('currency_name','Load Wallet')->value('currency_id');

        $query                  = Tbl_wallet_log::where('currency_id',$currency_id)
                                                    ->leftJoin('tbl_slot','tbl_slot.slot_id','=','tbl_wallet_log.wallet_log_slot_id')
                                                    ->leftJoin('users','users.id','=','tbl_slot.slot_owner')
                                                    ->select('name','email','slot_no','wallet_log_amount','wallet_log_date_created')
                                                    ->orderBy('wallet_log_date_created','ASC');

        if($search != '' || $search != null )
        {
            $query = $query->where("users.name", "like", "%". $search . "%")->orwhere("users.email", "like", "%". $search . "%");
        }
        if($date_from != '' || $date_from != null )
        {
            $query->whereDate('wallet_log_date_created', '>=', $date_from);
        }
        if($date_to != '' || $date_to != null )
        {
            $query->whereDate('wallet_log_date_created', '<=', $date_to);
        }

        $headings = ['Member Name','Slot Code','Member Email','Amount','Date'];
        
        $mapper = function($list) {
            return [
                $list->name,
                $list->slot_no,
                $list->email,
                $list->wallet_log_amount,
                $list->wallet_log_date_created
            ];
        };

        $export = new QueryExport($query, $headings, $mapper, 'Eloading');
        return Excel::download($export, 'Eloading Logs as of '.$date_today.'.xlsx');
	}


					// garbage code removed

	public function adjustwallet_report_xlxs()
	{
		$search      	= Request::input('search');
		$date_from      = Request::input('date_from');
		$date_to        = Request::input('date_to');
		if($search === "undefined")
		{
			$search = null;
		}
		
		$date_today =  Carbon::Now()->format('Y/d/m');
		
		$query = Tbl_adjust_wallet_log::Slot()->Owner()->where("slot_status","active");

		if($search != '' || $search != null)
		{
			$query->where("users.name", "like", "%". $search . "%");
		}
		if($date_from != '' || $date_from != null)
		{
			$query->whereDate('date_created','>=',$date_from);
		}
		if($date_to != '' || $date_to != null)
		{
			$query->whereDate('date_created','<=',$date_to);
		}
		$query->orderBy('date_created','Desc');

		$headings = ['Member Name','Member Email','Slot Code','Currency','Amount','Date'];

		$mapper = function($list) {
			return [
				$list->name,
				$list->email,
				$list->slot_no,
				$list->adjusted_detail,
				$list->adjusted_currency . " " . $list->adjusted_amount,
				$list->date_created
			];
		};

		$export = new QueryExport($query, $headings, $mapper, 'Adjusted Wallet');
		return Excel::download($export, 'Adjusted Wallet as of '.$date_today.'.xlsx');
	}

	public function code_transfer_report_xlxs()
	{
		$search      	= Request::input('search');
		$date_from      = Request::input('date_from');
		$date_to        = Request::input('date_to');
		if($search === "undefined")
		{
			$search = null;
		}
		
		$date_today =  Carbon::Now()->format('Y/d/m');
		
		if($date_from == null || $date_to == null || $date_from == 'undefined' || $date_to == 'undefined')
		{
			$date_from 	= Carbon::now()->format('Y-m-d');
			$date_to   	= Carbon::now()->format('Y-m-d');
		}

		$query = Tbl_code_transfer_logs::leftJoin('tbl_codes','tbl_codes.code_id','=','tbl_code_transfer_logs.code_id')
										->select('tbl_code_transfer_logs.code_id','from_slot','to_slot','original_slot','date_transfer','code_activation','code_pin');

		if($search != '' || $search != null)
		{
			$search_slot = Tbl_slot::where("slot_no",$search)->first();
			$search2 = $search_slot ? $search_slot->slot_id : null;
			
			if($search2)
			{
				$query->where(function($q) use ($search2) {
					$q->where("from_slot", "like", "%". $search2 . "%")
					  ->orWhere("to_slot", "like", "%". $search2 . "%")
					  ->orWhere("original_slot", "like", "%". $search2 . "%");
				});
			}
			else
			{
				$query->where("tbl_codes.code_activation", "like", "%". $search . "%");
			}
		}
		if($date_from != '' || $date_from != null)
		{
			$query->whereDate('date_transfer','>=',$date_from);
		}
		if($date_to != '' || $date_to != null)
		{
			$query->whereDate('date_transfer','<=',$date_to);
		}
		$query->orderBy('date_transfer','Desc');

		$headings = ['Code','Pin','Origin Slot','From Slot','To Slot','Date Transfer'];
		
		$mapper = function($list) {
            $from = Tbl_slot::where("slot_id", $list->from_slot)->value("slot_no");
            $to = Tbl_slot::where("slot_id", $list->to_slot)->value("slot_no");
            $orig = Tbl_slot::where("slot_id", $list->original_slot)->value("slot_no");
			
			return [
				$list->code_activation,
				$list->code_pin,
				$orig,
				$from,
				$to,
				$list->date_transfer
			];
		};

		$export = new QueryExport($query, $headings, $mapper, 'Code Transfer Report');
		return Excel::download($export, 'Code Transfer Report as of '.$date_today.'.xlsx');
	}


	public function members_detail_report_xlxs()
	{
		$search      	= Request::input('search');
		$date_from      = Request::input('date_from');
		$date_to        = Request::input('date_to');
		if($search === "undefined")
		{
			$search = null;
		}
		if($date_from === "undefined")
		{
			$date_from = null;
		}
		if($date_to === "undefined")
		{
			$date_to = null;
		}
		
		$date_today =  Carbon::Now()->format('Y/d/m');
		
		$query = Users::leftJoin("tbl_country","tbl_country.country_id","=","users.country_id")
		              ->join('tbl_slot', 'tbl_slot.slot_owner', '=', 'users.id')
		              ->leftJoin('users as sponsor', 'sponsor.id', '=', 'tbl_slot.slot_sponsor') // Assuming slot_sponsor is User ID based on legacy line 904 logic, OR logic needs review.
					  // Legacy Logic Analysis:
					  // Line 904: leftJoin("users","users.id","=","tbl_slot.slot_sponsor") -> access via value2 (slot) -> slot_sponsor
					  // Line 910: Tbl_slot::where("slot_id",$value2["slot_sponsor"])->Owner()->first()
					  // This implied slot_sponsor IS A SLOT ID.
					  // So joining Users on slot_sponsor is likely WRONG in legacy if it was intended to get Sponsor Name directly from a User ID.
					  // But if slot_sponsor is a Slot ID, we should join Tbl_slot as sponsor_slot.
					  // Let's stick to safe 'map' logic for sponsor details to avoid invalid joins if schema is ambiguous.
					  ->select('users.*', 'tbl_country.country_name', 'tbl_slot.slot_id as current_slot_id', 'tbl_slot.slot_no', 'tbl_slot.slot_sponsor');

		if($search != '' || $search != null)
		{
			$query->where("users.name", "like", "%". $search . "%")->orWhere("users.email", "like", "%". $search . "%");
		}
		if($date_from != '' || $date_from != null)
		{
			$query->whereDate('users.created_at','>=',$date_from);
		}
		if($date_to != '' || $date_to != null)
		{
			$query->whereDate('users.created_at','<=',$date_to);
		}

		$headings = ['First Name','Middle Name','Last Name','Address','Email Address','Contact No.','Tin','Slot Code','Sponsor Code','Sponsor Name','Country'];
		
		$mapper = function($list) {
            $address = Tbl_address::where('user_id',$list->id)->where("archived",0)->Address()->where("is_default",1)->first();
			$address_str = $address ? ($address->address_info . " " . $address->brgyDesc . " " . $address->citymunDesc . " " . $address->provDesc) : "";
			
			$sponsor_slot = Tbl_slot::where("slot_id", $list->slot_sponsor)->first();
			$sponsor_code = $sponsor_slot ? $sponsor_slot->slot_no : null;
			$sponsor_name = ($sponsor_slot && $sponsor_slot->owner) ? $sponsor_slot->owner->name : null;

			return [
				$list->first_name,
				$list->middle_name,
				$list->last_name,
				$address_str,
				$list->email,
				$list->contact,
				$list->tin,
				$list->slot_no,
				$sponsor_code,
				$sponsor_name,
				$list->country_name
			];
		};

		$export = new QueryExport($query, $headings, $mapper, 'Members');
		return Excel::download($export, 'Members as of '.$date_today.'.xlsx');
	}

	public function slot_network_item_breakdown()
	{
		// dd(Request::input());
		$type    = Tbl_mlm_unilevel_settings::first()->is_dynamic;
		$data = Self::slot_network_item_breakdown_data(Request::input('slot_id'));
		$start = Carbon::now()->startOfMonth()->format('Y-m-d');
		$end   = Carbon::now()->endOfMonth()->format('Y-m-d');
		$excels['_list'] = $data;
		// dd($data);
		// dd($excels['_list']['log'][0]['items']);
		if($type == 'normal')
		{
			$excels['data']  =   ['Level Name','No. of Slots','Last Purchase','Earnings'];
		}
		else 
		{	
			$excels['data']  =   ['Level','Slot Code','Dynamic Level','PV'];
		}
		
		$excels['type'] = $type; // Pass type to view

        $export = new ViewExport('export.excel.slot_network_item_breakdown', $excels, 'template');
        return Excel::download($export, 'Unilevel Level Breakdown From: '.$start." To: ".$end.'.xlsx');
	}
	public function slot_network_item_breakdown_data($slot_id)
	{

		$data = self::unilevel($slot_id);

		return $data;
	}
	public function unilevel($slot_id)
    {
		$type    = Tbl_mlm_unilevel_settings::first()->is_dynamic;
		if($type == 'normal')
		{
			$slot                    = Tbl_slot::where("slot_id",$slot_id)->first();
			$data                    = null;
			$total_ppv               = 0;
			$total_gpv               = 0;
			$required_ppv            = 0;
			$log                     = null;
			$ctr                     = 0;
	
			if($slot)
			{
				$membership                            = Tbl_membership::where("membership_id",$slot->slot_membership)->first();
				if($membership)
				{
					$membership->membership_unilevel_level = $membership->membership_unilevel_level;
					$level                                 = 1;
	
	
					$required_ppv                    = $membership->membership_required_pv;
					$first_date                      = Carbon::now()->startOfMonth();
					$end_date                        = Carbon::now()->endOfMonth();
	
					$log[$ctr]["level_name"]         = "Personal Purchase";
					$log[$ctr]["number_of_slots"]    = Tbl_unilevel_points::where("unilevel_points_slot_id",$slot->slot_id)->where("unilevel_points_date_created",">=",$first_date)->where("unilevel_points_date_created","<=",$end_date)->where("unilevel_points_type","UNILEVEL_PPV")->count() ? Tbl_unilevel_points::where("unilevel_points_slot_id",$slot->slot_id)->where("unilevel_points_date_created",">=",$first_date)->where("unilevel_points_date_created","<=",$end_date)->where("unilevel_points_type","UNILEVEL_PPV")->count()." Purchase(s)" : "No Purchase";
					$log[$ctr]["last_slot_creation"] = Tbl_unilevel_points::where("unilevel_points_slot_id",$slot->slot_id)->where("unilevel_points_type","UNILEVEL_PPV")->first() ? Carbon::parse(Tbl_unilevel_points::where("unilevel_points_slot_id",$slot->slot_id)->where("unilevel_points_type","UNILEVEL_PPV")->orderBy("unilevel_points_date_created","DESC")->first()->unilevel_points_date_created)->format("m/d/Y") : "---";
					$log[$ctr]["earnings"]           = Tbl_unilevel_points::where("unilevel_points_slot_id",$slot->slot_id)->where("unilevel_points_type","UNILEVEL_PPV")->where("unilevel_points_date_created",">=",$first_date)->where("unilevel_points_date_created","<=",$end_date)->sum("unilevel_points_amount");
	
					$total_ppv = $total_ppv + $log[$ctr]["earnings"];
	
					$log[$ctr]["earnings"] = number_format($log[$ctr]["earnings"],2);
	
					$ctr++;
	
					while($membership->membership_unilevel_level >= $level)
					{
						$log[$ctr]["level_name"]         = $this->ordinal($level);
						$log[$ctr]["number_of_slots"]    = Tbl_unilevel_points::where("unilevel_points_slot_id",$slot->slot_id)->where("unilevel_points_date_created",">=",$first_date)->where("unilevel_points_date_created","<=",$end_date)->where("unilevel_points_cause_level",$level)->where("unilevel_points_type","UNILEVEL_GPV")->count() ? Tbl_unilevel_points::where("unilevel_points_slot_id",$slot->slot_id)->where("unilevel_points_cause_level",$level)->where("unilevel_points_date_created",">=",$first_date)->where("unilevel_points_date_created","<=",$end_date)->where("unilevel_points_type","UNILEVEL_GPV")->count()." Purchase(s)" : "No Purchase";
						$log[$ctr]["last_slot_creation"] = Tbl_unilevel_points::where("unilevel_points_slot_id",$slot->slot_id)->where("unilevel_points_cause_level",$level)->where("unilevel_points_type","UNILEVEL_GPV")->first() ? Carbon::parse(Tbl_unilevel_points::where("unilevel_points_slot_id",$slot->slot_id)->where("unilevel_points_cause_level",$level)->where("unilevel_points_type","UNILEVEL_GPV")->orderBy("unilevel_points_date_created","DESC")->first()->unilevel_points_date_created)->format("m/d/Y") : "---";
						$log[$ctr]["earnings"]           = Tbl_unilevel_points::where("unilevel_points_slot_id",$slot->slot_id)->where("unilevel_points_cause_level",$level)->where("unilevel_points_date_created",">=",$first_date)->where("unilevel_points_date_created","<=",$end_date)->where("unilevel_points_type","UNILEVEL_GPV")->sum("unilevel_points_amount");
	
						$total_gpv = $total_gpv + $log[$ctr]["earnings"];
	
						$log[$ctr]["earnings"] = number_format($log[$ctr]["earnings"],2);
	
						$ctr++;
						$level++;
					}
				}
			}
	
			$data["log"]           = $log;
			$data["total_ppv"]     = number_format($total_ppv,2);
			$data["total_gpv"]     = number_format($total_gpv,2);
			$data["required_ppv"]  = number_format($required_ppv,2);
			$data["passed"]        = $total_ppv >= $required_ppv ? 1 : 0;
			$data = self::get_level_item($data,$slot_id,$type);
		}
		else 
		{
			$slot                    = Tbl_slot::where("slot_id",$slot_id)->first();
			$data                    = null;
			$total_ppv               = 0;
			$total_gpv               = 0;
			$required_ppv            = 0;
			$log                     = null;
			$ctr                     = 0;
			if($slot)
			{
				$membership                            = Tbl_membership::where("membership_id",$slot->slot_membership)->first();
				if($membership)
				{
					$membership->membership_unilevel_level = $membership->membership_unilevel_level;
					$level                                 = 1;
					$required_ppv                    = $membership->membership_required_pv;
					$first_date                      =  Carbon::now()->startOfMonth()->format("Y-m-d");
					$end_date                        =  Carbon::now()->endOfMonth()->format("Y-m-d");
	
					$log[$ctr]["level_name"]         = "Personal Purchase";
					$log[$ctr]["number_of_slots"]    = Tbl_unilevel_points::where("unilevel_points_slot_id",$slot->slot_id)->where("unilevel_points_date_created",">=",$first_date)->where("unilevel_points_date_created","<=",$end_date)->where("unilevel_points_type","UNILEVEL_PPV")->count() ? Tbl_unilevel_points::where("unilevel_points_slot_id",$slot->slot_id)->where("unilevel_points_date_created",">=",$first_date)->where("unilevel_points_date_created","<=",$end_date)->where("unilevel_points_type","UNILEVEL_PPV")->count()." Purchase(s)" : "No Purchase";
					$log[$ctr]["last_slot_creation"] = Tbl_unilevel_points::where("unilevel_points_slot_id",$slot->slot_id)->where("unilevel_points_type","UNILEVEL_PPV")->first() ? Carbon::parse(Tbl_unilevel_points::where("unilevel_points_slot_id",$slot->slot_id)->where("unilevel_points_type","UNILEVEL_PPV")->orderBy("unilevel_points_date_created","DESC")->first()->unilevel_points_date_created)->format("m/d/Y") : "---";
					$log[$ctr]["earnings"]           = Tbl_unilevel_points::where("unilevel_points_slot_id",$slot->slot_id)->where("unilevel_points_type","UNILEVEL_PPV")->where("unilevel_points_date_created",">=",$first_date)->where("unilevel_points_date_created","<=",$end_date)->sum("unilevel_points_amount");
					// $log[$ctr]["number_of_slots"]    = Tbl_dynamic_compression_record::where("slot_id",$slot->slot_id)->whereDate("start_date","=",$first_date)->where("end_date","=",$end_date)->where("dynamic_level",$level)->count() ?
					//                                    Tbl_dynamic_compression_record::where("slot_id",$slot->slot_id)->where("dynamic_level",$level)->whereDate("start_date","=",$first_date)->whereDate("end_date","=",$end_date)->count()." Purchase(s)" : "No Purchase";
					// $log[$ctr]["earnings"]           = Tbl_dynamic_compression_record::where("slot_id",$slot->slot_id)->where("dynamic_level",$level)->where("start_date","=",$first_date)->sum("earned_points");
					// dd($log[$ctr]["earnings"],$slot->slot_id,$level,$first_date,$end_date);
					$total_ppv = $total_ppv + $log[$ctr]["earnings"];
	
					$log[$ctr]["earnings"] = number_format($log[$ctr]["earnings"],2);
	
					$ctr++;
	
					while($membership->membership_unilevel_level >= $level)
					{
						$log[$ctr]["level_name"]         = $this->ordinal($level);
						$log[$ctr]["number_of_slots"]    = Tbl_dynamic_compression_record::where("slot_id",$slot->slot_id)->whereDate("start_date","=",$first_date)->where("end_date","=",$end_date)->where("dynamic_level",$level)->count() ? Tbl_dynamic_compression_record::where("slot_id",$slot->slot_id)->where("dynamic_level",$level)->whereDate("start_date","=",$first_date)->whereDate("end_date","=",$end_date)->count()." Purchase(s)" : "No Purchase";
						$log[$ctr]["earnings"]           = Tbl_dynamic_compression_record::where("slot_id",$slot->slot_id)->where("dynamic_level",$level)->where("start_date","=",$first_date)->where("end_date","=",$end_date)->sum("earned_points");
	
						$total_gpv = $total_gpv + $log[$ctr]["earnings"];
	
						$log[$ctr]["earnings"] = number_format($log[$ctr]["earnings"],2);
	
						$ctr++;
						$level++;
					}
				}
				$history = Tbl_unilevel_distribute::where("slot_id",$slot->slot_id)->get();
				foreach ($history as $key => $value)
				{
					$history[$key]->sum = Tbl_wallet_log::whereDate("wallet_log_date_created",">=",$value->unilevel_distribute_date_start)->whereDate("wallet_log_date_created","<=",$value->unilevel_distribute_end_start)->where("wallet_log_details","=","UNILEVEL COMMISSION")->where("wallet_log_slot_id",$slot->slot_id)->sum("wallet_log_amount");
	
					if($history[$key]->sum == 0)
					{
						$history[$key]->is_qualified = 0;
					}
					else
					{
						$history[$key]->is_qualified = 1;
					}
				}
				$total_unilevel_sum = Tbl_wallet_log::where("wallet_log_details","UNILEVEL COMMISSION")->where("wallet_log_slot_id",$slot->slot_id)->sum("wallet_log_amount");
			}
	
			$data["log"]           = $log;
			$data["history"]       = $history;
			$data["total_history"] = number_format($total_unilevel_sum,2);
			$data["total_ppv"]     = number_format($total_ppv,2);
			$data["total_gpv"]     = number_format($total_gpv,2);
			$data["required_ppv"]  = number_format($required_ppv,2);
			$data["passed"]        = $total_ppv >= $required_ppv ? 1 : 0;
			$data = self::get_level_item($data,$slot_id,$type);		
		}
		return $data;

	}
	public function get_level_item($data,$slot_id,$type)
    {
		if($type == 'normal')
		{

			foreach ($data['log'] as $key => $value)
			{
				$slot  = Tbl_slot::where("slot_id",$slot_id)->first();
				$start = Carbon::now()->startOfMonth();
				$end   = Carbon::now()->endOfMonth();
				$data['log'][$key]['items'] = Tbl_unilevel_points::whereDate("unilevel_points_date_created",">=",$start)
												->whereDate("unilevel_points_date_created","<=",$end)
												->where("unilevel_points_slot_id",$slot->slot_id)
												->where("unilevel_points_cause_level",$key)
												// ->get();
												->select('unilevel_item_id', DB::raw('count(*) as total'))
												->groupBy('unilevel_item_id')
												->get();
												//  dd($return);
				foreach ($data['log'][$key]['items'] as $key2 => $value)
				{
					$points = Tbl_item::where("item_id",$value->unilevel_item_id)->first();
					if($points->item_pv != 0)
					{
						$data['log'][$key]['items'][$key2]->sum_points   =  $points->item_pv*$value->total;
					}
					else
					{
						$data['log'][$key]['items'][$key2]->sum_points   =  0;
					}
	
					$data['log'][$key]['items'][$key2]->item_desc  =  $points;
				}
				$data['log'][$key]['level']= $key;
			}
		}
		else 
		{
			foreach ($data['log'] as $key => $value)
			{
				$slot  = Tbl_slot::where("slot_id",$slot_id)->first();
				$start = Carbon::now()->startOfMonth()->format('Y-m-d');
				$end   = Carbon::now()->endOfMonth()->format('Y-m-d');
				$data['log'][$key]["level"] = $key;
				$query                      = Tbl_dynamic_compression_record::where("tbl_dynamic_compression_record.slot_id",$slot_id)->leftJoin("tbl_slot","tbl_slot.slot_id","=","tbl_dynamic_compression_record.cause_slot_id")
											->whereDate("start_date",$start)->whereDate("end_date",$end)
											->where("dynamic_level",$key)
											->select("dynamic_level","earned_points","slot_no");
				$data['log'][$key]["total_points"] = $query->sum("earned_points");
				$data['log'][$key]["slots"]        = $query->get();
			}
			// dd($data);
		}


        return $data;
	}
	function ordinal($number)
    {
        $ends = array('th','st','nd','rd','th','th','th','th','th','th');
        if ((($number % 100) >= 11) && (($number%100) <= 13))
            return $number. 'th';
        else
            return $number. $ends[$number % 10];
	}

	public function export_selected_orders_xls()
	{
		$data = Request::input();
		$query = DB::table('tbl_orders')
		->join('tbl_slot', 'tbl_slot.slot_id', '=', 'tbl_orders.buyer_slot_id')
		->join('users', 'users.id', '=', 'tbl_slot.slot_owner');
		if(($data['from'] ?? null) != "undefined")
		{
			$query = $query->whereDate('order_date_created', '>=', $data['from']);
		}

		if(($data['to'] ?? null) != "undefined")
		{
			$query = $query->whereDate('order_date_created', '<=', $data['to']);
		}

		if(($data['status'] ?? 'all') == 'all')
		{
			$array['_list'] = $query->get();
		}
		else
		{
			$array['_list'] = $query->where('order_status', $data['status'])->get();
		}

		foreach($array['_list'] as $key => $value)
		{
			$array['_list'][$key]->item_list = DB::table('tbl_orders_rel_item')->where('rel_order_id', $value->order_id)->join('tbl_item', 'tbl_item.item_id', '=', 'tbl_orders_rel_item.item_id')->get();
			$array['_list'][$key]->receipt = DB::table('tbl_receipt')->where('receipt_order_id', $value->order_id)->first();
		}
		
		$filename = "ORDER LIST - " . strtoupper($data['status']);
		$export = new ViewExport('export.excel.adminOrderList_xls', $array, 'slot_payout_history');
		return Excel::download($export, $filename . '.xlsx');
	}

	public function export_selected_orders_pdf()
	{
		$data = Request::input();
		$query = DB::table('tbl_orders')
		->join('tbl_slot', 'tbl_slot.slot_id', '=', 'tbl_orders.buyer_slot_id')
		->join('users', 'users.id', '=', 'tbl_slot.slot_owner');
		if(($data['from'] ?? null) != "undefined")
		{
			$query = $query->whereDate('order_date_created', '>=', $data['from']);
		}

		if(($data['to'] ?? null) != "undefined")
		{
			$query = $query->whereDate('order_date_created', '<=', $data['to']);
		}

		if(($data['status'] ?? 'all') == 'all')
		{
			$array['_list'] = $query->get();
		}
		else
		{
			$array['_list'] = $query->where('order_status', $data['status'])->get();
		}


		foreach($array['_list'] as $key => $value)
		{
			$array['_list'][$key]->item_list = DB::table('tbl_orders_rel_item')->where('rel_order_id', $value->order_id)->join('tbl_item', 'tbl_item.item_id', '=', 'tbl_orders_rel_item.item_id')->get();
			$array['_list'][$key]->receipt = DB::table('tbl_receipt')->where('receipt_order_id', $value->order_id)->first();

		}
		
		$pdf = PDF::loadView('export.pdf.adminOrderList_pdf', $array);
		return $pdf->stream('AdminOrderList.pdf');
	}

	public function slot_network_list_csv()
	{
		$prime['id'] = Request::input('slot_id');
		$prime['type'] = Request::input('type');
		$prime['search'] = Request::input('search') == "null" || Request::input('search') == "" || Request::input('search') == "undefined"? null : Request::input('search');
		$prime['level'] = Request::input('level');
		$data["_slots"] = Slot::get_slot_network($prime);
		$data["_type"] =$prime['type'];

		$export = new ViewExport('export.csv.slot_network_list_csv', $data, 'slot_network_list');
		return Excel::download($export, 'slot_network_list.csv');
	}
	public function slot_network_list_pdf()
	{
		$prime['id'] = Request::input('slot_id');
		$prime['type'] = Request::input('type');
		$prime['search'] = Request::input('search') == "null" || Request::input('search') == "" || Request::input('search') == "undefined"? null : Request::input('search');
		$prime['level'] = Request::input('level');
		$data["_slots"] = Slot::get_slot_network($prime);
		$data["_type"] =$prime['type'];
		$pdf = PDF::loadView('export.pdf.slot_network_list_pdf', $data);
		return $pdf->stream('Slot Network List.pdf');
	}
	public function unilevel_dynamic_report_xlxs()
	{
		$search      = Request::input('search');
		$date_month  = Request::input('date_month');
		
		if($date_month == null || $date_month == 'undefined' || $date_month == '')
		{
				$start	 	= Carbon::now()->startofMonth();
				$end 			= Carbon::now()->endofMonth();
		}
		else
		{
				$start	 	= Carbon::parse($date_month)->startofMonth();
				$end 			= Carbon::parse($date_month)->endofMonth();
		}

		$query = Tbl_dynamic_compression_record::query()
			->leftJoin("tbl_slot","tbl_slot.slot_id","=","tbl_dynamic_compression_record.slot_id")
			->leftJoin("users","users.id","=","tbl_slot.slot_owner");

		if($search != '' && $search != null && $search != 'undefined')
		{
			$query->where(function($q) use ($search) {
				$q->where("users.name", "like", "%". $search . "%")
				  ->orWhere("users.email", "like", "%". $search . "%")
				  ->orWhere("tbl_slot.slot_no", "like", "%". $search . "%");
			});
		}
		
		if($start != '' && $start != null)
		{
			$query->whereDate('tbl_dynamic_compression_record.date_created','>=',$start);
		}
		if($end != '' && $end != null)
		{
			$query->whereDate('tbl_dynamic_compression_record.date_created','<=',$end);
		}
		
		$query->selectRaw('tbl_dynamic_compression_record.slot_id, sum(earned_points) as sum, tbl_slot.slot_no, users.first_name, users.last_name, users.middle_name, users.email, users.contact')
		      ->groupBy('tbl_dynamic_compression_record.slot_id', 'tbl_slot.slot_no', 'users.first_name', 'users.last_name', 'users.middle_name', 'users.email', 'users.contact');

		$headings = ['Slot Code','Customer Name','Contact','Email','Amount Earned for the Period'];
		
		$mapper = function($list) {
			return [
				$list->slot_no,
				$list->first_name . "," . $list->last_name . " " . $list->middle_name,
				$list->email,
				$list->contact,
				$list->sum
			];
		};

		$export = new QueryExport($query, $headings, $mapper, 'template');
		return Excel::download($export, 'Unilevel Dynamic Report From: '.$start." To: ".$end.'.xlsx');
	}

	public function export_payout_xls()
	{
		$array = Cashout::get_schedules(Request::input());
		foreach($array['list'] as $key => $value)
		{
			foreach($value->transactions as $key2 => $value2)
			{
				$transaction[$key][$key2] = $value2;
			}
		}

		//flatten transaction array
		if (!is_array($transaction)) 
		{ 
			return FALSE; 
		} 
		$result = array(); 
		foreach ($transaction as $key => $value) 
		{ 
			if (is_array($value)) 
			{ 
				$result = array_merge($result, array_flatten($value)); 
			} 
			else 
			{ 
				$result[$key] = $value; 
			} 
		} 
		$data['_list'] = $result;
		
		$export = new ViewExport('export.excel.exportPayout_xls', $data, 'export_payout');
		return Excel::download($export, 'export_payout.xlsx');
	}

	public function export_admin_inventory_xls()
	{
		$data['_list'] = Item::get_inventory(Request::input());
		$branch_details = Tbl_branch::where('branch_id', $data['_list'][0]->inventory_branch_id)->first();
		$data['branch']	= $branch_details;

		$export = new ViewExport('export.excel.exportAdminInventory_xls', $data, 'Inventory');
		return Excel::download($export, 'Inventory.xlsx');
	}

	public function export_admin_inventory_pdf()
	{
		$data['_list'] = Item::get_inventory(Request::input());
		$branch_details = Tbl_branch::where('branch_id', $data['_list'][0]->inventory_branch_id)->first();
		$data['branch']	= $branch_details;
		$data = PDF::loadView('export.pdf.exportAdminInventory_pdf', $data);
		return $data->stream('InventoryReport.pdf');
	}

	public function export_admin_item_inventory_xls()
	{
		$data['_list'] = Item::get_item_inventory(Request::input());
		
		$export = new ViewExport('export.excel.exportAdminInventory_xls', $data, 'Inventory');
		return Excel::download($export, 'Inventory.xlsx');
	}

	public function export_admin_item_inventory_pdf()
	{
		$data['_list'] = Item::get_item_inventory(Request::input());
		$data = PDF::loadView('export.pdf.exportAdminInventory_pdf', $data);
		return $data->stream('InventoryReport.pdf');
	}

	public function export_top_seller_xls()
	{
		$filter = Request::input();
		$data = Tbl_receipt::groupBy('buyer_slot_id');
		$top_sellers['_list'] = [];

		if (isset($filter['date_from']) && $filter['date_from'] != "undefined") {
			$data = $data->whereDate('receipt_date_created', '>=', $filter['date_from']);
		}

		if (isset($filter['date_to']) && $filter['date_to'] != "undefined") {
			$data = $data->whereDate('receipt_date_created', '<=', $filter['date_to']);
		}

		if (isset($filter['search']) && $filter['search'] != "undefined") {
			$data = $data->where('buyer_slot_code', $filter['search']);
		}

		$top_sellers['_list'] = $data->select(
				'buyer_slot_id',
				DB::raw('SUM(grand_total) as total_sales')
			)->orderBy('total_sales', "desc")->limit(15)->get();

		// Fetch all slot IDs at once to reduce queries
		$slot_ids = $top_sellers['_list']->pluck('buyer_slot_id')->toArray();
		$users = Tbl_slot::Owner()
			->whereIn('tbl_slot.slot_id', $slot_ids)
			->get()
			->keyBy('slot_id');

		foreach ($top_sellers['_list'] as $key => $value) {
			$top_sellers['_list'][$key]['user_info'] = $users[$value->buyer_slot_id] ?? null;

			$query = Tbl_receipt::where('buyer_slot_id', $value->buyer_slot_id)
				->join('tbl_receipt_rel_item', 'tbl_receipt_rel_item.rel_receipt_id', '=', 'tbl_receipt.receipt_id')
				->join('tbl_item', 'tbl_item.item_id', '=', 'tbl_receipt_rel_item.item_id')
				->select(
					'tbl_item.item_id',
					'tbl_item.item_sku',
					DB::raw('MAX(tbl_receipt_rel_item.price) as price'),
					DB::raw('SUM(tbl_receipt_rel_item.quantity * tbl_receipt_rel_item.price) as subtotal'),
					DB::raw('SUM(tbl_receipt_rel_item.quantity) as quantity')
				)
				->groupBy('tbl_item.item_id', 'tbl_item.item_sku');

			if (isset($filter['item']) && $filter['item'] != 0) {
				$query->where('tbl_item.item_id', $filter['item']);
			}

			if (isset($filter['date_from']) && $filter['date_from'] != "undefined") {
				$query->whereDate('tbl_receipt.receipt_date_created', '>=', $filter['date_from']);
			}

			if (isset($filter['date_to']) && $filter['date_to'] != "undefined") {
				$query->whereDate('tbl_receipt.receipt_date_created', '<=', $filter['date_to']);
			}

			// Execute the query
			$receipts = $query->get();

			// If there are no matching receipts, remove this seller from the list
			if ($receipts->isNotEmpty()) {
				$top_sellers['_list'][$key]['receipts'] = $receipts;
				$top_sellers['_list'][$key]['user_info'] = Tbl_slot::Owner()->where('tbl_slot.slot_id', $value->buyer_slot_id)->first();
			} else {
				unset($top_sellers['_list'][$key]); // Remove seller from the list
			}
		}

		$filename = "Item_Purchased_Report_" . date('m_d_Y');
		$export = new ViewExport('export.excel.adminTopSellerReport_xls', ['top_sellers' => $top_sellers], 'Top Sellers');
		return Excel::download($export, $filename . '.xlsx');
	}

	public function promo_report_xls()
	{
		$date   = Request::input("date_month") ? Request::input("date_month") : Carbon::now();
		$level   = Request::input("level") ? Request::input("level") : 1;
		$item_id = Request::input("item") ? Request::input("item") : 0;
		// $start = Carbon::parse($date)->startofMonth();
		// $end = Carbon::parse($date)->endofMonth();

        $export = new PromoReportExport($date, $level, $item_id);
		return Excel::download($export, 'Promo as of '.$date.'.xlsx');
	}
	public function survey_csv()
	{
		ini_set('memory_limit', '1000M');
		set_time_limit ( 7200 );

		$date_today =  Carbon::Now()->format('Y/d/m');
		$questions = DB::table('tbl_survey_question')->where('survey_archived', 0)->get();
		
		foreach ($questions  as $key1 => $question) {
					
				$total_count = 0;
				$questions[$key1]->choices	= DB::table('tbl_survey_choices')->where('survey_question_id',$question->id)->where('survey_choices_status',0)->get();
				foreach ($questions[$key1]->choices as $key2 => $choice) {
					$i = DB::table('tbl_survey_answer')->where('survey_choices_id',$choice->id)->count();
					$choice->count = $i;
					$total_count = $total_count + $i;
				}
				$questions[$key1]->total_count	= $total_count;	
		}
		
		$export = new ViewExport('export.excel.survey_report', ['questions' => $questions], 'Top Recruiter');
		return Excel::download($export, 'Survey summary as of '.$date_today.'.xlsx');
	}
	public static function get_percentage($count1,$count2){
		$a = 0;
		if($count1 && $count2){
			$a = ($count1/$count2)*100;
		}
		return round($a);
	}
	public function export_dragonpay_export_xls()
	{
		$data['_list'] = AdminOrderController::get_dragonpay_orders(Request::input(),1);

		$export = new ViewExport('export.excel.Dragonpay.AdminDragonpayOrders_xls', $data, 'Dragonpay Orders');
		return Excel::download($export, 'DragonpayOrders.xlsx');
	}
	public function export_dragonpay_export_pdf()
	{
		$data['_list'] = AdminOrderController::get_dragonpay_orders(Request::input(),1);
		$pdf = PDF::loadView('export.pdf.Dragonpay.AdminDragonpayOrders_pdf', $data);
		return $pdf->stream('AdminDragonpayOrderList.pdf');
	}
	public function export_dragonpay_payout_csv()
	{
		$schedule = Tbl_cash_out_schedule::where('schedule_id',Request::get('schedule_id'))->first();
		if($schedule->schedule_method_id != 0)
		{
			$data["methods"] = Tbl_cash_out_method::where('cash_out_method_id',$schedule->schedule_method_id)->get();
		}
		else
		{
			$data["methods"] = Tbl_cash_out_method::get();
		}
		foreach ($data["methods"] as $key => $value)
		{
			$data["methods"][$key]["transactions"] = Tbl_cash_out_list::where('schedule_id',$schedule->schedule_id)->where("tbl_cash_out_list.cash_out_method_id", $value->cash_out_method_id)->get();
		}
		// $data['column']  =   ['Proc','Acct','Type','Name','Amount','Date','TxnId','Email','Description','Slot Code'];
		
		$export = new DragonpayPayoutExport($data);
		return Excel::download($export, 'Payout.xlsx');
	}

}
