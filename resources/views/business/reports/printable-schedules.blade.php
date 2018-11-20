@extends('layouts.app')

@section('title', 'Printable Schedules')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/business/reports">Reports</a></li>
    <li class="breadcrumb-item active">Printable Schedules</li>
@endsection

@section('content')
    <business-printable-schedules token="{{ csrf_token() }}"></business-printable-schedules>
@endsection