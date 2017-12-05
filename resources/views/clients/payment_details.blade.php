@extends('layouts.app')

@section('title', 'Payment Details')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/payment-history">Payment History</a></li>
    <li class="breadcrumb-item active">Payment Details</li>
@endsection

@section('content')
    <client-payment-details :payment="{{ $payment }}"></client-payment-details>
@endsection