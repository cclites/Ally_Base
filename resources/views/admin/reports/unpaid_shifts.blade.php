@extends('layouts.app')

@section('title', 'Unpaid Shifts Report')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active">Unpaid Shifts Report</li>
@endsection

@section('content')
    <admin-unpaid-shifts-report></admin-unpaid-shifts-report>
@endsection
