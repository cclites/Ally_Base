@extends('layouts.app')

@section('title', 'Schedule')

@section('content')
    <business-schedule default-view="{{ $business->calendar_default_view OR 'month' }}"></business-schedule>
@endsection