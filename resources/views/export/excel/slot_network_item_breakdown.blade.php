@if($type == 'normal')
    <table>
        <thead>
            <tr>
                @foreach($data as $header)
                    <th>{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($_list['log'] as $list)
                <tr>
                    <td>{{ $list['level_name'] }}</td>
                    <td>{{ $list['number_of_slots'] }}</td>
                    <td>{{ $list['last_slot_creation'] }}</td>
                    <td>{{ $list['earnings'] }}</td>
                </tr>
                @foreach($list['items'] as $item)
                    <tr>
                        <td></td>
                        <td>Item Name : {{ $item['item_desc']['item_sku'] }}</td>
                        <td>Total Purchase: {{ $item['total'] }}</td>
                        <td>Item PV : {{ $item['item_desc']['item_pv'] }}</td>
                        <td>Sum of Points: {{ $item['sum_points'] }}</td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>
@else
    <table>
        <thead>
            <tr>
                @foreach($data as $header)
                    <th>{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($_list['log'] as $list)
                @if($list['level_name'] != 'Personal Purchase')
                    <tr>
                        <td>{{ $list['level_name'] }}</td>
                    </tr>
                    @foreach($list['slots'] as $slot)
                        <tr>
                            <td></td>
                            <td>{{ $slot['slot_no'] }}</td>
                            <td>{{ $slot['dynamic_level'] }}</td>
                            <td>{{ $slot['earned_points'] }}</td>
                        </tr>
                    @endforeach
                    @if(count($list['slots']) == 0)
                        <tr><td></td></tr>
                    @endif
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>Total Points : {{ $list['total_points'] }}</td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>
@endif
