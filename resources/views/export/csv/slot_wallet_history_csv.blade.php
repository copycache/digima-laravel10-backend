<!DOCTYPE html>
<html>
<head>
	<title>Slot Wallet History</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
</head>
<body>
	<table>
		<thead>
			<tr>
				<th>Posting Date</th>
				<th>Detail</th>
				<th>Debit / Credit</th>
				<th>Amount</th>
				<th>Running Balance</th>
			</tr>
		</thead>
		<tbody>
			@foreach($_wallet as $wallet)
			<tr>
				<td>{{ date("n/j/Y", strtotime($wallet->wallet_log_date_created)) }}</td>
				<td>{{ $wallet->wallet_log_details }}</td>
				<td>{{ $wallet->wallet_log_type }}</td>
				<td>{{ number_format($wallet->wallet_log_amount, 2) }}</td>
				<td>{{ number_format($wallet->wallet_log_running_balance, 2) }}</td>
			</tr>
			@endforeach
			<tr>
				<td></td>
				<td></td>
				<td></td>
				<td>Current Balance</td>
				<td>{{ number_format($total_wallet, 2) }}</td>
			</tr>
		</tbody>
	</table>
</body>
</html>
