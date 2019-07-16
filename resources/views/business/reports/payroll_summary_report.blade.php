@extends('layouts.app')

@section('title', 'Payroll Summary Report')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/business/reports">Reports</a></li>
    <li class="breadcrumb-item active">Payroll Summary Report</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <payroll-summary-report></payroll-summary-report>
        </div>
    </div>
@endsection