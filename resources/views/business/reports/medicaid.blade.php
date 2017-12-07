@extends('layouts.app')

@section('title', 'Medicaid Report')

@section('content')
    <business-medicaid-report
            :shifts="{{ $shifts }}"
            :dates="{{ json_encode($dates) }}"
            :totals="{{ json_encode($totals) }}">
    </business-medicaid-report>
@endsection