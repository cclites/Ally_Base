@extends('layouts.app')

@section('title', 'Sales Pipeline Report')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/business/reports">Reports</a></li>
    <li class="breadcrumb-item active">Sales Piepeline Report</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <sales-pipeline-report></sales-pipeline-report>
        </div>
    </div>
@endsection