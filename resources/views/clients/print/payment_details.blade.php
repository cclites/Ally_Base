@extends('layouts.print')

@section('title', 'Payment Details - Print')

@section('content')
    <client-payment-details-print :payment="{{ $payment }}"></client-payment-details-print>
@endsection