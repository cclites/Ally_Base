@extends('layouts.app')

@section('title', 'Clock Out')

@section('content')
    <div class="row">
        <div class="col-lg-6">
            <div class="alert alert-success" role="alert">
                <strong>Checked in.</strong> You are currently checked in.  Check out below.
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-6">
            <clock-out :shift="{{ $shift }}" :activities="{{ $activities }}"></clock-out>
        </div>
        <div class="col-lg-6">
            @if(env('GMAPS_API_KEY'))
                <iframe width="100%" height="450" frameborder="0" style="border:0" src="https://www.google.com/maps/embed/v1/place?q={{ $shift->checked_in_latitude }},{{ $shift->checked_in_longitude }}&amp;key={{ env('GMAPS_API_KEY') }}"></iframe>
            @endif
        </div>
    </div>
@endsection