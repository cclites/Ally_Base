@extends('layouts.app')

@section('title', 'Edit Application')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('business.caregivers.applications') }}">Caregiver Applications</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
    <caregiver-application-edit :application="{{ json_encode($application) }}"
                                :business="{{ json_encode($business) }}"></caregiver-application-edit>
@endsection