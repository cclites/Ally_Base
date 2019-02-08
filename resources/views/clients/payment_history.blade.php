@extends('layouts.app')

@section('title', 'Payment History')

@section('content')
    <b-card title="Payment History">
        <client-payment-history :client="{{ $client }}" :payments="{{ $payments }}"></client-payment-history>
    </b-card>
@endsection