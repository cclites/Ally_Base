@extends('layouts.app')

@section('title', 'Caregiver Overtime Report')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/business/reports">Reports</a></li>
    <li class="breadcrumb-item active">Caregiver Overtime Report</li>
@endsection

@section('content')
    <business-overtime-report></business-overtime-report>
@endsection