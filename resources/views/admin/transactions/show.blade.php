@extends('layouts.app')

@section('title', 'Transaction Details')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.transactions') }}">Transaction Report</a></li>
    <li class="breadcrumb-item active">Transaction Details</li>
@endsection

@section('content')
    <admin-transaction :transaction="{{ $transaction }}" :user="{{ $user or '{}' }}" user-type="{{ $userType }}"></admin-transaction>
@endsection