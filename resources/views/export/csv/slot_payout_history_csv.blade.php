<!DOCTYPE html>
<html>
<head>
	<title>Slot Payout History</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
</head>
<body>
	<table>
		<thead>
			<tr>
				<th>Timestamp Requested</th>
				<th>Timestamp Processed</th>
				<th>Status</th>
				<th>Deposit Amount</th>
				<th>Additional Charge</th>
				<th>Total Wallet Deduction</th>
			</tr>
		</thead>
		<tbody>
			@foreach($_payout as $payout)
			<tr>
				<td>{{ date("n/j/Y", strtotime($payout->wallet_log_date_created)) }}</td>
				<td>{{ $payout->wallet_log_details }}</td>
				<td>Processed</td>
				<td>{{ number_format($payout->wallet_log_amount, 2) }}</td>
				<td>{{ number_format($payout->wallet_log_amount, 2) }}</td>
				<td>{{ number_format($payout->wallet_log_amount, 2) }}</td>
			</tr>
			@endforeach
			<tr>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td style="text-align: right;">Total Deposit</td>
				<td>{{ number_format($total_payout, 2) }}</td>
			</tr>
			<tr>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td style="text-align: right;">Total Charge</td>
				<td>{{ number_format($total_payout, 2) }}</td>
			</tr>
			<tr>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td style="text-align: right;">Total Wallet Deduction</td>
				<td>{{ number_format($total_payout, 2) }}</td>
			</tr>
		</tbody>
	</table>
</body>
</html>