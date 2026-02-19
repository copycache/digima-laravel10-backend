<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Item Purchased Report</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <link href="{{ public_path('css/export_excel.css') }}" rel="stylesheet" type="text/css">
    </head>
    <body>
        <div class="table-header-container">
            <div class="header-info">
                <table>
                    <tr>
                        <th colspan="10">Item Purchased Report</th>
                    </tr>
                </table>
            </div>
        </div>
        
        <div class="table-body-container">
            <table>
                <tr>
                    <th style="text-align: center; vertical-align: middle;">No.</th>
                    <th style="text-align: center; vertical-align: middle;">Username</th>
                    <th style="text-align: center; vertical-align: middle;">Full Name</th>
                    <th style="text-align: center; vertical-align: middle;">Email Address</th>
                    <th style="text-align: center; vertical-align: middle;">Contact no.</th>
                    <th style="text-align: center; vertical-align: middle;">Item Name</th>
                    <th style="text-align: center; vertical-align: middle;">Quantity</th>
                    <th style="text-align: center; vertical-align: middle;">Price</th>
                    <th style="text-align: center; vertical-align: middle;">Subtotal</th>
                    <th style="text-align: center; vertical-align: middle;">Total Amount</th>
                </tr>
                @php $count = 1; @endphp
                @foreach($top_sellers['_list'] as $list)
                    @php $rowCount = count($list['receipts']); @endphp
                    @foreach($list['receipts'] as $index => $receipt)
                        <tr>
                            @if($index === 0 && $list['user_info'])
                                <td style="text-align: center; vertical-align: middle;" rowspan="{{ $rowCount }}">{{ $count++ }}</td>
                                <td style="text-align: center; vertical-align: middle;" rowspan="{{ $rowCount }}">
                                    {{ $list['user_info']->slot_no}}
                                </td>
                                <td style="text-align: center; vertical-align: middle;" rowspan="{{ $rowCount }}">
                                    {{ $list['user_info']->name}}
                                </td>
                                <td style="text-align: center; vertical-align: middle;" rowspan="{{ $rowCount }}">
                                    {{ $list['user_info']->email}}
                                </td>
                                <td style="text-align: center; vertical-align: middle;" rowspan="{{ $rowCount }}">
                                    {{ $list['user_info']->contact}}
                                </td>
                            @endif

                            @if($index)
                                <td style="text-align: center; vertical-align: middle;" colspan="5"></td>
                            @endif

                            <td style="text-align: center; vertical-align: middle;">{{ $receipt->item_sku }}</td>
                            <td style="text-align: center; vertical-align: middle;">{{ number_format($receipt->quantity, 0) }}</td>
                            <td style="text-align: center; vertical-align: middle;">₱{{ number_format($receipt->price, 2) }}</td>
                            <td style="text-align: center; vertical-align: middle;">₱{{ number_format($receipt->subtotal, 2) }}</td>
                           
                            @if($index === 0 && $list->total_sales)
                                <td style="text-align: center; vertical-align: middle;" rowspan="{{ $rowCount }}">
                                    <b>₱{{ number_format($list->total_sales, 2) }}</b>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                @endforeach
            </table>
        </div>
        
        <br><br><br><br><br>
        
        <div class="table-footer-container">
            <table>
                <tr>
                    <td colspan="10">Excel Generated: {{ date("F j, Y") }}</td>
                </tr>
            </table>
        </div>
    </body>
</html>
