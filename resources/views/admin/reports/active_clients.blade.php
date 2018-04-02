@extends('layouts.app')

@section('title', 'Active Clients Report')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active">Active Clients Report</li>
@endsection

@section('content')
    <admin-active-clients-report></admin-active-clients-report>
@endsection
