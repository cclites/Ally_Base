@extends('layouts.app')

@section('title', 'Submit Timesheet')

@section('content')

    @if($success)
    
    <b-card header="Timesheet Awaiting Approval"
            border-variant="info"
            header-bg-variant="info"
            header-text-variant="white">
            
            Your timesheet has been submitted for approval.
    </b-card>
    @else
    <caregiver-timesheet 
        :cg="{{ $caregiver }}" 
        :activities="{{ $activities ?? '[]' }}"
        :caregivers="{{ $caregivers ?? '[]' }}"
    ></caregiver-timesheet>
    @endif

@endsection
