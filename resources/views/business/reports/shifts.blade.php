@extends('layouts.app')

@section('title', 'Shift Report')

@section('content')
    <business-shift-report></business-shift-report>
    {{--<business-shift-history :shifts="{{ $shifts }}"></business-shift-history>--}}
@endsection