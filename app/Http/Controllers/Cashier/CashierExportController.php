<?php
namespace App\Http\Controllers\Cashier;

use PDF;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Request;
use App\Exports\ViewExport;

class CashierExportController extends CashierController
{
    public function export_sales_report($ref)
    {
        $data 	= CashierItemController::load_sales_report(1);
        if($ref=="pdf")
        {
            $pdf['_list']  = $data;
            $pdf = PDF::loadView('export.pdf.cashierSalesReport', $pdf);
            return $pdf->stream('salesreport.pdf');
        }
        else
        {
            $xls['_list'] = $data;
            Excel::create("CashierSalesReport" ,function($excel) use ($xls)
            {
                $excel->sheet("Sales Report", function($sheet) use ($xls)
                {
                    $sheet->setOrientation('landscape');
                    $sheet->loadView('export.excel.cashierSalesReportxls', $xls);
                });
            
            })->export('xls');
        }
    }

    public function export_list_of_codes($ref)
    {
        $membership = Request::input('filter');
		$status = Request::input('status');
		$search = Request::input('search');
		$paginate = Request::input('paginate');
        $data = CashierItemController::load_list_of_codes($membership, $status, $search, $paginate);

        if($ref=="pdf")
        {
            $pdf['_list']  = $data['code_list'];

            $pdf = PDF::loadView('export.pdf.cashierListOfCodes', $pdf);
            return $pdf->stream('listofcodes.pdf');
        }
    }
}
