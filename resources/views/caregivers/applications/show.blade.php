@extends('layouts.app')

@section('title', 'View Application')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('business.caregivers.applications') }}">Caregiver Applications</a></li>
    <li class="breadcrumb-item active">View</li>
@endsection

@section('content')
    <caregiver-application :application="{{ json_encode($application) }}">
    </caregiver-application>
@endsection