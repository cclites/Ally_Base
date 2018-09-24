@extends('layouts.app')

@section('title', 'Schedule')

@section('content')
    <business-schedule
        :business="{{ $active_business OR '{}' }}"
        :multi_location="{{ json_encode($multiLocation) }}"
        default-view="{{ $business->calendar_default_view ?? 'timelineWeek' }}">
    </business-schedule>
@endsection
