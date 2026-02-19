<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Registration Code History</title>
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
			<div class="header-text">Registration Code Historyy</div>
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
								<th>No.</th>
                                <th>Code</th>
                                <th>Pin</th>
                                <th>Subscription Package</th>
                                <th>Slot Quantity</th>
                                <th>Status</th>
                                <th>Date Used</th>
                                <th>Date Sold</th>
							</tr>
                            <?php $key = 1; ?>
							@foreach($_list as $list)
							<tr>
								<td>{{$key++}}</td>
                                <td>{{$list->code_activation}}</td>
                                <td>{{$list->code_pin}}</td>
                                <td>{{$list->membership_name}}</td>
                                <td>{{$list->slot_qty}}</td>
                                <td>{{$list->code_used == 1 ? 'USED' : 'UNUSED'}}</td>
                                <td>{{$list->code_used == 1 ? $list->code_date_used : "UNUSED"}}</td>
                                <td>{{$list->code_date_sold}}</td>
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
