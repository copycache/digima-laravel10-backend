<!DOCTYPE html>
<html>
<head>
	<title>Slot Wallet History</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<style type="text/css">
	body
	{
	    font-family: 'Helvetica';
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
	}
	th, td
	{
		padding: 2.5px 5px;
	}
	h2
	{
		text-align: center;
	}
	</style>
</head>
<body>
	<h2 style="margin-top: 0; margin-bottom: 15px;">Slot Wallet History</h2>
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
				<td colspan="4" style="text-align: right;">Current Balance</td>
				<td>{{ number_format($total_wallet, 2) }}</td>
			</tr>
		</tbody>
	</table>
</body>
</html>
