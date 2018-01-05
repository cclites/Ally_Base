@extends('layouts.print')

@section('title')
    Shift Details - Print
@endsection

@section('content')
    <style>
        .col-sm-6 {
            float: left;
            width: 50%;
        }
    </style>
    <div class="container-fluid" style="margin-top: 1rem;">
        <div class="with-padding-bottom row">
            <div class="col-sm-6">
                <strong>Client</strong>
                <br/>
                {{ $shift->client->name }}
            </div>
            <div class="col-sm-6">
                <strong>Caregiver</strong><br/>
                {{ $shift->caregiver->name }}
            </div>
        </div>
        @if($shift->client->client_type == 'LTCI' && !is_null($shift->signature))
            <div class="row with-padding-bottom">
                <div>
                    <strong>Client Signature</strong>
                    <div class="signature">
                        {!! $shift->signature->content !!}
                    </div>
                </div>
            </div>
        @endif
        <div class="row with-padding-bottom">
            <div class="col-sm-6">
                <strong>Clocked In Time</strong><br/>
                {{ $shift->checked_in_time }}
            </div>
            <div class="col-sm-6">
                <strong>Clocked Out Time</strong><br/>
                {{ $shift->checked_out_time }}<br/>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6 with-padding-bottom">
                <strong>Special Designation</strong><br>
                @switch($shift->hours_type)
                    @case('default')
                    None
                    @break
                    @case('overtime')
                    OT
                    @break
                    @case('holiday')
                    HOL
                    @break
                @endswitch
            </div>
        </div>
        @if($shift->schedule && $shift->schedule->notes)
            <div class="row with-padding-bottom">
                <div class="col-sm-12">
                    <strong>Schedule Notes</strong><br/>
                    {{ $shift->schedule->notes }}
                </div>
            </div>
        @endif
        <div class="row with-padding-bottom">
            <div class="col-sm-12">
                <strong>Caregiver Comments</strong><br/>
                {{ $shift->caregiver_comments ? $shift->caregiver_comments : 'No comments recorded' }}
            </div>
        </div>
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
        <h4>EVV</h4>
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
@endsection