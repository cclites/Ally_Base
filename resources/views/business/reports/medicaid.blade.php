@extends('layouts.app')

@section('title', 'Medicaid Report')

@section('content')
    <div class="row">
        <div class="col-lg-4">
            <dashboard-metric variant="info" value="${{ $hours }}" text="Total Hours" />
        </div>
        <div class="col-lg-4">
            <dashboard-metric variant="primary" value="${{ $totalAllyFee }}" text="Total Ally Fee" />
        </div>
        <div class="col-lg-4">
            <dashboard-metric variant="success" value="${{ $totalOwed }}" text="Total Owed by Provider" />
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <business-medicaid-report-caregivers :caregivers="{{ json_encode($caregivers) }}"></business-medicaid-report-caregivers>
        </div>
    </div>
@endsection