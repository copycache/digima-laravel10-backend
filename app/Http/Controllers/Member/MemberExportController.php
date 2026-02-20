<?php
namespace App\Http\Controllers\Member;

use App\Globals\Code;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use PDF;
use App\Exports\BaseExport;
use App\Exports\ViewExport;
use App\Exports\DynamicExport;

use App\Models\Tbl_cash_in_proofs;
use App\Models\Tbl_slot;
use App\Models\Tbl_wallet_log;
use App\Models\Tbl_currency;
use App\Models\Tbl_tree_sponsor;
use App\Models\Tbl_tree_placement;
use App\Models\Tbl_code_transfer_logs;



class MemberExportController extends MemberController
{
    public function export_member_sponsor_list_csv()
    {
        $slot_id            = Request::input("slot_id");
        $genealogy_type     = Request::input('genealogy_type');

        if($genealogy_type=="BINARY")
        {
            $response = Tbl_tree_placement::where("placement_parent_id",$slot_id)->child()->Owner()->membership()->select("slot_id","placement_child_id","slot_no","slot_date_placed","placement_level","placement_position","placement_level","first_name","last_name","email","contact","membership_name")->get();
            $headings = ['First Name','Middle Name','Last Name','Email','Contact','Slot Nos','Membership','Position','Placement Level','Date Placed'];
            
            $mapper = function($list) {
                return [
                    $list->first_name,
                    $list->middle_name,
                    $list->last_name,
                    $list->email,
                    $list->contact,
                    $list->slot_no,
                    $list->membership_name,
                    $list->placement_position,
                    $list->placement_level,
                    date("F j, Y",strtotime($list->slot_date_placed))
                ];
            };
        }
        else
        {
            $response = Tbl_tree_sponsor::where("sponsor_parent_id",$slot_id)->child()->Owner()->membership()->select("slot_id","sponsor_child_id","slot_no","slot_date_created","sponsor_level","first_name","last_name","email","contact","membership_name","slot_sponsor")->get();
            foreach ($response as $key => $value) 
            {
                $response[$key]->sponsor_code = Tbl_slot::where("slot_id",$value->slot_sponsor)->first()->slot_no;
            }
            $headings = ['First Name','Middle Name','Last Name','Email','Contact','Slot Nos','Membership','Sponsor Level','Sponsor Code','Date Created'];
            
            $mapper = function($list) {
                return [
                    $list->first_name,
                    $list->middle_name,
                    $list->last_name,
                    $list->email,
                    $list->contact,
                    $list->slot_no,
                    $list->membership_name,
                    $list->sponsor_level,
                    $list->sponsor_code,
                    date("F j, Y",strtotime($list->slot_date_created))
                ];
            };
        }

        $export = new DynamicExport($response, $headings, $mapper, 'SPONSOR LIST');
        
        return Excel::download($export, 'SPONSOR_LIST.xlsx');
    }
    public function export_member_sponsor_list_pdf()
    {
        $slot_id            = Request::input("slot_id");
        $genealogy_type     = Request::input('genealogy_type');

        if($genealogy_type=="BINARY")
        {
            $response           = Tbl_tree_placement::where("placement_parent_id",$slot_id)->child()->Owner()->membership()->select("slot_id","placement_child_id","slot_no","slot_date_placed","placement_level","placement_position","placement_level","first_name","last_name","email","contact","membership_name")->get();
            // $data     =   ['First Name','Middle Name','Last Name','Email','Contact','Slot Nos','Membership','Position','Placement Level','Date Placed'];
        }
        else
        {
            $response           = Tbl_tree_sponsor::where("sponsor_parent_id",$slot_id)->child()->Owner()->membership()->select("slot_id","sponsor_child_id","slot_no","slot_date_created","sponsor_level","first_name","last_name","email","contact","membership_name","slot_sponsor")->get();
            foreach ($response as $key => $value) 
            {
                $response[$key]->sponsor_code = Tbl_slot::where("slot_id",$value->slot_sponsor)->first()->slot_no;
            }
        }
        $excels['_list']    = $response;
        $excels['type']     = $genealogy_type;

        if($excels['type'] == "BINARY")
        {
            $pdf['_list']  = $response;
            $pdf = PDF::loadView('export.pdf.member.memberBinaryList', $pdf);
            return $pdf->stream('Binary_list.pdf');
        }
        else
        {
            $pdf['_list']  = $response;
            $pdf = PDF::loadView('export.pdf.member.memberUnilevelList', $pdf);
            return $pdf->stream('Unilevel_sponsor_list.pdf');
        }
    }
    public function export_member_product_code_csv()
    {
        $slot_id    = Request::input('slot_id');
        $key        = Request::input('key');
        $action     = Request::input('action');
        $user_id    = Request::input('code_owner');
        $paginate   = 0;
        $response   = Code::load_product_code($user_id,$slot_id,$key,$action,$paginate);

        $headings = ['No.','Product Code','Product Pin','Item SKU','Status','Date Usage','Date Sold'];
        
        $counter = 0;
        $mapper = function($list) use (&$counter) {
            $counter++;
            return [
                $counter,
                $list['code_activation'],
                $list['code_pin'],
                $list['item_sku'],
                $list['code_used']==1 ? 'USED' : 'UNUSED',
                $list['code_used']==1 ? $list['code_date_used'] : '----------UNUSED----------',
                $list['code_date_sold']
            ];
        };

        $export = new DynamicExport($response['code_list'], $headings, $mapper, 'PRODUCT CODE');
        return Excel::download($export, 'PRODUCT_CODE.xlsx');
    }

