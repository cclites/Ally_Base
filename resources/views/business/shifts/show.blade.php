@extends('layouts.app')

@section('title', 'Shift Details')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('business.reports.shifts') }}">Shift History</a></li>
    <li class="breadcrumb-item active">Shift Details</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <b-card
                header="Shift Details"
                header-text-variant="white"
                header-bg-variant="info"
            >
                <business-shift
                    :shift="{{ $shift }}"
                    :caregiver="{{ $shift->caregiver }}"
                    :client="{{ $shift->client }}"
                    :activities="{{ $activities OR '[]' }}"
                    :issues="{{ $shift->issues OR '[]' }}"
                    :admin="{{ (int) is_admin() }}"
                ></business-shift>
            </b-card>
        </div>
    </div>
@endsection
