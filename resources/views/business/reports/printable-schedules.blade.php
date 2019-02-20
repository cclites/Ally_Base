@extends('layouts.app')

@section('title', 'Printable Schedules')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/business/reports">Reports</a></li>
    <li class="breadcrumb-item active">Printable Schedules</li>
@endsection

@section('content')
    @foreach($errors->all() as $message)
        <div class="alert alert-danger alert-dismissible"><strong>Error:</strong> {{ $message }}</div>
    @endforeach

    <business-printable-schedules token="{{ csrf_token() }}"></business-printable-schedules>
@endsection