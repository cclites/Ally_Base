@extends('layouts.app')

@section('title', 'Pending Transactions Report')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active">Pending Transactions Report</li>
@endsection

@section('content')
    <admin-pending-transactions-report></admin-pending-transactions-report>
@endsection
