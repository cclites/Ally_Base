@extends('layouts.app')

@section('title', 'Avery Labels Report')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/business/reports">Reports</a></li>
    <li class="breadcrumb-item active">Avery Labels Report</li>
@endsection

@section('content')
    <avery-labels-report />
@endsection
