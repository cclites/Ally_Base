@extends('layouts.app')

@section('title', 'Payment History')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/business/reports">Reports</a></li>
    <li class="breadcrumb-item active">Payment History</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <business-payment-history :payments="{{ $payments }}"></business-payment-history>
        </div>
    </div>
@endsection