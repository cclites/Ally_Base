@extends('layouts.app')

@section('title', 'Payment Details')

@section('content')
    <client-payment-details :payment="{{ $payment }}"></client-payment-details>
@endsection