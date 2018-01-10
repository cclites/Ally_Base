@extends('layouts.app')

@section('title', 'Caregiver Overtime Report')

@section('content')
    <business-overtime-report :caregivers="{{ json_encode($caregivers) }}" :date-range="{{ json_encode($date_range) }}"></business-overtime-report>
@endsection