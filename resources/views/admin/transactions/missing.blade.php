@extends('layouts.app')

@section('title', 'Missing Transactions')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item">Missing Transactions</li>
@endsection

@section('content')
    <admin-missing-transactions></admin-missing-transactions>
@endsection