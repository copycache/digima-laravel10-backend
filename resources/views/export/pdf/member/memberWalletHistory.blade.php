<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Transaction History</title>
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
			<div class="header-text">Transaction History</div>
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
								<th>Posting Date</th>
								<th>Detail</th>
								<th>Debit / Credit</th>
								<th>Amount</th>
								<th>Running Balance</th>
							</tr>
							@foreach($_list as $list)
							<tr>
								<td>{{ date("F j, Y",strtotime($list->wallet_log_date_created))}}</td>
								<td>{{ $list->wallet_log_details == 'ecommerce' ? 'Shop/Purchased' : $list->wallet_log_details}}</td>
								<td>{{ $list->wallet_log_type}}</td>
								<td>{{ number_format($list->wallet_log_amount, 2) }}</td>
								<td>{{ number_format($list->wallet_log_running_balance, 2) }}</td>
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
