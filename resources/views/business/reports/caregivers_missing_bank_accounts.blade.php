@extends('layouts.app')

@section('title', 'Caregivers Missing Bank Accounts')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/business/reports">Reports</a></li>
    <li class="breadcrumb-item active">Caregivers Missing Bank Accounts</li>
@endsection

@section('content')
    <caregivers-missing-bank-accounts :caregivers="{{ $caregivers }}"></caregivers-missing-bank-accounts>
@endsection