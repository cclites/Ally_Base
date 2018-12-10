@extends('layouts.app')

@section('title', 'Provider Settings')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item">Provider Settings</li>
    <li class="breadcrumb-item active">Bank Accounts</li>
@endsection

@section('content')
    <business-bank-accounts :business="{{ $business ?? '{}' }}"></business-bank-accounts>
@endsection
