@extends('layouts.app')

@section('title', 'View Timesheet')

@section('content')
    <caregiver-timesheet
        :timesheet="{{ $timesheet }}"
        :cg="{{ $caregiver }}" 
        :activities="{{ $activities ?? '[]' }}"
        :caregivers="{{ $caregivers ?? '[]' }}"
    ></caregiver-timesheet>
@endsection
