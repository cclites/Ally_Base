@extends('layouts.app')

@section('title', $type . ' Birthday Report')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/business/reports">Reports</a></li>
    <li class="breadcrumb-item active">{{ $type }} Birthday Report</li>
@endsection

@section('content')
    <user-birthday-report type="{{ $type }}" :client-types="{{ $clientTypes }}"/>
@endsection
