@extends('layouts.app')

@section('title', 'Deposit Report')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active">Failed Deposit Report</li>
@endsection

@section('content')
    <admin-failed-deposit-report></admin-failed-deposit-report>
@endsection