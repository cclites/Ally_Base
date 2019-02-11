@extends('layouts.app')

@section('title', 'Caregiver Expirations')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/business/reports">Reports</a></li>
    <li class="breadcrumb-item active">Caregiver Expirations</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <business-certification-expirations :certifications="{{ $certifications OR '[]' }}">
            </business-certification-expirations>
        </div>
    </div>
@endsection