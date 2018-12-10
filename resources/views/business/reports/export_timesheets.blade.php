@extends('layouts.app')

@section('title', 'Export Timesheets')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/business/reports">Reports</a></li>
    <li class="breadcrumb-item active">Export Timesheets</li>
@endsection

@section('content')
    @foreach($errors->all() as $message)
        <div class="alert alert-danger alert-dismissible"><strong>Error:</strong> {{ $message }}</div>
    @endforeach

    <div class="row">
        <div class="col-lg-12">
            <business-export-timesheets :clients="{{ $clients }}" :caregivers="{{ $caregivers }}" token="{{ csrf_token() }}"></business-export-timesheets>
        </div>
    </div>
@endsection