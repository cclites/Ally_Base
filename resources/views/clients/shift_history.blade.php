@extends('layouts.app')

@section('title', 'LTC Shift Approval')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active">Notes</li>
@endsection

@section('content')
    <ltc-shift-approval :shifts="{{ $shifts }}"
                        :week-start-date="'{{ $week_start_date }}'"
                        :week-end-date="'{{ $week_end_date }}'"
                        :verified="{{ json_encode($shifts_verified) }}"></ltc-shift-approval>
@endsection