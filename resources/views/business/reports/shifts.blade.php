@extends('layouts.app')

@section('title', 'Shift History')

@section('breadcrumbs')
    Only Confirmed Shifts will be charged and paid.
@endsection

@section('content')
    @if(is_admin_now())
        <admin-business-select :business="{{ $active_business OR '{}' }}"></admin-business-select>
    @endif
    @if($active_business)
        <?php $imports = is_admin() ? \App\Import::orderBy('id', 'DESC')->get()->toArray() : [] ?>
        <business-shift-report :admin="{{ (int) is_admin() }}" :imports="{{ json_encode($imports) }}" :autoload="{{ (int) request('autoload', 1) }}"></business-shift-report>
        {{--<business-shift-history :shifts="{{ $shifts }}"></business-shift-history>--}}
    @endif
@endsection
