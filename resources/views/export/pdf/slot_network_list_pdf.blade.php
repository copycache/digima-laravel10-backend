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
