@extends('layouts.print')

@section('title')
    Printable Schedule
@endsection

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                
                @foreach($schedules->groupBy('date') as $scheduleGroup)
                    <h4 class="">
                        {{ $scheduleGroup->first()->date }}
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
                            @foreach($scheduleGroup as $schedule)
                                <tr>
                                    <td>{{ optional($schedule->client)->name }}</td>
                                    <td>
                                        @if($schedule->caregiver)
                                            {{ $schedule->caregiver->name }} -
                                            @if($schedule->caregiver->phoneNumbers->count() == 1)
                                                {{ $schedule->caregiver->phoneNumbers->first()->number }}
                                            @else
                                                {{ optional($schedule->caregiver->phoneNumbers->where('type', 'primary')->first())->number }}
                                            @endif
                                        @endif
                                    </td>
                                    <td>{{ $schedule->starts_at->format('g:i a') }}</td>
                                    <td>{{ $schedule->ends_at->format('g:i a') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endforeach
                
            </div>
        </div>
    </div>
@endsection