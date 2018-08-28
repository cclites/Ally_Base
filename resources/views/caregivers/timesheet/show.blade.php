@extends('layouts.app')

@section('title', 'View Timesheet')

@section('content')
    <caregiver-timesheet
        :timesheet="{{ $timesheet }}"
        :cg="{{ $caregiver }}" 
        :activities="{{ $activities OR '[]' }}"
        :caregivers="{{ $caregivers OR '[]' }}"
    ></caregiver-timesheet>
@endsection
