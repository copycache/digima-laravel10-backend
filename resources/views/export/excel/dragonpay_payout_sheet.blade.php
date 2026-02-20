<table>
    <thead>
        <tr>
            <th>Proc</th>
            <th>Acct</th>
            <th>Type</th>
            <th>Name</th>
            <th>Amount</th>
            <th>Date</th>
            <th>TxnId</th>
            <th>Email</th>
            <th>Description</th>
            <th>Slot Code</th>
        </tr>
    </thead>
    <tbody>
        @foreach($transactions as $list)
            <tr>
                <td>{{ $method['cash_out_proc'] }}</td>
                <td>{{ $list['cash_out_secondary_info'] }}</td>
                <td>{{ $list['cash_out_optional_info'] }}</td>
                <td>{{ $list['cash_out_primary_info'] }}</td>
                <td>{{ $list['cash_out_net_payout'] }}</td>
                <td>{{ $list['cash_out_date'] }}</td>
                <td>{{ $list['txnid'] }}</td>
                <td>{{ $list['cash_out_email_address'] }}</td>
                <td>{{ $list['cash_out_method_message'] }}</td>
                <td>{{ $list['cash_out_slot_code'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
