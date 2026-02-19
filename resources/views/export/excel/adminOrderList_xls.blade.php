<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Order List</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <link href="{{ public_path('css/export_excel.css') }}" rel="stylesheet" type="text/css">

    </head>

    <body>
        <div class="table-body-container">
        <table >
                <tr>
                    <th class="text-center">Order No</th>
                    <th class="text-center">Ordered Items</th>
                    <th class="text-center">Ordered Quantity</th>
                    <th class="text-center">Customer Name</th>
                    <th class="text-center">Contact</th>
                    <th class="text-center">Date Ordered</th>
                    <th class="text-center">Claim Code</th>
                    <th class="text-center">Total</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Address</th>
                    <th class="text-center">Order Type</th>
                    <th class="text-center">Date Status Was Updated</th>
                </tr>
                @foreach($_list as $arr)
                <tr>
                    <td class="text-center text-secondary v-align-middle">{{$arr->order_id}}</td>
                    <td class="text-center text-secondary v-align-middle" style="vertical-align:middle">
                        @foreach($arr->item_list as $items)
                            <div>{{$items->item_sku}}</div><br>
                        @endforeach
                    </td>
                    <td class="text-center text-secondary v-align-middle" style="vertical-align:middle">
                        @foreach($arr->item_list as $items)
                            <div>{{$items->quantity}}</div><br>
                        @endforeach
                    </td>
                    <td class="text-center text-secondary v-align-middle">{{$arr->buyer_name}}</td>
                    <td class="text-center text-secondary v-align-middle">{{$arr->contact}}</td>
                    <td class="text-center text-secondary v-align-middle">{{$arr->order_date_created}}</td>
                    <td class="text-center text-secondary v-align-middle">{{$arr->receipt->claim_code}}</td>
                    <td class="text-center text-secondary v-align-middle">{{$arr->grand_total}}</td>
                    <td class="text-center text-secondary v-align-middle">{{$arr->order_status}}</td>
                    <td class="text-center text-secondary v-align-middle">{{$arr->buyer_address}}</td>
                    <td class="text-center text-secondary v-align-middle">{{$arr->delivery_method == 'none' ? 'Cashier' : $arr->delivery_method}}</td>
                    <td class="text-center text-secondary v-align-middle">{{$arr->date_status_changed}}</td>
                </tr>
                @endforeach
            </table>
        </div>
    </body>
</html>