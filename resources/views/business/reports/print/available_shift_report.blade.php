@extends('layouts.print')

@section('content')

    @push('head')
        <style>
            table{
                width: 100%;
                font-size: 10px;
            }

            table thead tr{
                border-bottom: 1px solid black;
            }

            table tbody tr{
                border-bottom: 1px solid #ccc;
            }

            tr, th {
                padding: 8px 4px;
            }
        </style>

    @endpush

    <h3>
        {{ $business->name }}
    </h3>

    <p>
        Available Reports for {{ $client ? $client->nameLastFirst() : 'All Clients By Client' }}
    </p>

    <h5>
        {{ $start }} - {{ $end }}   Location: {{ $city ? $city : 'All Locations' }}
    </h5>

    <table>
        <thead>
            <tr>
                <th>Client</th>
                <th>City</th>
                <th>Case Manager</th>
                <th>Services</th>
                <th>Day</th>
                <th>Date</th>
                <th>Start Time</th>
                <th>End Time</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $row)
                <tr>
                    <td>{{ $row["client_name"] }}</td>
                    <td>{{ $row["client_city"] }}</td>
                    <td>{{ $row["case_manager"] }}</td>
                    <td>
                        @foreach($row["client_services"][0] as $service)
                            {{ $service["service_name"] }}<br>
                        @endforeach
                    </td>
                    <td>
                        @foreach($row["client_services"][0] as $service)
                            {{ $service["day"] }}<br>
                        @endforeach
                    </td>
                    <td>
                        @foreach($row["client_services"][0] as $service)
                            {{ $service["date"] }}<br>
                        @endforeach
                    </td>
                    <td>
                        @foreach($row["client_services"][0] as $service)
                            {{ $service["start_time"] }}<br>
                        @endforeach
                    </td>
                    <td>
                        @foreach($row["client_services"][0] as $service)
                            {{ $service["end_time"] }}<br>
                        @endforeach
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

@endsection
