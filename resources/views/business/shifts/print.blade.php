@extends('layouts.print')

@section('title')
    Shift Details - Print
@endsection

@push('head')
    <style>
        .col-sm-6 {
            float: left;
            width: 50%;
        }
    </style>
@endpush

@section('content')
    @include('layouts.partials.print_logo')
    @include('business.shifts.print_details', ['report_type' => 'full'])
@endsection