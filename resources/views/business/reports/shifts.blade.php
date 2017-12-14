@extends('layouts.app')

@section('title', 'Shift History')

@section('breadcrumbs')
    Only Confirmed Shifts will be charged and paid.
@endsection

@section('content')
    <business-shift-report></business-shift-report>
    {{--<business-shift-history :shifts="{{ $shifts }}"></business-shift-history>--}}
@endsection