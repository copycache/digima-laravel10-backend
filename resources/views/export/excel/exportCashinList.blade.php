<!DOCTYPE html>
<html lang="en">
	<head>
		<title>CASHIN APPROVAL</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		
	<link href="{{ public_path('css/export_excel.css') }}" rel="stylesheet" type="text/css">
	</head>

	<body>
		<div class="table-header-container">
			<table>
				<tr><th colspan="6">Excel Exported</th></tr>
				<tr><th colspan="6">MLM SYSTEM</th></tr>
			</table>
		</div>
		<br><br>
		
		<div class="header-info">
			<table>
				<tr>
					<th colspan="6">CASHIN APPROVAL</th>
				</tr>
			</table>
		</div>
		<div class="table-body-container">
			<table >
				<tr>
					<th>Slot Code</th>
					<th>Member Name</th>
					<th>Method</th>
					<th>Currency</th>
					<th>Amount Required</th>
					<th>Cash In Charge</th>
					<th>Cash In Status</th>
					<th>Member will receive</th>
				</tr>
				@foreach($_list as $list)
				<tr>
					<td class="text-center">{{ $list->cash_in_slot_code}}</td>
					<td class="text-center">{{ $list->cash_in_member_name}}</td>
					<td class="text-center">{{ $list->cash_in_method_name }}</td>
					<td class="text-center">{{ $list->cash_in_currency  }}</td>
					<td class="text-right">{{ $list->cash_in_payable  }}</td>
					<td class="text-right">{{ $list->cash_in_charge  }}</td>
					<td class="text-center">{{ $list->cash_in_status  }}</td>
					<td class="text-right font-weight-bold">{{ $list->cash_in_receivable }}</td>
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