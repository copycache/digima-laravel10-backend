<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Binary List</title>
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
			<div class="header-text">Binary List</div>
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
								<th>First Name</th>
                                <th>Middle Name</th>
                                <th>Last Name</th>
                                <th>Email</th>
                                <th>Contact</th>
                                <th>Username</th>
                                <th>Subscription Package</th>
                                <th>Position</th>
                                <th>Placement Level</th>
                                <th>Date Created</th>
							</tr>
                            <?php $key = 1; ?>
							@foreach($_list as $list)
							<tr>
								<td>{{$list->first_name}}</td>
                                <td>{{$list->middle_name}}</td>
                                <td>{{$list->last_name}}</td>
                                <td>{{$list->email}}</td>
                                <td>{{$list->contact}}</td>
                                <td>{{$list->slot_no}}</td>
                                <td>{{$list->membership_name}}</td>
                                <td>{{$list->placement_position}}</td>
                                <td>{{$list->placement_level}}</td>
                                <td>{{date("F j, Y",strtotime($list->slot_date_placed))}}</td>
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
