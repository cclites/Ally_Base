@extends('layouts.app')

@section('title', 'Payment Details')

@section('breadcrumbs')
    @switch(auth()->user()->role_type)
        @case('client')
            <li class="breadcrumb-item"><a href="/payment-history">Payment History</a></li>
            <li class="breadcrumb-item active">Payment Details</li>
            @break
        @case('office_user')
            <li class="breadcrumb-item"><a href="{{ url()->previous() }}#client_payment_history">Client Statements</a></li>
            <li class="breadcrumb-item active">Payment Details</li>
            @break
    @endswitch
@endsection

@section('content')
    <client-payment-details :payment="{{ $payment }}"
                            role-type="{{ auth()->user()->role_type }}"></client-payment-details>
@endsection