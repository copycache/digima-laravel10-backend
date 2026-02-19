<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Dragonpay Order List</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <link href="{{ public_path('css/export_excel.css') }}" rel="stylesheet" type="text/css">

    </head>

    <body>
        <div class="table-body-container">
        <table >
                <tr>
                    <th class="text-center">Customer Name</th>
                    <th class="text-center">Slot No</th>
                    <th class="text-center">Item SKU</th>
                    <th class="text-center">Quantity</th>
                    <th class="text-center">Subtotal</th>
                    <th class="text-center">Convenience Fee</th>
                    <th class="text-center">Grand Total</th>
                    <th class="text-center">TxnID</th>
                    <th class="text-center">Reference No</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Message</th>
                    <th class="text-center">Date Order</th>
                    <th class="text-center">Date Accomplished</th>
                </tr>
                @foreach($_list as $arr)
                <tr>
                    <td class="text-center text-secondary v-align-middle">{{$arr['name']}}</td>
                    <td class="text-center text-secondary v-align-middle">{{$arr['slot_no']}}</td>
                    <td class="text-center text-secondary v-align-middle" style="vertical-align:middle">
                    @foreach($arr['items'] as $items)
                            <div>{{$items->item_sku}}</div><br>
                        @endforeach
                    </td>
                    <td class="text-center text-secondary v-align-middle" style="vertical-align:middle">
                        @foreach($arr['items'] as $items)
                            <div>{{$items->quantity}}</div><br>
                        @endforeach
                    </td>
                    <td class="text-center text-secondary v-align-middle">{{$arr['subtotal']}}</td>
                    <td class="text-center text-secondary v-align-middle">{{$arr['dragonpay_charged'] != null ? $arr['dragonpay_charged'] : 0}}</td>
                    <td class="text-center text-secondary v-align-middle">{{$arr['grandtotal']}}</td>
                    <td class="text-center text-secondary v-align-middle">{{$arr['dragonpay_txnid']}}</td>
                    <td class="text-center text-secondary v-align-middle">{{$arr['dragonpay_refno'] != null ? $arr['dragonpay_refno'] : '---'}}</td>
                    <td class="text-center text-secondary v-align-middle">{{$arr['dragonpay_status']}}</td>
                    <td class="text-center text-secondary v-align-middle">{{$arr['dragonpay_message']}}</td>
                    <td class="text-center text-secondary v-align-middle">{{$arr['created_at']}}</td>
                    <td class="text-center text-secondary v-align-middle">{{$arr['date_accomplished'] != null ? $arr['date_accomplished'] : '---'}}</td>
                </tr>
                @endforeach
            </table>
        </div>
    </body>
</html>