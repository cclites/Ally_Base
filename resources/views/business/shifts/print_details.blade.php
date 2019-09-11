<div class="container-fluid" style="margin-top: 1rem;">
    <div class="row">
        <div class="col-sm-6">
            <!-- LEFT SIDE -->
            <div class="with-padding-bottom">
                <strong>Client</strong>
                <br/>
                {{ $shift->client->name }}
            </div>
            <div class="with-padding-bottom">
                <strong>Clocked In Time</strong><br/>
                {{ $shift->checked_in_time->setTimezone($timezone)->format('m/d/Y g:i A') }}
            </div>
            @if ($report_type != 'notes')
            <div class="with-padding-bottom">
                <strong>Mileage</strong><br/>
                {{ $shift->mileage }}
            </div>
            @endif
            <div class="with-padding-bottom">
                <strong>Special Designation</strong><br>
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
            </div>
            @if($shift->schedule && $shift->schedule->notes)
                <div class="with-padding-bottom">
                    <strong>Schedule Notes</strong><br/>
                    {{ $shift->schedule->notes }}
                </div>
            @endif
            <div class="with-padding-bottom">
                <h4>Activities Performed</h4>
                <div class="row">
                    <div class="col-sm-12">
                        @if($shift->activities->count() == 0)
                            <p>
                                No activities recorded
                            </p>
                        @else
                            <table class="table">
                                <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Name</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($shift->activities as $activity)
                                    <tr>
                                        <td>{{ $activity->code }}</td>
                                        <td>{{ $activity->name }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>
            </div>
            <!-- /end LEFT SIDE -->
        </div>

        <div class="col-sm-6">
            <!-- RIGHT SIDE -->
            <div class="with-padding-bottom">
                <strong>Caregiver</strong><br/>
                {{ $shift->caregiver->name }}
            </div>
            <div class="with-padding-bottom">
                <strong>Clocked Out Time</strong><br/>
                @if($shift->checked_out_time)
                    {{ $shift->checked_out_time->setTimezone($timezone)->format('m/d/Y g:i A') }}
                @else
                    (Still clocked in)
                @endif
                <br/>
            </div>
            @if ($report_type != 'notes')
                <div class="with-padding-bottom">
                    <strong>Other Expenses</strong><br/>
                    &dollar;{{ number_format($shift->other_expenses, 2) }}<br/>
                </div>
                @if($shift->other_expenses_desc)
                    <div class="with-padding-bottom">
                        <strong>Other Expenses Description</strong><br/>
                        {{ $shift->other_expenses_desc }}
                    </div>
                @endif
            @endif
            <div class="with-padding-bottom">
                <strong>Caregiver Comments</strong><br/>
                {!! $shift->caregiver_comments ? nl2br($shift->caregiver_comments) : 'No comments recorded' !!}
            </div>
            <div class="with-padding-bottom">
                <h4>Issues on Shift</h4>
                <div class="row">
                    <div class="col-sm-12">
                        @if($shift->issues->count() == 0)
                            <p>
                                No issues reported
                            </p>
                        @else
                            @foreach($shift->issues as $issue)
                                <p>
                                    @if($issue->caregiver_injury)
                                        <strong>The caregiver reported an injury to themselves.<br/></strong>
                                    @endif
                                    {{ $issue->comments }}
                                </p>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
            @if($shift->goals->count() > 0)
                <div class="with-padding-bottom">
                    <h4>Goals</h4>
                    @foreach($shift->goals as $goal)
                    <div class="row with-padding-bottom" >
                        <div class="col-sm-12">
                            <strong>{{ $goal->question }}</strong><br/>
                            {{ $goal->pivot->comments }}
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif

            <!-- /end RIGHT SIDE -->
        </div>
    </div>

    @if($shift->clientSignature)
        <div class="row with-padding-bottom">
            <div @if( $shift->business->co_caregiver_signature ) class="col-sm-6" @endif>
                <strong>Client Signature</strong>
                <div class="signature">
                    {!! $shift->clientSignature->content !!}
                </div>
            </div>
            @if( $shift->business->co_caregiver_signature )
                <div class="col-sm-6">
                    <strong>Caregiver Signature</strong>
                    <div class="signature">
                        {!! $shift->caregiverSignature->content !!}
                    </div>
                </div>
            @endif
        </div>
    @endif

    <h4>EVV</h4>
    <div class="row">
        <div class="col-sm-12">
            <strong>Was this shift electronically verified?</strong><br/>
            {{ $shift->verified ? 'Yes' : 'No' }}
        </div>
    </div>
    <div class="row">
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
                        <td>{{ $shift->checked_in_latitude }},<br/>{{ $shift->checked_in_longitude }}</td>
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
                        <td>{{ $shift->checked_out_latitude }},<br/>{{ $shift->checked_out_longitude }}</td>
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
</div>