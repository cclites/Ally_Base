@extends('layouts.app')

@section('title', 'Deposit Report')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active">Deposit Report</li>
@endsection

@section('content')
    <admin-deposit-report></admin-deposit-report>
@endsection