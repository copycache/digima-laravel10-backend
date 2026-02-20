<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Models\Tbl_cash_out_method;

class PayoutScheduleExport implements WithMultipleSheets
{
    protected $methods;
    protected $headingsBank;
    protected $headingsRemit;

    public function __construct($methods, $headingsBank, $headingsRemit)
    {
        $this->methods = $methods;
        $this->headingsBank = $headingsBank;
        $this->headingsRemit = $headingsRemit;
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];

        foreach ($this->methods as $method) {
            $column = $method->cash_out_method_category == 'remittance' ? $this->headingsRemit : $this->headingsBank;
            
            // Replicate the mapping logic from AdminExportController
            $mapper = function($list) {
                return [
                    $list->cash_out_slot_code,
                    $list->cash_out_primary_info,
                    $list->cash_out_secondary_info,
                    $list->cash_out_optional_info,
                    $list->cash_out_email_address,
                    $list->cash_out_contact_number,
                    $list->cash_out_tin,
                    $list->cash_out_method_tax,
                    $list->cash_out_method_fee,
                    $list->cash_out_method_service_charge,
                    $list->survey_charge,
                    $list->product_charge,
                    $list->gc_charge,
                    $list->cash_out_savings,
                    $list->cash_out_net_payout_actual,
                    $list->cash_out_net_payout,
                    $list->cash_out_date
                ];
            };

            $sheets[] = new DynamicExport(
                $method->transactions,
                $column,
                $mapper,
                $method->cash_out_method_name
            );
        }

        return $sheets;
    }
}
