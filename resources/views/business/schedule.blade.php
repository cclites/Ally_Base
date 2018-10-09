@extends('layouts.app')
@if(Auth::user()->officeUser->businesses[0]->type == 'Franchisor')
    @section('title', 'Franchises')
    @section('breadcrumbs')
        <li class="breadcrumb-item"><a href="/">Home</a></li>
        <li class="breadcrumb-item active">Franchises</li>
    @endsection
    @section('content')
        <business-franchisor-dashboard>

        </business-franchisor-dashboard>
    @endsection
@else
    @section('title', 'Schedule')
    @section('content')
        <business-schedule
                :business="{{ $active_business OR '{}' }}"
                :multi_location="{{ json_encode($multiLocation) }}"
                default-view="{{ $business->calendar_default_view ?? 'timelineWeek' }}">
        </business-schedule>
    @endsection
@endif



