@extends('layouts.app')

@section('title', 'Claims Report')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/business/reports">Reports</a></li>
    <li class="breadcrumb-item active">Claims Report</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <ltci-claims-report token="{{ csrf_token() }}"></ltci-claims-report>
        </div>
    </div>
@endsection