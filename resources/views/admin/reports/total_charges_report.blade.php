@extends('layouts.app')

@section('title', 'Total Charges Report')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active">Total Charges Report</li>
@endsection

@section('content')
    <total-charges-report></total-charges-report>
@endsection
