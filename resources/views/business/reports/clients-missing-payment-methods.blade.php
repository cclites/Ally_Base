@extends('layouts.app')

@section('title', 'Clients Missing Payment Methods')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/business/reports">Reports</a></li>
    <li class="breadcrumb-item active">Clients Missing Payment Methods</li>
@endsection

@section('content')
    <business-clients-missing-payment-methods-report :clients="{{ $clients }}" />
@endsection