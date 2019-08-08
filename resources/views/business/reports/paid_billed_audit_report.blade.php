@extends('layouts.app')

@section('title', 'Payment History')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/business/reports">Reports</a></li>
    <li class="breadcrumb-item active">Paid Billed Audit Report</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <paid-billed-audit-report></paid-billed-audit-report>
        </div>
    </div>
@endsection