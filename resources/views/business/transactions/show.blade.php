@extends('layouts.app')

@section('title', 'Transaction Details')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('business.reports.reconciliation') }}">Reconciliation Report</a></li>
    <li class="breadcrumb-item active">Transaction Details</li>
@endsection

@section('content')
    <business-transaction :transaction="{{ $transaction }}"
                          :shifts="{{ $shifts }}"
                          :caregiver-summary="{{ $caregiver_summary }}"
                          :client-summary="{{ $client_summary }}"></business-transaction>
@endsection