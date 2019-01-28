@extends('layouts.app')

@section('title', 'Revenue')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/business/reports">Reports</a></li>
    <li class="breadcrumb-item active">Revenue</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <revenue-report :client-options="{{ $clients }}" :caregiver-options="{{ $caregivers }}" :client-types="{{ $clientTypes }}"></revenue-report>
        </div>
    </div>
@endsection
