@extends('layouts.app')

@section('title', 'Manual Timesheet')

@section('content')
    <business-timesheet 
        :activities="{{ $activities OR '[]' }}"
        :timesheet="{{ $timesheet OR '{}' }}"
    ></business-timesheet>
@endsection
