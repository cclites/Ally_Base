@extends('layouts.app')

@section('title', 'Caregiver Overtime Report')

@section('content')
    <business-overtime-report :caregivers="{{ json_encode($caregivers) }}"></business-overtime-report>
@endsection