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
                    <th>Order No</th>
                    <th>Ordered Items</th>
                    <th>Qty</th>
                    <th>Customer</th>
                    <th>Contact</th>
                    <th>Date Ordered</th>
                    <th>Claim Code</th>
                    <th>Total</th>
                    <th>Address</th>
                    <th>Status</th>
                    <th>Date Updated</th>
                </tr>
                @foreach($_list as $arr)
                <tr>
                    <td>{{$arr->order_id}}</td>
                    <td>
                        @foreach($arr->item_list as $items)
                            <div>{{$items->item_sku}}</div><br>
                        @endforeach
                    </td>
                    <td>
                        @foreach($arr->item_list as $items)
                            <div>{{$items->quantity}}</div><br>
                        @endforeach
                    </td>
                    <td>{{$arr->buyer_name}}</td>
                    <td>{{$arr->contact}}</td>
                    <td>{{$arr->order_date_created}}</td>
                    <td>{{$arr->receipt->claim_code}}</td>
                    <td>{{$arr->grand_total}}</td>
                    <td>{{$arr->buyer_address}}</td>
                    <td>{{$arr->order_status}}</td>
                    <td>{{$arr->date_status_changed}}</td>
                </tr>
                @endforeach
            </table>
        </div>
    </body>
</html>