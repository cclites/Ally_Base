@extends('layouts.app')

@section('title', 'Payment History')

@section('content')
    <client-payment-history :client="{{ $client }}" :payments="{{ $payments }}"></client-payment-history>
@endsection