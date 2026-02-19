<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Order List</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    </head>
    <style type="text/css">
    body 
    {
        font-family: 'Sans-Serif';
        width: 100%;
        font-size: 10px;
        font-weight: bold;
    }
    table
    {
        width: 100%;
        border-collapse: collapse;
    }
    thead:before, thead:after { display: none; }
    tbody:before, tbody:after { display: none; }
    table, th, td
    {
        border: 0.01em solid #000;
        text-align: center;

    }
    </style>
    <body>
        <div>
            <table >
                <tr>
                    <th>Customer Name</th>
                    <th>Slot No</th>
                    <th>Item SKU</th>
                    <th>Quantity</th>
                    <th>Subtotal</th>
                    <th>Convenience Fee</th>
                    <th>Grand Total</th>
                    <th>TxnID</th>
                    <th>Reference No</th>
                    <th>Status</th>
                    <th>Message</th>
                    <th>Date Order</th>
                    <th>Date Accomplished</th>
                </tr>
                @foreach($_list as $arr)
                <tr>
                    <td>{{$arr['name']}}</td>
                    <td>{{$arr['slot_no']}}</td>
                    <td style="vertical-align:middle">
                    @foreach($arr['items'] as $items)
                            <div>{{$items->item_sku}}</div><br>
                        @endforeach
                    </td>
                    <td style="vertical-align:middle">
                        @foreach($arr['items'] as $items)
                            <div>{{$items->quantity}}</div><br>
                        @endforeach
                    </td>
                    <td>{{$arr['subtotal']}}</td>
                    <td>{{$arr['dragonpay_charged'] != null ? $arr['dragonpay_charged'] : 0}}</td>
                    <td>{{$arr['grandtotal']}}</td>
                    <td>{{$arr['dragonpay_txnid']}}</td>
                    <td>{{$arr['dragonpay_refno'] != null ? $arr['dragonpay_refno'] : '---'}}</td>
                    <td>{{$arr['dragonpay_status']}}</td>
                    <td>{{$arr['dragonpay_message']}}</td>
                    <td>{{$arr['created_at']}}</td>
                    <td>{{$arr['date_accomplished'] != null ? $arr['date_accomplished'] : '---'}}</td>
                </tr>
                @endforeach
            </table>
        </div>
    </body>
</html>