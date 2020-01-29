@extends('layouts.app')

@section('title', 'Manual Timesheet')

@section('content')
    <business-timesheet 
        :activities="{{ $activities ?? '[]' }}"
        :timesheet="{{ $timesheet ?? '{}' }}"
        :caregivers="{{ $caregivers ?? '[]' }}"
    ></business-timesheet>
@endsection
