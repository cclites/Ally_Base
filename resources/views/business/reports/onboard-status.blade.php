@extends('layouts.app')

@section('title', 'Onboarded Status Report')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/business/reports">Reports</a></li>
    <li class="breadcrumb-item active">Onboarded Status Report</li>
@endsection

@section('content')
    <onboard-status-report type="{{ $type }}" />
@endsection
