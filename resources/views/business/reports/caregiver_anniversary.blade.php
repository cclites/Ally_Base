@extends('layouts.app')

@section('title', 'Caregiver Anniversary Report')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/business/reports">Reports</a></li>
    <li class="breadcrumb-item active">Caregiver Anniversary Report</li>
@endsection

@section('content')
    <caregiver-anniversary-report />
@endsection
