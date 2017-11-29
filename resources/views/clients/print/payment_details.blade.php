@extends('layouts.print')

@section('title', 'Payment Details - Print')

@section('content')
    <client-payment-details-print :payment="{{ json_encode($payment) }}"></client-payment-details-print>
@endsection