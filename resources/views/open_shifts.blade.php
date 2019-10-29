@extends('layouts.app')

@section('title', 'Open Shifts')

@section('content')
    <open-shifts
        :businesses="{{ $businesses }}"
        default-view="{{ $business->calendar_default_view ?? 'timelineWeek' }}"
        role_type="{{ $role_type }}">
    </open-shifts>
@endsection
