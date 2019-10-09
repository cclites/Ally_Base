@extends('layouts.print')

@section('title')
    Printable Schedule
@endsection

@section('content')
    @include('layouts.partials.print_logo')
    <script>
        function onPrint() {
            window.print();
        }
    </script>
    <div class="container print-content">
        <div class="print-controls">
            <button class="btn btn-secondary btn-print" onClick="onPrint()">
                <i class="fa fa-print"></i>&nbsp;&nbsp; Print
            </button>
        </div>
        <div class="row">
            <div class="col-xs-12">
                @if($schedules->count() === 0)
                    <b>No scheduled shifts found.</b>
                @endif

                @php
                    $groups = [];

                    if ($group_by == 'none') {
                        $groups = $schedules->groupBy('date')->sortBy(function($group) {
                            return $group->first()->date;
                        });
                    } else if ($group_by == 'client') {
                        $groups = $schedules->groupBy('client_id');
                    } else if ($group_by == 'caregiver') {
                        $groups = $schedules->groupBy('caregiver_id');
                    }
                @endphp
                @foreach($groups as $group)
                    @php
                        $scheduleGroup = $group->sortBy(function($schedule) {
                            return $schedule->starts_at;
                        }, SORT_REGULAR, false);
                    @endphp
                    <h4 class="">
                        @if ($group_by == 'none')
                            {{ $scheduleGroup->first()->date }}
                        @elseif ($group_by == 'client')
                            {{ $scheduleGroup->first()->client->nameLastFirst }}
                        @elseif ($group_by == 'caregiver')
                            @if($scheduleGroup->first()->caregiver)
                                {{ $scheduleGroup->first()->caregiver->nameLastFirst }}
                            @else
                                No Caregiver
                            @endif
                        @endif
                    </h4>
                    <table class="table table-condensed">
                        <col width="30%">
                        <col width="30%">
                        <col width="20%">
                        <col width="20%">
                        <thead>
                            <tr>
                                @if ($group_by != 'none')
                                    <th>Date</th>
                                @endif
                                @if ($group_by != 'client')
                                    <th>Client</th>
                                @endif
                                @if ($group_by != 'caregiver')
                                    <th>Caregiver</th>
                                @endif
                                <th>Start</th>
                                <th>End</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($scheduleGroup as $schedule)
                                <tr>
                                    @if ($group_by != 'none')
                                        <td>{{ $schedule->date }}</td>
                                    @endif
                                    @if ($group_by != 'client')
                                        <td>{{ optional($schedule->client)->name }}</td>
                                    @endif
                                    @if ($group_by != 'caregiver')
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
                                    @endif
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