@extends('layouts.app')

@section('title', 'Projected Billing')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/business/reports">Reports</a></li>
    <li class="breadcrumb-item active">Projected Billing</li>
@endsection

@section('content')
    <projected-billing-report :client-options="{{ $clientOptions }}"
                       :client-type-options="{{ $clientTypeOptions }}"
                       :caregiver-options="{{ $caregiverOptions }}">
    </projected-billing-report>
@endsection
