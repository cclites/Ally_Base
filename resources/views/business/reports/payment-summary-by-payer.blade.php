@extends('layouts.app')

@section('title', 'Payment Summary By Payer')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/business/reports">Reports</a></li>
    <li class="breadcrumb-item active">Payment Summary By Payer Report</li>
@endsection

@section('content')
    <payment-summary-by-payer />
@endsection