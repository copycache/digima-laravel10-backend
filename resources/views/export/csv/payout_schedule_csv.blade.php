<!DOCTYPE html>
<html>
<head>
	<title>Payout Schedule</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
</head>
<body>
	<table>
		@foreach($methods as $method)
		<thead>
			<tr>
				<th colspan="7" style="font-weight: 600; text-align: center; color: #888 !important">
					{{ $method->cash_out_method_name }}
				</th>
			</tr>
		</thead>
		<thead>
			<tr>
				<th>Member Name</th>
				<th>Slot Code</th>
				<th>Gross Payout</th>
				<th>Service Charge (%)</th>
				<th>Tax(10%)</th>
				<th>Method Fee(P)</th>
				<th>Message</th>
				<th>Amount Due</th>
				<th>Net Payout Amount</th>
			</tr>
		</thead>

		<!-- {{ $method->transactions }} -->
		<tbody>
			@if(count($method->transactions) > 0)
				@foreach($method->transactions as $tx)
				<tr>
				<td>{{ $tx->cash_out_name }}</td>
				<td>{{ $tx->cash_out_slot_code }}</td>
				<td>{{ $tx->cash_out_amount_requested }}</td>
				<td>{{ $tx->cash_out_method_service_charge }}</td>
				<td>{{ $tx->cash_out_method_tax }}</td>
				<td>{{ $tx->cash_out_method_fee }}</td>
				<td>{{ $tx->cash_out_method_message }}</td>
				<td>{{ $tx->cash_out_net_payout_actual }}</td>
				<td>{{ $tx->cash_out_net_payout }}</td>
				</tr>
				@endforeach
			@else
				<tr style="text-align: center;">
					<td colspan="9" style="text-align: center;">No payout requests for this method.</td>
				</tr>
			@endif
		</tbody>
		<tfoot>
			<tr>
				<td colspan="7"></td>
			</tr>
		</tfoot>
		@endforeach
	</table>
</body>
</html>

