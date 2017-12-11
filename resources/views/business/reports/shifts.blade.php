@extends('layouts.app')

@section('title', 'Shift History')

@section('breadcrumbs')
    Only Confirmed Shifts are included in the totals, charges, &amp; payments.
@endsection

@section('content')
    <business-shift-report></business-shift-report>
    {{--<business-shift-history :shifts="{{ $shifts }}"></business-shift-history>--}}
@endsection