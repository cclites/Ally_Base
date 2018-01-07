@extends('layouts.app')

@section('title', 'Unsettled Report')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active">Unsettled Report</li>
@endsection

@section('content')
    <admin-unsettled-report
        start_at="{{ request('start_date', '10/01/2017') }}"
        end_at="{{ request('end_date') }}"
        client_id="{{ request('client_id') }}"
        business_id="{{ request('business_id') }}"
        caregiver_id="{{ request('caregiver_id') }}"
        :selected_statuses="{{ json_encode(request('status', [
                \App\Shift::WAITING_FOR_AUTHORIZATION,
                \App\Shift::WAITING_FOR_CHARGE,
            ])) }}"
    >
    </admin-unsettled-report>
@endsection