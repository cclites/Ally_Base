@extends('layouts.app')

@section('title', 'Failed Transactions Report')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active">Failed Transactions Report</li>
@endsection

@section('content')
    <admin-failed-transactions-report></admin-failed-transactions-report>
@endsection
