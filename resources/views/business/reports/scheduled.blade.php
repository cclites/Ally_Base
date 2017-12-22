@extends('layouts.app')

@section('title', 'Scheduled Payments')

@section('content')
    <business-scheduled-payments
            :payments="{{ $payments }}"
            :dates="{{ json_encode($dates) }}"
            :caregivers="{{ json_encode($caregivers) }}"
            :clients="{{ json_encode($clients) }}"
            :totals="{{ json_encode($totals) }}">
    </business-scheduled-payments>
@endsection