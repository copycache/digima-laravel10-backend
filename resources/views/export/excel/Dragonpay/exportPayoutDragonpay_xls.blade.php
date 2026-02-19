<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Payout Dragonpay Export</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		
    <link href="{{ public_path('css/export_excel.css') }}" rel="stylesheet" type="text/css">
    </head>

    <body>
        <div class="table-header-container">
        <div class="header-info">
            <table>
                <tr>
                    <th colspan="8">Payout Details</th>
                </tr>
            </table>
        </div>
        <div class="table-body-container">
            <table >
                <tr>
                    <th>Slot Code</th>
                    <th>Member Name</th>
                    <th>Currency</th>
                    <th>Amount Requested</th>
                    <th>Cash Out Method Fee</th>
                    <th>Cash Out Tax</th>
                    <th>Cash Out Savings</th>
                    <th>Member will receive</th>
                </tr>
                @foreach($_list as $list)
                <tr>
                    <td class="text-center">{{ $list->cash_out_slot_code}}</td>
                    <td class="text-center">{{ $list->cash_out_name}}</td>
                    <td class="text-center">{{ $list->cash_out_currency  }}</td>
                    <td class="text-right">{{ $list->cash_out_amount_requested  }}</td>
                    <td class="text-right">{{ $list->cash_out_method_fee  }}</td>
                    <td class="text-center">{{ $list->cash_out_method_tax  }}</td>
                    <td class="text-center">{{ $list->cash_out_savings  }}</td>
                    <td class="text-right font-weight-bold">{{ $list->cash_out_net_payout }}</td>
                </tr>
                @endforeach
            </table>
        </div>
        <br>
        <br>
        <br>
        <br>
        <br>
        <div class="table-footer-container">
            <table>
                <tr>
                    <td colspan="8">Excel Generated : {{date("F j, Y",strtotime(date('Y-m-d')))}}</td>
                </tr>
            </table>
			
        </div>
    </body>
</html>