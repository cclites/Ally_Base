@extends('layouts.app')

@section('title', 'Client & Caregiver Rates')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/business/reports">Reports</a></li>
    <li class="breadcrumb-item active">Client & Caregiver Rates</li>
@endsection

@section('content')
    <business-client-caregivers-report></business-client-caregivers-report>
@endsection