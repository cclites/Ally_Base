@extends('layouts.app')

@section('title', 'Submit Timesheet')

@section('content')
    <caregiver-timesheet 
        :cg="{{ $caregiver }}" 
        :activities="{{ $activities OR '[]' }}"
        :caregivers="{{ $caregivers OR '[]' }}"
    ></caregiver-timesheet>
@endsection