    public function export_member_product_code_pdf()
    {
        $slot_id    = Request::input('slot_id');
        $key        = Request::input('key');
        $action     = Request::input('action');
        $user_id    = Request::input('code_owner');
        $paginate   = 0;
        $response   = Code::load_product_code($user_id,$slot_id,$key,$action,$paginate);

        $pdf['_list']  = $response['code_list'];
        $pdf = PDF::loadView('export.pdf.member.memberProductCode', $pdf);
        return $pdf->stream('product_code_history.pdf'); 
    }

    public function export_member_slot_code_csv()
    {
        $slot_id    = Request::input('slot_id');
        $key        = Request::input('key');
        $action     = Request::input('action');
        $user_id    = Request::input('code_owner');
        $paginate   = 0;
        $response   = Code::load_membership_code($user_id,$slot_id,$key,$action,$paginate);

        $headings = ['No.','Slot Code','Slot Pin','Membership Name','Slot Quantity','Status','Date Usage','Date Sold'];

        $counter = 0;
        $mapper = function($list) use (&$counter) {
            $counter++;
            return [
                $counter,
                $list['code_activation'],
                $list['code_pin'],
                $list['membership_name'],
                $list['slot_qty'],
                $list['code_used']==1 ? 'USED' : 'UNUSED',
                $list['code_used']==1 ? $list['code_date_used'] : '----------UNUSED----------',
                $list['code_date_sold']
            ];
        };

        $export = new DynamicExport($response['code_list'], $headings, $mapper, 'SLOT CODE');
        return Excel::download($export, 'SLOT_CODE.xlsx');
    }

    public function export_member_slot_code_pdf()
    {
        $slot_id    = Request::input('slot_id');
        $key        = Request::input('key');
        $action     = Request::input('action');
        $user_id    = Request::input('code_owner');
        $paginate   = 0;
        $response   = Code::load_membership_code($user_id,$slot_id,$key,$action,$paginate);

        $pdf['_list']  = $response['code_list'];
        $pdf = PDF::loadView('export.pdf.member.memberRegistrationCode', $pdf);
        return $pdf->stream('Registration_code_history.pdf'); 
    }

