@extends('layouts.app')

@section('title', 'Schedule')

@section('content')
    <business-schedule :business="{{ $active_business OR '{}' }}">
    </business-schedule>
@endsection
