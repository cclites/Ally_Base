@extends('layouts.app')

@section('title', 'Caregiver Statistics')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/business/reports">Reports</a></li>
    <li class="breadcrumb-item active">Caregiver Statistics</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <caregiver-stats></caregiver-stats>
        </div>
    </div>
@endsection
