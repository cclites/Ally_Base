@extends('layouts.app')

@section('title', 'Shared Shifts Report')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active">Shared Shifts Report</li>
@endsection

@section('content')
    <admin-shared-shifts-report></admin-shared-shifts-report>
@endsection
