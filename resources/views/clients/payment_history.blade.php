@extends('layouts.app')

@section('title', 'Payment History')

@section('content')
    <b-card title="Payment History">
        <client-payment-history :client="{{ $client }}"></client-payment-history>
    </b-card>
@endsection