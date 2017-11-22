@extends('layouts.app')

@section('title', 'Transactions Report')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active">Transactions Report</li>
@endsection

@section('content')
    <admin-transactions-report></admin-transactions-report>
@endsection