    public function export_member_history_code_csv()
    {
        // $data = Request::input();
        $slot_id      = Request::input('slot_id') ? Request::input('slot_id') : null;
        $search      = Request::input('search');
        if($search == 'undefined')
        {
            $search = null;
        }
        
        $date_from 	= Request::input('date_from');
        $date_to   	= Request::input('date_to');
        $query = Tbl_code_transfer_logs::leftJoin('tbl_codes','tbl_codes.code_id','=','tbl_code_transfer_logs.code_id')
                                        ->leftJoin('tbl_inventory','tbl_inventory.inventory_id','=','tbl_codes.code_inventory_id')
                                        ->leftJoin('tbl_item','tbl_item.item_id','=','tbl_inventory.inventory_item_id')
                                        ->select('tbl_code_transfer_logs.code_id','from_slot','to_slot','original_slot','date_transfer','code_activation','code_pin','item_sku','item_type');		

        if($search != '' || $search != null)
        {
            $search2 = Tbl_slot::where("slot_no",$search)->first() ? Tbl_slot::where("slot_no",$search)->first()->slot_id : null;
            // dd($search2);
            if($search2 != '' || $search2 != null)
            {
                $query->where("from_slot", "like", "%". $search2 . "%")->orwhere("to_slot", "like", "%". $search2 . "%")->orwhere("original_slot", "like", "%". $search2 . "%");
            }
            else 
            {
                $query->where("tbl_codes.code_activation", "like", "%". $search . "%");
            }
        }
        if($slot_id != '' || $slot_id != null)
        {
            $query->where("from_slot", "=",$slot_id);
        }
        //lazy fixing; baligtad talaga yan, kasi baligtad yung sa html 
        if($date_from)
        {
            $query->whereDate('date_transfer','<=',$date_from);
        }
        if($date_to)
        {
            $query->whereDate('date_transfer','>=',$date_to);
        }
        $query->orderBy('date_transfer','DESC');
            
        $data  = $query->get();

        $headings = ['No.','SKU','Type','Code','Pin','Origin Slot','From Slot','To Slot','Transfer Timestamp'];
        
        $counter = 0;
        $mapper = function($list) use (&$counter) {
            $counter++;

            $kit = "Service Kit";
            if($list->item_type == 'membership_kit') {
                $kit = "Membership Kit";
            } else if($list->item_type == 'product') {
                $kit = "Product Kit";
            }

            $from_slot_code = Tbl_slot::where("slot_id", $list->from_slot)->value("slot_no");
            $to_slot_code = Tbl_slot::where("slot_id", $list->to_slot)->value("slot_no");
            $original_slot_code = Tbl_slot::where("slot_id", $list->original_slot)->value("slot_no");

            return [
                $counter,
                $list->item_sku,
                $kit,
                $list->code_activation,
                $list->code_pin,
                $original_slot_code,
                $from_slot_code,
                $to_slot_code,
                $list->date_transfer
            ];
        };

        $export = new DynamicExport($data, $headings, $mapper, 'HISTORY CODE');
        return Excel::download($export, 'HISTORY_CODE.xlsx');
    }
    public function export_member_history_code_pdf()
	{
        // $data = Request::input();
        $slot_id      = Request::input('slot_id') ? Request::input('slot_id') : null;
		$search      = Request::input('search');
		if($search == 'undefined')
		{
			$search = null;
		}
		
		$date_from 	= Request::input('date_from');
        $date_to   	= Request::input('date_to');
        $query = Tbl_code_transfer_logs::leftJoin('tbl_codes','tbl_codes.code_id','=','tbl_code_transfer_logs.code_id')
                                        ->leftJoin('tbl_inventory','tbl_inventory.inventory_id','=','tbl_codes.code_inventory_id')
                                        ->leftJoin('tbl_item','tbl_item.item_id','=','tbl_inventory.inventory_item_id')
										->select('tbl_code_transfer_logs.code_id','from_slot','to_slot','original_slot','date_transfer','code_activation','code_pin','item_sku','item_type');		

        if($search != '' || $search != null)
        {
            $search2 = Tbl_slot::where("slot_no",$search)->first() ? Tbl_slot::where("slot_no",$search)->first()->slot_id : null;
            // dd($search2);
            if($search2 != '' || $search2 != null)
            {
                $query->where("from_slot", "like", "%". $search2 . "%")->orwhere("to_slot", "like", "%". $search2 . "%")->orwhere("original_slot", "like", "%". $search2 . "%");
            }
            else 
            {
                $query->where("tbl_codes.code_activation", "like", "%". $search . "%");
            }
        }
        if($slot_id != '' || $slot_id != null)
		{
			$query->where("from_slot", "=",$slot_id);
        }
        //lazy fixing; baligtad talaga yan, kasi baligtad yung sa html 
		if($date_from)
		{
			$query->whereDate('date_transfer','<=',$date_from);
		}
		if($date_to)
		{
			$query->whereDate('date_transfer','>=',$date_to);
		}
		$query->orderBy('date_transfer','DESC');
			
        $data  = $query->get();

        foreach($data as  $key => $list)
        {
            // dd($list);
            if($list['item_type'] == 'membership_kit')
            {
                $data[$key]['kit']                  = "Membership Kit";
            }
            else if($list['item_type'] == 'product')
            {
                $data[$key]['kit']                  = "Product Kit";
            }
            else 
            {
                $data[$key]['kit']                  = "Service Kit";
            }
            $data[$key]['from_slot_code']       = Tbl_slot::where("slot_id",$list["from_slot"])->select("slot_no")->first();
            $data[$key]['to_slot_code']         = Tbl_slot::where("slot_id",$list["to_slot"])->select("slot_no")->first();
            $data[$key]['original_slot_code']   = Tbl_slot::where("slot_id",$list["original_slot"])->select("slot_no")->first(); 
        }


        $pdf['_list']  = $data;
        $pdf = PDF::loadView('export.pdf.member.memberTransferCode', $pdf);
        return $pdf->stream('Transfer_code_history.pdf'); 
       
    }
    public function export_wallet_history_csv()
    {
        $slot             = Tbl_slot::where("slot_id",Request::input("slot_id"))->first();
        $data             = null;
        $running_balance  = 0;
        $currency_default = Tbl_currency::where("currency_default",1)->first();
        if($currency_default)
        {
            $currency_id = $currency_default->currency_id;
        }
        else
        {
            $currency_id = null;
        }


        if($slot)
        {
            $data = Tbl_wallet_log::where("wallet_log_slot_id",$slot->slot_id)
                                    ->select("*",DB::raw("DATE_FORMAT(tbl_wallet_log.wallet_log_date_created, '%m/%d/%Y') as wallet_log_date_created"));

            if(Request::input('currency') != 'all')
            {
                $data->where("currency_id", Request::input('currency'));
            }
            else
            {
                // $data->where("currency_id",$currency_id);
            }
            if(Request::input('month') != 'all')
            {
                $data->whereMonth('wallet_log_date_created', Request::input('month'));
            }

            if(Request::input('year') != 'all')
            {
                $data->whereYear('wallet_log_date_created', Request::input('year'));
            }

            $data = $data->orderBy('wallet_log_date_created', 'DESC');
            $data = $data->get();

            $headings = ['Posting Date','Detail','Debit / Credit','Amount','Running Balance'];

            $mapper = function($list) {
                return [
                    date("F j, Y",strtotime($list->wallet_log_date_created)),
                    $list->wallet_log_details == 'ecommerce' ? 'Shop/Purchased' : $list->wallet_log_details,
                    $list->wallet_log_type,
                    $list->wallet_log_amount,
                    $list->wallet_log_running_balance
                ];
            };

            $export = new DynamicExport($data, $headings, $mapper, 'WALLET HISTORY');
            return Excel::download($export, 'WALLET_HISTORY.xlsx');
        }
    }
    public function export_wallet_history_pdf()
    {
        $slot             = Tbl_slot::where("slot_id",Request::input("slot_id"))->first();
        $data             = null;
        $running_balance  = 0;
        $currency_default = Tbl_currency::where("currency_default",1)->first();
        if($currency_default)
        {
            $currency_id = $currency_default->currency_id;
        }
        else
        {
            $currency_id = null;
        }


        if($slot)
        {
            $data = Tbl_wallet_log::where("wallet_log_slot_id",$slot->slot_id)
                                    ->select("*",DB::raw("DATE_FORMAT(tbl_wallet_log.wallet_log_date_created, '%m/%d/%Y') as wallet_log_date_created"));

            if(Request::input('currency') != 'all')
            {
                $data->where("currency_id", Request::input('currency'));
            }
            if(Request::input('month') != 'all')
            {
                $data->whereMonth('wallet_log_date_created', Request::input('month'));
            }

            if(Request::input('year') != 'all')
            {
                $data->whereYear('wallet_log_date_created', Request::input('year'));
            }

            $data = $data->orderBy('wallet_log_date_created', 'DESC');
            $data = $data->get();

            
            $pdf['_list']  = $data;
            $pdf = PDF::loadView('export.pdf.member.memberWalletHistory', $pdf);
            return $pdf->stream('transaction_history.pdf');


        }
    }
    public function export_cashin_history_csv()
    {
        $slot            = Tbl_slot::where("slot_id",Request::input("slot_id"))->first();
        $cash_in_history = Tbl_cash_in_proofs::where("cash_in_slot_code", $slot->slot_no)->get();
        
        $headings = ['Request Date','Process Date','Status','Wallet Addition','Charge','Cash In Amount'];

        $mapper = function($list) {
            return [
                date("F j, Y",strtotime($list->cash_in_date)),
                ($list->cash_in_status == "processing" || $list->cash_in_status == "pending") ? "Processing" : date("F j, Y",strtotime($list->cash_in_date)),
                $list->cash_in_status == "approved" ? "Processed" : "Processing",
                number_format($list->cash_in_receivable,2),
                number_format($list->cash_in_charge,2),
                number_format($list->cash_in_payable,2)
            ];
        };

        $export = new DynamicExport($cash_in_history, $headings, $mapper, 'CASHIN HISTORY');
        return Excel::download($export, 'CASHIN_HISTORY.xlsx');
    }
    public function export_cashin_history_pdf()
    {
        $slot            = Tbl_slot::where("slot_id",Request::input("slot_id"))->first();
        $cash_in_history = Tbl_cash_in_proofs::where("cash_in_slot_code", $slot->slot_no)->get();
   
        $pdf['_list']  = $cash_in_history;
        $pdf = PDF::loadView('export.pdf.member.memberCashinHistory', $pdf);
        return $pdf->stream('cashin_history.pdf');
    }
    public function export_cashout_history_csv()
    {
        $cash_out_history = Tbl_wallet_log::cashout()->where("wallet_log_slot_id", Request::input('slot_id'))->get();
        
        $headings = ['Request Date','Process Date','Status','Wallet Deduction','Charge','Cash Out Amount'];
        
        $mapper = function($list) {
            return [
                date("F j, Y",strtotime($list->wallet_log_date_created)),
                ($list->cash_out_status == "processing" || $list->cash_out_status == "pending") ? "Processing" : $list->cash_out_date,
                $list->cash_out_status,
                number_format($list->cash_out_net_payout_actual, 2),
                number_format($list->cash_out_net_payout_actual - $list->cash_out_net_payout, 2),
                number_format($list->cash_out_net_payout, 2)
            ];
        };

        $export = new DynamicExport($cash_out_history, $headings, $mapper, 'CASHOUT HISTORY');
        return Excel::download($export, 'CASHOUT_HISTORY.xlsx');
    }
    public function export_cashout_history_pdf()
    {
        $cash_out_history = Tbl_wallet_log::cashout()->where("wallet_log_slot_id", Request::input('slot_id'))->get();

        $pdf['_list']  = $cash_out_history;
        $pdf = PDF::loadView('export.pdf.member.memberCashoutHistory', $pdf);
        return $pdf->stream('cashout_history.pdf');        
    }
	public function export_member_dragonpay_csv()
	{
		$data['_list'] = MemberDashboardController::dragonpay_history(Request::input(),1);
        
        $export = new ViewExport('export.excel.Dragonpay.AdminDragonpayOrders_xls', $data, 'Dragonpay Orders');
        return Excel::download($export, 'Dragonpay_Orders.xlsx'); // Switched to xlsx as xls is deprecated/needs special lib
	}
}
