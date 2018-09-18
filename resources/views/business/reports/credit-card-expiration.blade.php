@extends('layouts.app')

@section('title', 'Credit Card Expiration')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/business/reports">Reports</a></li>
    <li class="breadcrumb-item active">Credit Card Expiration</li>
@endsection

@section('content')
    <cc-expiration-report></cc-expiration-report>
@endsection