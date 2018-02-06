@extends('layouts.app')

@section('title', 'Reconciliation Report')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active">Reconciliation Report</li>
@endsection

@section('content')
    <admin-reconciliation-report
            client-id="{{ request('client_id') }}"
            business-id="{{ request('business_id') }}"
            caregiver-id="{{ request('caregiver_id') }}"
    >
    </admin-reconciliation-report>
@endsection