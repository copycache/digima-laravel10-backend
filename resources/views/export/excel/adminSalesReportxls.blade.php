<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Admin Sales Report</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		
    <link href="{{ public_path('css/export_excel.css') }}" rel="stylesheet" type="text/css">
    </head>

    <body>
        <div class="table-body-container">
        <table >
                <tr>
                    <th class="text-center">Receipt No</th>
                    <th class="text-center">Customer Name</th>
                    <th class="text-center">Item Name</th>
                    <th class="text-center">Price</th>
                    <th class="text-center">Quantity</th>
                    <th class="text-center">Amount</th>
                    <th class="text-center">Tax Rate</th>
                    <th class="text-center">Tax</th>
                    <th class="text-center">Total</th>
                </tr>
                @foreach($_list as $list)
                <tr>
                <td class="text-center text-secondary v-align-middle">{{$list['receipt_id']}}</td>
                <td class="text-center text-secondary v-align-middle">{{$list['buyer_name']}}</td>
                    <td class="text-center text-secondary v-align-middle">
                    @foreach($list['items'] as $item)
                        <div>
                            {{$item->item_sku}}
                        </div>
                    @endforeach
                    </td>
                    <td class="text-center text-secondary v-align-middle">
                    @foreach($list['items'] as $item)
                        <div>
                            {{$item->item_price}} 
                        </div>
                    @endforeach
                    </td>
                    <td class="text-center text-secondary v-align-middle">
                    @foreach($list['items'] as $item)
                        <div>
                            {{$item->quantity}} 
                        </div>
                    @endforeach
                    </td>
                    <td class="text-center text-secondary v-align-middle">PHP {{$list['subtotal'] }}</td>
                    <td class="text-center text-secondary v-align-middle">{{$list['tax_amount'] == 0 ? '0' : '12%'}}</td>
                    <td class="text-center text-secondary v-align-middle">PHP {{$list['tax_amount'] }}</td>
                    <td class="text-center text-secondary v-align-middle">PHP {{$list['grand_total'] }}</td>
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
                    <td colspan="6">Excel Generated : {{date("F j, Y",strtotime(date('Y-m-d')))}}</td>
                </tr>
            </table>
			
        </div>
    </body>
</html>