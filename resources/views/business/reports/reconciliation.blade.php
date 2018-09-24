@extends('layouts.app')

@section('title', 'Reconciliation Report')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/business/reports">Reports</a></li>
    <li class="breadcrumb-item active">Reconciliation Report</li>
@endsection

@section('content')
    <business-reconciliation-report></business-reconciliation-report>
@endsection