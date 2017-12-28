@extends('layouts.app')

@section('title', 'Clock Out')

@section('content')
    <div class="row">
        <div class="col-lg-6">
            <div class="alert alert-success" role="alert">
                <strong>Clocked-in.</strong> You are currently clocked-in.  Clock out below.
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12 hidden-sm-up">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Schedule Notes</h4>
                    @if ($notes)
                        {!! nl2br(htmlentities($notes)) !!}
                    @else
                        No notes for this shift.
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <clock-out :shift="{{ $shift }}" :activities="{{ $activities }}" :care-plan-activity-ids="{{ json_encode($carePlanActivityIds) }}"></clock-out>
        </div>
        <div class="col-lg-6">
            <div class="card hidden-xs-down">
                <div class="card-body">
                    <h4 class="card-title">Schedule Notes</h4>
                    {!! nl2br(htmlentities($notes)) !!}
                </div>
            </div>

            @if(env('GMAPS_API_KEY') && $shift->verified)
                <iframe width="100%" height="450" frameborder="0" style="border:0" src="https://www.google.com/maps/embed/v1/place?q={{ $shift->checked_in_latitude }},{{ $shift->checked_in_longitude }}&amp;key={{ env('GMAPS_API_KEY') }}"></iframe>
            @endif
        </div>
    </div>
@endsection