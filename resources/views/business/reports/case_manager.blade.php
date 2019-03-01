@extends('layouts.app')

@section('title', 'Case Managers Report')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/business/reports">Reports</a></li>
    <li class="breadcrumb-item active">Case Managers Report</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <case-manager-report :case-managers="{{ $caseManagers }}" :clients="{{ $clients }}"></case-manager-report>
        </div>
    </div>
@endsection