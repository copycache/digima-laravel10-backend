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

        Excel::create('SPONSOR LIST', function($excel) use ($excels)
        {
            $excel->sheet('template', function($sheet) use ($excels)
            {
                $data = $excels['data'];
                $sheet->fromArray($data, null, 'A1', false, false);
                $sheet->freezeFirstRow();
                foreach($excels['_list'] as  $key => $list)
                {

                    if($excels['type'] == "BINARY")
                    {
                        $key = $key+=2;
                        $sheet->setCellValue('A'.$key, $list['first_name']);
                        $sheet->setCellValue('B'.$key, $list['middle_name']);
                        $sheet->setCellValue('C'.$key, $list['last_name']);
                        $sheet->setCellValue('D'.$key, $list['email']);
                        $sheet->setCellValue('E'.$key, $list['contact']);
                        $sheet->setCellValue('F'.$key, $list['slot_no']);
                        $sheet->setCellValue('G'.$key, $list['membership_name']);
                        $sheet->setCellValue('H'.$key, $list['placement_position']);
                        $sheet->setCellValue('I'.$key, $list['placement_level']);
                        $sheet->setCellValue('J'.$key, date("F j, Y",strtotime($list['slot_date_placed'])));
                    }
                    else
                    {
                        $key = $key+=2;
                        $sheet->setCellValue('A'.$key, $list['first_name']);
                        $sheet->setCellValue('B'.$key, $list['middle_name']);
                        $sheet->setCellValue('C'.$key, $list['last_name']);
                        $sheet->setCellValue('D'.$key, $list['email']);
                        $sheet->setCellValue('E'.$key, $list['contact']);
                        $sheet->setCellValue('F'.$key, $list['slot_no']);
                        $sheet->setCellValue('G'.$key, $list['membership_name']);
                        $sheet->setCellValue('H'.$key, $list['sponsor_level']);
                        $sheet->setCellValue('I'.$key, $list['sponsor_code']);
                        $sheet->setCellValue('J'.$key, date("F j, Y",strtotime($list['slot_date_created'])));
                    }
                }
            });
        })->download('xls');
    }
	public function export_member_product_code_csv()
	{
		$slot_id    = Request::input('slot_id');
        $key        = Request::input('key');
        $action     = Request::input('action');
        $user_id    = Request::input('code_owner');
        $paginate   = 0;
        $response   = Code::load_product_code($user_id,$slot_id,$key,$action,$paginate);


        $excels['_list'] = $response['code_list'];
		$excels['data']  =   ['No.','Product Code','Product Pin','Item SKU','Status','Date Usage','Date Sold'];
        Excel::create('PRODUCT CODE', function($excel) use ($excels)
        {
            $excel->sheet('template', function($sheet) use ($excels)
            {
                $data = $excels['data'];
                $sheet->fromArray($data, null, 'A1', false, false);
                $sheet->freezeFirstRow();
                foreach($excels['_list'] as  $key => $list)
                {
                    $key = $key+=2;
                    $sheet->setCellValue('A'.$key, $key -1);
                    $sheet->setCellValue('B'.$key, $list['code_activation']); 
                    $sheet->setCellValue('C'.$key, $list['code_pin']);
                    $sheet->setCellValue('D'.$key, $list['item_sku']);
                    $sheet->setCellValue('E'.$key, $list['code_used']==1 ? 'USED' : 'UNUSED');
                    $sheet->setCellValue('F'.$key, $list['code_used']==1 ? $list['code_date_used'] : '----------UNUSED----------');
                    $sheet->setCellValue('G'.$key, $list['code_date_sold']);
                }
            });
        })->download('xls');
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

        // dd($response);
        $excels['_list'] = $response['code_list'];
		$excels['data']  =   ['No.','Slot Code','Slot Pin','Membership Name','Slot Quantity','Status','Date Usage','Date Sold'];
        Excel::create('SLOT CODE', function($excel) use ($excels)
        {
            $excel->sheet('template', function($sheet) use ($excels)
            {
                $data = $excels['data'];
                $sheet->fromArray($data, null, 'A1', false, false);
                $sheet->freezeFirstRow();
                foreach($excels['_list'] as  $key => $list)
                {
                    $key = $key+=2;
                    $sheet->setCellValue('A'.$key, $key - 1);
                    $sheet->setCellValue('B'.$key, $list['code_activation']);
                    $sheet->setCellValue('C'.$key, $list['code_pin']);
                    $sheet->setCellValue('D'.$key, $list['membership_name']);
                    $sheet->setCellValue('E'.$key, $list['slot_qty']);
                    $sheet->setCellValue('F'.$key, $list['code_used']==1 ? 'USED' : 'UNUSED');
                    $sheet->setCellValue('G'.$key, $list['code_used']==1 ? $list['code_date_used'] : '----------UNUSED----------');
                    $sheet->setCellValue('H'.$key, $list['code_date_sold']);
                }
            });
        })->download('xls');
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
        // foreach ($response as $key => $value) 
        // {
        //     if($response[$key]['item_type'] == 'membership_kit')
        //     {
        //         $response[$key]['kit']                  = "Membership Kit";
        //     }
        //     else if($response[$key]['item_type'] == 'product')
        //     {
        //         $response[$key]['kit']                  = "Product Kit";
        //     }
        //     else 
        //     {
        //         $response[$key]['kit']                  = "Service Kit";
        //     }
        //     $response[$key]['from_slot_code']       = Tbl_slot::where("slot_id",$value->from_slot)->select("slot_no")->first();
        //     $response[$key]['to_slot_code']         = Tbl_slot::where("slot_id",$value->to_slot)->select("slot_no")->first();
        //     $response[$key]['original_slot_code']   = Tbl_slot::where("slot_id",$value->original_slot)->select("slot_no")->first();
        //     // dd($response[$key]);
        // }
        $excels['_list'] = $data;
		$excels['data']  =   ['No.','SKU','Type','Code','Pin','Origin Slot','From Slot','To Slot','Transfer Timestamp'];
        Excel::create('HISTORY CODE', function($excel) use ($excels)
        {
            $excel->sheet('template', function($sheet) use ($excels)
            {
                $data = $excels['data'];
                $sheet->fromArray($data, null, 'A1', false, false);
                $sheet->freezeFirstRow();
                foreach($excels['_list'] as  $key => $list)
                {
                    // dd($list);
                    if($list['item_type'] == 'membership_kit')
                    {
                        $list['kit']                  = "Membership Kit";
                    }
                    else if($list['item_type'] == 'product')
                    {
                        $list['kit']                  = "Product Kit";
                    }
                    else 
                    {
                        $list['kit']                  = "Service Kit";
                    }
                    $list['from_slot_code']       = Tbl_slot::where("slot_id",$list["from_slot"])->select("slot_no")->first();
                    $list['to_slot_code']         = Tbl_slot::where("slot_id",$list["to_slot"])->select("slot_no")->first();
                    $list['original_slot_code']   = Tbl_slot::where("slot_id",$list["original_slot"])->select("slot_no")->first();
                    // dd($list);
                    $key = $key+=2;
                    $sheet->setCellValue('A'.$key, $key -1);
                    $sheet->setCellValue('B'.$key, $list['item_sku']); 
                    $sheet->setCellValue('C'.$key, $list['kit']); 
                    $sheet->setCellValue('D'.$key, $list['code_activation']); 
                    $sheet->setCellValue('E'.$key, $list['code_pin']); 
                    $sheet->setCellValue('F'.$key, $list['original_slot_code']["slot_no"]); 
                    $sheet->setCellValue('G'.$key, $list['from_slot_code']["slot_no"]); 
                    $sheet->setCellValue('H'.$key, $list['to_slot_code']["slot_no"]); 
                    $sheet->setCellValue('I'.$key, $list['date_transfer']); 
                    
                }
            });
        })->download('xls');
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

            $excels['_list'] = $data;

            $excels['data']  =   ['Posting Date','Detail','Debit / Credit','Amount','Running Balance'];
            Excel::create('WALLET HISTORY', function($excel) use ($excels)
            {
                $excel->sheet('template', function($sheet) use ($excels)
                {
                    $data = $excels['data'];
                    $sheet->fromArray($data, null, 'A1', false, false);
                    $sheet->freezeFirstRow();
                    foreach($excels['_list'] as  $key => $list)
                    {
                        $key = $key+=2;
                        $sheet->setCellValue('A'.$key, date("F j, Y",strtotime($list['wallet_log_date_created'])));
                        $sheet->setCellValue('B'.$key, $list['wallet_log_details'] == 'ecommerce' ? 'Shop/Purchased' : $list['wallet_log_details']);
                        $sheet->setCellValue('C'.$key, $list['wallet_log_type']);
                        $sheet->setCellValue('D'.$key, $list['wallet_log_amount']);
                        $sheet->setCellValue('E'.$key, $list['wallet_log_running_balance']);
                    }
                });
            })->download('xls');


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
        $excels['_list'] = $cash_in_history;

        $excels['data']  =   ['Request Date','Process Date','Status','Wallet Addition','Charge','Cash In Amount'];
        Excel::create('CASHIN HISTORY', function($excel) use ($excels)
        {
            $excel->sheet('template', function($sheet) use ($excels)
            {
                $data = $excels['data'];
                $sheet->fromArray($data, null, 'A1', false, false);
                $sheet->freezeFirstRow();
                foreach($excels['_list'] as  $key => $list)
                {
                    $key = $key+=2;
                    $sheet->setCellValue('A'.$key, date("F j, Y",strtotime($list['cash_in_date'])));
                    $sheet->setCellValue('B'.$key, $list['cash_in_status'] == "processing"  || $list['cash_in_status'] == "pending" ? "Processing" : date("F j, Y",strtotime($list['cash_in_date'])));
                    $sheet->setCellValue('C'.$key, $list['cash_in_status'] == "approved" ? "Processed" : "Processing");
                    $sheet->setCellValue('D'.$key, number_format($list['cash_in_receivable'],2));
                    $sheet->setCellValue('E'.$key, number_format($list['cash_in_charge'],2));
                    $sheet->setCellValue('F'.$key, number_format($list['cash_in_payable'],2));
                }
            });
        })->download('xls');
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
        $excels['_list'] = $cash_out_history;

        $excels['data']  =   ['Request Date','Process Date','Status','Wallet Deduction','Charge','Cash Out Amount'];
        Excel::create('CASHOUT HISTORY', function($excel) use ($excels)
        {
            $excel->sheet('template', function($sheet) use ($excels)
            {
                $data = $excels['data'];
                $sheet->fromArray($data, null, 'A1', false, false);
                $sheet->freezeFirstRow();
                foreach($excels['_list'] as  $key => $list)
                {
                    $key = $key+=2;
                    $sheet->setCellValue('A'.$key, date("F j, Y",strtotime($list['wallet_log_date_created'])));
                    $sheet->setCellValue('B'.$key, $list['cash_out_status'] == "processing"  || $list['cash_out_status'] == "pending" ? "Processing" : $list['cash_out_date']);
                    $sheet->setCellValue('C'.$key, $list['cash_out_status']);
                    $sheet->setCellValue('D'.$key, number_format($list['cash_out_net_payout_actual']),2);
                    $sheet->setCellValue('E'.$key, number_format($list['cash_out_net_payout_actual'] - $list['cash_out_net_payout']),2);
                    $sheet->setCellValue('F'.$key, number_format($list['cash_out_net_payout']),2);
                }
            });
        })->download('xls');
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

		Excel::create("Dragonpay Orders", function($excel) use ($data)
		{
			$excel->sheet("Dragonpay Orders", function($sheet) use ($data)
			{
					$sheet->setOrientation('landscape');
					$sheet->loadView('export.excel.Dragonpay.AdminDragonpayOrders_xls', $data);
			});
		})->export('xls');
	}
}
