@extends('layouts.app')

@section('title', 'Add a New Caregiver')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('business.caregivers.index') }}">Caregivers</a></li>
    <li class="breadcrumb-item active">Add Caregiver</li>
@endsection

@section('content')

    <div class="row">

        <div class="col-lg-12">

            {{-- <caregiver-create></caregiver-create> --}}
        </div>
    </div>
@endsection
