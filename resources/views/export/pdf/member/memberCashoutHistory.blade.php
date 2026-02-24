<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Cash out History</title>
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
			<div class="header-text">Cash-out History</div>
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
								<th>Wallet Deduction</th>
								<th>Charge</th>
								<th>Cash Out Amount</th>
							</tr>
							@foreach($_list as $list)
							<tr>
								<td>{{ date("F j, Y",strtotime($list->wallet_log_date_created))}}</td>
								<td>{{ $list->cash_out_status == "processing" ||  $list->cash_out_status == "Pending" ? "Processing" : $list->cash_out_date}}</td>
								<td>{{ $list->cash_out_status}}</td>
								<td>{{ number_format($list->cash_out_net_payout_actual, 2) }}</td>
								<td>{{ number_format($list->cash_out_net_payout_actual - $list->cash_out_net_payout, 2) }}</td>
								<td>{{ number_format($list->cash_out_net_payout, 2) }}</td>
							</tr>
							@endforeach
						</table>
					</div>
				</div>
			</div>
			
		</div>
		<br>
		
		<br><br><br>
		<div class="pdf-footer">PDF GENERATED : {{ date("F j, Y") }}</div>
	</body>
</html>
