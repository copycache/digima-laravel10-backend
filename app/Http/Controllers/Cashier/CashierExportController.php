<?php

namespace App\Http\Controllers\Cashier;

use App\Exports\ViewExport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use PDF;

class CashierExportController extends CashierController
{
    public function export_sales_report($ref)
    {
        $data = CashierItemController::load_sales_report(1);
        
        if ($ref == 'pdf') {
            $pdf = PDF::loadView('export.pdf.cashierSalesReport', ['_list' => $data]);
            return $pdf->stream('salesreport.pdf');
        }

        return Excel::create('CashierSalesReport', function($excel) use ($data) {
            $excel->sheet('Sales Report', function($sheet) use ($data) {
                $sheet->setOrientation('landscape');
                $sheet->loadView('export.excel.cashierSalesReportxls', ['_list' => $data]);
            });
        })->export('xls');
    }

    public function export_list_of_codes(Request $request, $ref)
    {
        $data = CashierItemController::load_list_of_codes(
            $request->input('filter'),
            $request->input('status'),
            $request->input('search'),
            $request->input('paginate')
        );

        if ($ref == 'pdf') {
            $pdf = PDF::loadView('export.pdf.cashierListOfCodes', ['_list' => $data['code_list']]);
            return $pdf->stream('listofcodes.pdf');
        }
    }
}
