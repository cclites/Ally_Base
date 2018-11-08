@extends('layouts.app')

@section('title', 'Client & Caregiver RateFactory')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/business/reports">Reports</a></li>
    <li class="breadcrumb-item active">Client & Caregiver RateFactory</li>
@endsection

@section('content')
    <business-client-caregivers-report></business-client-caregivers-report>
@endsection