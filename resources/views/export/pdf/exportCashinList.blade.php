<!DOCTYPE html>
<html lang="en">
	<head>
		<title>CASHIN</title>
		<style>
		@page
		{
			size: Legal landscape;
			margin: 0.4in;
		}
		</style>
		<link href="{{ public_path('css/export_pdf.css') }}" rel="stylesheet" type="text/css" />
	</head>
	<body>
		<div class="header-container">
			<div class="header-text">CASHIN APPROVAl</div>
			<div class="header-text">PDF EXPORT</div>
		</div>
		<br>
		<br>
		<br>
		<br>
		<div class="header-container">
			<div class="header-text">APPROVAL LIST</div>
		</div>
		<br>
		<div>
			<div class="box-border">
				<div class="box-border-content">
					<div class="table-container">
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
								<td>{{ $list->cash_in_slot_code}}</td>
								<td>{{ $list->cash_in_member_name}}</td>
								<td>{{ $list->cash_in_method_name}}</td>
								<td>{{ $list->cash_in_currency}}</td>
								<td>{{ $list->cash_in_payable}}</td>
								<td>{{ $list->cash_in_charge}}</td>
								<td>{{ $list->cash_in_status}}</td>
								<td>{{ $list->cash_in_receivable}}</td>
							</tr>
							@endforeach
						</table>
					</div>
				</div>
			</div>
			
		</div>
		<br>
		
		<br><br><br>
		<div class="pdf-footer">PDF GENERATED : {{date("F j, Y",strtotime(date('Y-m-d')))}}</div>
	</body>
</html>
