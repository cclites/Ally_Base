@extends('layouts.app')

@section('title', 'On Hold Report')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active">On Hold Report</li>
@endsection

@section('content')
    <admin-on-hold-report></admin-on-hold-report>
@endsection