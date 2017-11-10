@extends('layouts.app')

@section('title', 'Create Shift')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('business.reports.shifts') }}">Shift History</a></li>
    <li class="breadcrumb-item active">Create a Manual Shift</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <business-shift
                :activities="{{ $activities OR '[]' }}"
                :caregivers="{{ $caregivers OR '[]' }}"
                :clients="{{ $clients OR '[]' }}"
            ></business-shift>
        </div>
    </div>
@endsection
