@extends('layouts.print')

@section('title')
    Printable Schedule
@endsection

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                
                @foreach($events->groupBy('date') as $event_group)                     
                    <h4 class="">
                        {{ $event_group[0]['date'] }}
                    </h4>
                    <table class="table table-condensed">
                        <thead>
                            <tr>
                                <th>Client</th>
                                <th>Caregiver</th>
                                <th>Start</th>
                                <th>End</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($event_group as $event)
                                <tr>
                                    <td>{{ $event['client_name'] }}</td>
                                    <td>
                                        {{ $event['caregiver_name'] }} -
                                        @if($event['caregiver_phones'])
                                            @if($event['caregiver_phones']->count() == 1)
                                                {{ $event['caregiver_phones']->first()->number }}
                                            @else
                                                {{ optional($event['caregiver_phones']->where('type', 'primary')->first())->number }}
                                            @endif
                                        @endif
                                    </td>
                                    <td>{{ $event['start']->format('g:i a') }}</td>
                                    <td>{{ $event['end']->format('g:i a') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endforeach
                
            </div>
        </div>
    </div>
@endsection