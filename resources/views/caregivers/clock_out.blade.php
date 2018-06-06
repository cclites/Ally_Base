@extends('layouts.app')

@section('title', 'Clock Out')

@section('content')
    <div class="row">
        @if ($shift->schedule_id)
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
        @endif
        <div class="col-lg-6">
            <clock-out :shift="{{ $shift }}" :activities="{{ $activities }}" :care-plan-activity-ids="{{ json_encode($carePlanActivityIds) }}"></clock-out>
        </div>
        <div class="col-lg-6">
            @if ($shift->schedule_id)
                <div class="card hidden-xs-down">
                    <div class="card-body">
                        <h4 class="card-title">Schedule Notes</h4>
                        @if ($notes)
                            {!! nl2br(htmlentities($notes)) !!}
                        @else
                            No notes for this shift.
                        @endif
                    </div>
                </div>
            @endif

            @if(config('services.gmaps.key') && $shift->verified)
                <iframe width="100%" height="450" frameborder="0" style="border:0" src="https://www.google.com/maps/embed/v1/place?q={{ $shift->checked_in_latitude }},{{ $shift->checked_in_longitude }}&amp;key={{ config('services.gmaps.key') }}"></iframe>
            @endif
        </div>
    </div>
@endsection
