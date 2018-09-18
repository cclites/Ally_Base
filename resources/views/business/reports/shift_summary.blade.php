@extends('layouts.app')

@section('title', 'Shifts by ' . ucfirst($type))

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/business/reports">Reports</a></li>
    <li class="breadcrumb-item active">Shifts by {{ ucfirst($type) }}</li>
@endsection

@section('content')
    <shift-summary-report type="{{ $type }}" :users="{{ $users }}" />
@endsection
