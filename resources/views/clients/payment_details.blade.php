@extends('layouts.app')

@section('title', 'Payment Details')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/payment-history">Payment History</a></li>
    <li class="breadcrumb-item active">Payment Details</li>
@endsection

@section('content')
    <client-payment-details :payment="{{ json_encode($payment) }}" print-url="{{ $print_url }}">
    </client-payment-details>
@endsection