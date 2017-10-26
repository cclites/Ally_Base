@extends('layouts.app')

@section('title', 'Caregiver Distance Report')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('business.caregivers.index') }}">Caregivers</a></li>
    <li class="breadcrumb-item active">Distance Report</li>
@endsection

@section('content')
    <caregiver-distance-report :clients="{{ $clients }}"></caregiver-distance-report>
@endsection