@extends('layouts.app')

@section('title', 'Schedule')

@section('content')
    <business-schedule :business="{{ $active_business OR '{}' }}"
                       default-view="{{ $business->calendar_default_view === 'month' ? 'month' : 'timelineWeek' }}">
    </business-schedule>
@endsection
