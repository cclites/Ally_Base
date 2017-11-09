@extends('layouts.app')

@section('title', 'Payment History')

@section('content')
    <client-payment-history :client="{{ $client }}"></client-payment-history>
@endsection