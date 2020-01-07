@extends('layouts.app')

@section('title', 'Unsettled Report')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active">Unsettled Report</li>
@endsection

@section('content')
    <admin-unsettled-report
        start_at="{{ request('start_date') }}"
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

{{--

    SAMPLE LINK leading to this view:
    
    
    <a class="my-report-link" 
       href="{{ 
            route('admin.reports.unsettled', $params = [
                'caregiver_id' => 2,
                'status'=> [
                    \App\Shift::PAID_NOT_CHARGED,
                    \App\Shift::PAID_BUSINESS_ONLY,
                ]
            ]) 
       }}" 
    >
       View Unsettled Shifts
    </a>
    <!-- 
        To Generate query-string like: 
            ?caregiver_id=2&status[]=PAID_NOT_CHARGED&status[]=PAID_BUSINESS_ONLY 
    -->
    
--}}
@endsection