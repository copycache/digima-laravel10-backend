<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Admin Cashflow Report</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		
    <link href="{{ public_path('css/export_excel.css') }}" rel="stylesheet" type="text/css">
    </head>

    <body>
        <div class="table-body-container">
        <table >
                <tr>
                    <th class="text-center">Slot no</th>
                    <th class="text-center">Name</th>
                    <th class="text-center">Contact</th>
                    <th class="text-center">Email</th>
                    <th class="text-center">Total Income Receive</th>
                    <th class="text-center">Amount Paid Out</th>
                    <th class="text-center">Wallet Amount</th>
                </tr>
                @foreach($_list as $list)
                <tr>
                    <td class="text-center text-secondary v-align-middle">{{$list['slot_no']}}</td>
                    <td class="text-center text-secondary v-align-middle">{{$list['name']}}</td>
                    <td class="text-center text-secondary v-align-middle">{{$list['contact']}}</td>
                    <td class="text-center text-secondary v-align-middle">{{$list['email']}}</td>
                    <td class="text-center text-secondary v-align-middle">{{$list['total_income_receive']}}</td>
                    <td class="text-center text-secondary v-align-middle">{{$list['amount_paid_out']}}</td>
                    <td class="text-center text-secondary v-align-middle">{{$list['wallet_amount']}}</td>
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
                    <td colspan="7">Excel Generated : {{date("F j, Y",strtotime(date('Y-m-d')))}}</td>
                </tr>
            </table>
			
        </div>
    </body>
</html>