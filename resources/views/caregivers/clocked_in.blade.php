@extends('layouts.app')

@section('title', 'Clocked In')

@section('content')
    <div class="row">
        <div class="col-lg-6">
            <div class="alert alert-success" role="alert">
                You are currently clocked-in.
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-6">
            <a href="{{ route('clock_out') }}" class="btn btn-info btn-lg btn-block">Click here to Clock Out</a>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-6">
            <clocked-in
                    :shift="{{ $shift }}"
                    :activities="{{ $activities }}"
                    :care-plan-activity-ids="{{ json_encode($carePlanActivityIds) }}"
            />
        </div>
    </div>
@endsection
