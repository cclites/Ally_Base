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
            <b-card
                header="Create a Manual Shift"
                header-text-variant="white"
                header-bg-variant="info"
            >
                <business-shift
                    :activities="{{ $activities ?? '[]' }}">
                </business-shift>
            </b-card>
        </div>
    </div>
@endsection
