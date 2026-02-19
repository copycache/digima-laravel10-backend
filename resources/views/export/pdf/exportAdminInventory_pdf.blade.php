<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Inventory Export</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		
    <link href="{{ public_path('css/export_excel.css') }}" rel="stylesheet" type="text/css">
    </head>

    <body>
        <div class="table-header-container">
        <div class="header-info">
           
        </div>
        <div class="table-body-container">
            <table >
                <tr>
                    <th>Branch Name</th>
                    <th>Item Name</th>
                    <th>Item Description</th>
                    <th>Selling Price</th>
                    <th>Quantity Sold</th>
                    <th>Quantity Used</th>
                    <th>Quantity Available</th>
                    <th>Quantity Claimed</th>
                    <th>Quantity Unclaimed</th>
                    <th>Total Quantity</th>
                </tr>
                @foreach($_list as $list)
                <tr>
                    <td class="text-center">{{ $list->branch_name}}</td>
                    <td class="text-center">{{ $list->item_sku}}</td>
                    <td class="text-center">{{ $list->item_description}}</td>
                    <td class="text-center">{{ $list->item_price  }}</td>
                    <td class="text-right">{{ $list->sold_codes  }}</td>
                    <td class="text-right">{{ $list->used_codes  }}</td>
                    <td class="text-center">{{ $list->inventory_quantity  }}</td>
                    <td class="text-center">{{ $list->claimed  }}</td>
                    <td class="text-center">{{ $list->unclaimed  }}</td>
                    <td class="text-right font-weight-bold">{{ $list->inventory_total }}</td>
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
                    <td colspan="6">PDF Generated : {{date("F j, Y",strtotime(date('Y-m-d')))}}</td>
                </tr>
            </table>
			
        </div>
    </body>
</html>