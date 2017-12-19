@extends('layouts.print')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col">
                <table class="table table-sm">
                    @foreach($events->groupBy('date') as $event_group)
                        <tr>
                            <th colspan="4"><div class="h5 mt-3">{{ $event_group[0]['date'] }}</div></th>
                        </tr>
                        <tr>
                            <th>Client</th>
                            <th>Caregiver</th>
                            <th>Start</th>
                            <th>End</th>
                        </tr>
                        @foreach($event_group as $event)
                            <tr>
                                <td>{{ $event['client_name'] }}</td>
                                <td>{{ $event['caregiver_name'] }}</td>
                                <td>{{ $event['start']->format('g:i a') }}</td>
                                <td>{{ $event['end']->format('g:i a') }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                </table>
            </div>
        </div>
    </div>
@endsection