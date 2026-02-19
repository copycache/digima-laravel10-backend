<!DOCTYPE html>
<html lang="en">
    <head>
        <title>List of Codes</title>
        <style>
        @page
        {
            size: Legal landscape;
            margin: 0.4in;
        }
        .no-column {
            width: 40px; 
        }
        th, td {
            padding: 5px 0 !important;
        }
        </style>
        <link href="{{ public_path('css/export_pdf.css') }}" rel="stylesheet" type="text/css" />
    </head>
    <body>
        <div class="header-container">
            <div class="header-text">Cashier List of Codes</div>
        </div>
        <br>
        <br>
        <br>
        <br>
        <br>
        <div>
            <!-- <div class="box-border"> -->
                <div class="box-border-content">
                    <div class="table-container">
                        <table >
                            <tr>
                                <th class="text-center no-column">No.</th>
                                <th class="text-center">Membership Name</th>
                                <th class="text-center">Code</th>
                                <th class="text-center">Pin</th>
                                <th class="text-center">Receiver's Name</th>
                                <th class="text-center">Receiver's Contact No.</th>
                            </tr>
                            @foreach($_list as $count => $list)
                            <tr>
                                <td class="text-center text-secondary v-align-middle no-column">{{$count + 1}}</td>
                                <td class="text-center text-secondary v-align-middle ">{{$list['membership_name']}}</td>
                                <td class="text-center text-secondary v-align-middle ">{{$list['code_activation']}}</td>
                                <td class="text-center text-secondary v-align-middle ">{{$list['code_pin'] }}</td>
                                <td class="text-center text-secondary v-align-middle "></td>
                                <td class="text-center text-secondary v-align-middle "></td>
                            </tr>
                            @endforeach
                        </table>
                    </div>
                </div>
            <!-- </div> -->
			
        </div>
        <br>
		
        <br><br><br>
    </body>
</html>
