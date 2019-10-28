@extends('layouts.app')

@section('title', 'Open Shifts')

@section('content')
    <open-shifts
        :business="{{ $active_business OR '{}' }}"
        default-view="{{ $business->calendar_default_view ?? 'timelineWeek' }}">
    </open-shifts>
@endsection
