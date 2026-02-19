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
				<th>Level</th>
				<th>Slot Code</th>
				<th>Slot Owner</th>
				<th>Timestamp Created</th>
				<th>Timestamp Placed</th>
			</tr>
		</thead>
		<tbody>
			@foreach($_slots as $slot)
			<tr>
				<td>{{ $_type == 'placement' ? $slot->placement_level : $slot->sponsor_level}}</td>
				<td>{{ $slot->slot_no }}</td>
				<td>{{ $slot->name}}</td>
        <td>{{ date("n/j/Y", strtotime($slot->slot_date_created)) }}</td>
        <td>{{ date("n/j/Y", strtotime($slot->slot_date_placed)) }}</td>
			</tr>
			@endforeach
		</tbody>
	</table>
</body>
</html>
