@extends('layouts.app')

@section('title', 'Payment History')

@section('breadcrumbs')
    Showing all client charges.
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <business-payment-history :payments="{{ $payments }}"></business-payment-history>
        </div>
    </div>
@endsection