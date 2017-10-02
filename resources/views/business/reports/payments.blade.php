@extends('layouts.app')

@section('title', 'Payment History')

@section('content')
    <div class="row">
        <div class="col-lg-4">
            <dashboard-metric variant="info" value="${{ $month_sum }}" text="This Month" />
        </div>
        <div class="col-lg-4">
            <dashboard-metric variant="primary" value="${{ $scheduled_sum }}" text="Scheduled" />
        </div>
        <div class="col-lg-4">
            <dashboard-metric variant="success" value="${{ $year_sum }}" text="Year to Date" />
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <business-payment-history :payments="{{ $payments }}"></business-payment-history>
        </div>
    </div>
@endsection