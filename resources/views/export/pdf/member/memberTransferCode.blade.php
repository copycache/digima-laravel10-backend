<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Transfer Code History</title>
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
			<div class="header-text">Transfer Code History</div>
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
                                <th>SKU</th>
                                <th>Type</th>
                                <th>Code</th>
                                <th>Pin</th>
                                <th>Origin Slot</th>
                                <th>From Slot</th>
                                <th>To Slot</th>
                                <th>Transfer Timestamp</th>
							</tr>
                            <?php $key = 1; ?>
							@foreach($_list as $list)
							<tr>
								<td>{{$key++}}</td>
                                <td>{{$list->item_sku}}</td>
                                <td>{{$list->kit}}</td>
                                <td>{{$list->code_activation}}</td>
                                <td>{{$list->code_pin}}</td>
                                <td>{{$list->original_slot_code['slot_no']}}</td>
                                <td>{{$list->from_slot_code['slot_no']}}</td>
                                <td>{{$list->to_slot_code['slot_no']}}</td>
                                <td>{{$list->date_transfer}}</td>
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
