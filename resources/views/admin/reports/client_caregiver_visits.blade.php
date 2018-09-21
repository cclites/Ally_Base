@extends('layouts.app')

@section('title', 'Client Caregiver Visits Report')

@section('content')
    <business-client-caregiver-visits-report
        :caregivers="{{ json_encode($caregivers) }}"
        :start-date="{{ json_encode(now()->subWeeks(4)->format('m/d/Y')) }}"
        :end-date="{{ json_encode(now()->format('m/d/Y')) }}"
        :clients="{{ json_encode($clients) }}">
    </business-client-caregiver-visits-report>
@endsection