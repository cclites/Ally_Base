@extends('layouts.app')

@section('title', 'Payment Details')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('caregivers.reports.payment_history') }}">Payment History</a></li>
    <li class="breadcrumb-item active">Payment Details</li>
@endsection

@section('content')
    <caregiver-payment-details :shifts="{{ $shifts }}"></caregiver-payment-details>
@endsection