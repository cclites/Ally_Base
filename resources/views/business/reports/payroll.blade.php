@extends('layouts.app')

@section('title', 'Payroll Report')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/business/reports">Reports</a></li>
    <li class="breadcrumb-item active">Payroll Report</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <business-payroll-report :caregivers="{{ $caregivers }}" />
        </div>
    </div>
@endsection