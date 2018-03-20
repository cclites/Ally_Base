@extends('layouts.app')

@section('title', 'Shift History')

@section('breadcrumbs')
    Only Confirmed Shifts will be charged and paid.
@endsection

@section('content')
    <?php $imports = is_admin() ? \App\Import::orderBy('id', 'DESC')->get()->toArray() : [] ?>
    <business-shift-report :admin="{{ (int) is_admin() }}" :imports="{{ json_encode($imports) }}"></business-shift-report>
    {{--<business-shift-history :shifts="{{ $shifts }}"></business-shift-history>--}}
@endsection
