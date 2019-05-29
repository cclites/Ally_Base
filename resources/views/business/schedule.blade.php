@extends('layouts.app')

@section('title', 'Schedule')

@section('content')
    <business-schedule
        :business="{{ $active_business OR '{}' }}"
        week-start="{{ $weekStart }}"
        default-view="{{ $business->calendar_default_view ?? 'timelineWeek' }}">
    </business-schedule>
@endsection
