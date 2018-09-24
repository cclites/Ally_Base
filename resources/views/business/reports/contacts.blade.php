@extends('layouts.app')

@section('title', 'Contacts Report')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/business/reports">Reports</a></li>
    <li class="breadcrumb-item active">Contacts Report</li>
@endsection

@section('content')
    <contacts-report type="{{ $type }}" />
@endsection
