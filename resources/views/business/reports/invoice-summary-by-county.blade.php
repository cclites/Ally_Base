@extends('layouts.app')

@section('title', 'Invoice Summary By County Report')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/business/reports">Reports</a></li>
    <li class="breadcrumb-item active">Invoice Summary By County Report</li>
@endsection

@section('content')
    <invoice-summary-by-county></invoice-summary-by-county>
@endsection