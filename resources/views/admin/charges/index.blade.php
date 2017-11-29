@extends('layouts.app')

@section('title', 'Charges Report')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active">Charges Report</li>
@endsection

@section('content')
    <admin-charges-report></admin-charges-report>
@endsection