<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Cash in History</title>
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
			<div class="header-text">Cash-in History</div>
			<!-- <div class="header-text">PDF EXPORT</div> -->
		</div>
		<br>
		<br>
		<br>
		<br>
		<!-- <div class="header-container">
			<div class="header-text">APPROVAL LIST</div>
		</div> -->
		<br>
		<div>
			<div class="box-border">
				<div class="box-border-content">
					<div class="table-container">
						<table >
							<tr>
								<th>Request Date</th>
								<th>Process Date</th>
								<th>Status</th>
								<th>Wallet Addition</th>
								<th>Charge</th>
								<th>Cash In Amount</th>
							</tr>
							@foreach($_list as $list)
							<tr>
								<td>{{ date("F j, Y",strtotime($list->cash_in_date))}}</td>
								<td>{{ $list->cash_in_status == "processing"  || $list->cash_in_status == "pending" ? "Processing" : date("F j, Y",strtotime($list->cash_in_date))}}</td>
								<td>{{ $list->cash_in_status == "approved" ? "Processed" : "Processing"}}</td>
								<td>{{ number_format($list->cash_in_receivable),2}}</td>
								<td>{{ number_format($list->cash_in_charge),2}}</td>
								<td>{{ number_format($list->cash_in_payable),2}}</td>
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
