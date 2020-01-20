<style>
    .col-sm-6 {
        float: left;
        width: 50%;
    }

    .client-head {
        background: grey;
    }

    .client-head h4 {
        color: white;
    }

    .client-entry {
        margin-top: 1rem;
        border: 2px solid lightgrey;
    }

    .caregiver-entry {
        margin-top: 1rem;
    }

    @media print {
        .client-entry {
            page-break-after: always;
        }
    }
</style>
@if($client_shift_groups->count() == 0)
    <h3 class="text-center">No Results</h3>
@endif
@foreach($client_shift_groups as $group)
    <div class="container client-entry">
        <div class="with-padding-bottom row client-head">
            <div class="col-sm-6">
                <h4>{{ $group->first()->client->name }}&nbsp;&nbsp; {{ $start_date }} - {{ $end_date }}</h4>
            </div>
        </div>
        @foreach($group->groupBy('caregiver_id') as $caregiver_shifts)
            <div class="row with-padding-bottom caregiver-entry">
                <div class="col-sm-12">
                    <h5><strong>Caregiver</strong> - {{ $caregiver_shifts->first()->caregiver->name }}</h5>
                </div>
            </div>
            @foreach($caregiver_shifts as $shift)
                <div style="margin: 0 2rem 1rem;" class="timesheet-entry">
                    <table class="table">
                        <tr>
                            <th>Clocked In</th>
                            <th>Clocked Out</th>
                            <th>Special Designation</th>
                            <th>Ally Fee</th>
                            <th>Hourly Total</th>
                            <th>Other Expenses</th>
                            <th>Mileage</th>
                            <th>Mileage Costs</th>
                            <th>Caregiver Total</th>
                            <th>Provider Total</th>
                            <th>Ally Total</th>
                            <th>Shift Total</th>
                        </tr>
                        <tr>
                            <td>{{ $shift->checked_in_time ? $shift->checked_in_time->setTimezone($timezone)->format('m/d/y g:i a') : '---' }}</td>
                            <td>{{ $shift->checked_out_time ? $shift->checked_out_time->setTimezone($timezone)->format('m/d/y g:i a') : '---' }}</td>
                            <td>
                                @switch($shift->hours_type)
                                    @case('default')
                                    Regular
                                    @break
                                    @case('overtime')
                                    Overtime
                                    @break
                                    @case('holiday')
                                    Holiday
                                    @break
                                @endswitch
                            </td>
                            <td>&dollar;{{ $shift->ally_fee }}</td>
                            <td>&dollar;{{ $shift->hourly_total }}</td>
                            <td>&dollar;{{ $shift->other_expenses }}</td>
                            <td>{{ $shift->mileage }}</td>
                            <td>&dollar;{{ $shift->mileage_costs }}</td>
                            <td>&dollar;{{ $shift->caregiver_total }}</td>
                            <td>&dollar;{{ $shift->provider_total }}</td>
                            <td>&dollar;{{ $shift->ally_total }}</td>
                            <td>&dollar;{{ $shift->shift_total }}</td>
                        </tr>
                        <tr>
                            <td colspan="12">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <span>Caregiver Comments:</span>
                                        <p>{{ $shift->caregiver_comments ?: 'None' }}</p>
                                    </div>
                                    <div class="col-sm-6">
                                        <span>Notes:</span>
                                        @if($shift->schedule && $shift->schedule->notes)
                                            <p>{{ $shift->schedule->notes }}</p>
                                        @endif
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <span>Activities Performed:</span>
                                        @if($shift->activities->count() == 0)
                                            <p>No activities recorded</p>
                                        @else
                                            <p>{!! $shift->activities->sortBy('name')->pluck('name')->unique()->implode('<br>') !!}</p>
                                        @endif
                                    </div>
                                    <div class="col-sm-6">
                                        <span>Issues on Shift:</span>
                                        @if($shift->issues->count() == 0)
                                            <p>No issues reported</p>
                                        @else
                                            <p>{!! $shift->issues->pluck('comments')->implode('<br>') !!}</p>
                                        @endif
                                    </div>
                                </div>

                                @if( $shift->questions->count() > 0 )

                                    <div class="row">

                                        <div><strong>Shift Questions:</strong></div>

                                        @foreach( $shift->questions as $question )

                                            <div class="col-sm-6" style="margin-bottom: 12px">

                                                <strong>{{ $question->question }}</strong>
                                                @if( $question->pivot->answer )

                                                    <p>{{ $question->pivot->answer }}</p>
                                                @else

                                                    <p style="color:grey">(Unanswered)</p>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                <div class="row">
                                    <div class="col-sm-12"><span style="font-weight: 500;">EVV</span></div>
                                    <div class="col-sm-6">
                                        <table class="table">
                                            <thead>
                                            <tr>
                                                <th colspan="2">Clock In</th>
                                            </tr>
                                            </thead>
                                            @if($shift->checked_in_latitude || $shift->checked_in_longitude)
                                                <tbody>
                                                <tr>
                                                    <th>Geocode</th>
                                                    <td>{{ $shift->checked_in_latitude }}
                                                        ,<br/>{{ $shift->checked_in_longitude }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Distance</th>
                                                    <td>{{ $shift->checked_in_distance }}m</td>
                                                </tr>
                                                </tbody>
                                            @elseif($shift->checked_in_number)
                                                <tbody>
                                                <tr>
                                                    <th>Phone Number</th>
                                                    <td>{{ $shift->checked_in_number }}</td>
                                                </tr>
                                                </tbody>
                                            @else
                                                <tbody>
                                                <tr>
                                                    <td colspan="2">No EVV data</td>
                                                </tr>
                                                </tbody>
                                            @endif
                                        </table>
                                    </div>
                                    <div class="col-sm-6">
                                        <table class="table">
                                            <thead>
                                            <tr>
                                                <th colspan="2">Clock Out</th>
                                            </tr>
                                            </thead>
                                            @if($shift->checked_out_latitude || $shift->checked_out_longitude)
                                                <tbody>
                                                <tr>
                                                    <th>Geocode</th>
                                                    <td>{{ $shift->checked_out_latitude }}
                                                        ,<br/>{{ $shift->checked_out_longitude }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Distance</th>
                                                    <td>{{ $shift->checked_out_distance }}m</td>
                                                </tr>
                                                </tbody>
                                            @elseif($shift->checked_out_number)
                                                <tbody>
                                                <tr>
                                                    <th>Phone Number</th>
                                                    <td>{{ $shift->checked_out_number }}</td>
                                                </tr>
                                                </tbody>
                                            @else
                                                <tbody>
                                                <tr>
                                                    <td colspan="2">No EVV data</td>
                                                </tr>
                                                </tbody>
                                            @endif
                                        </table>
                                    </div>
                                </div>
                                @if($shift->clientSignature)
                                    <div class="row with-padding-bottom">
                                        <div class="col-sm-12">
                                            <span style="font-weight: 500;">Client Signature</span>
                                            {!! $shift->clientSignature->content !!}
                                        </div>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            @endforeach
        @endforeach
    </div>
    <hr>
@endforeach