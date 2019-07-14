@extends('layouts.app')

@section('title', 'Client Referrals')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/business/reports">Reports</a></li>
    <li class="breadcrumb-item active">Client Referrals Report</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <client-referrals-report :clients="{{ $clients }}"></client-referrals-report>
        </div>
    </div>
@endsection