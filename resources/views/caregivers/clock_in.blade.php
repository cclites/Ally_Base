@extends('layouts.app')

@section('title', 'Clock In')

@section('content')
    <div class="row">
        <div class="col-lg-6">
            <clock-in :events="{{ json_encode($events) }}" selected="{{ $schedule_id }}"></clock-in>
        </div>
    </div>
@endsection