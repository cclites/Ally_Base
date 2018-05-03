@extends('layouts.app')

@section('title', 'Manual Timesheets')

@section('content')
    <manual-timesheets 
        {{-- :caregiver="{{ $caregiver }}"  --}}
        :activities="{{ $activities OR '[]' }}"
        :caregivers="{{ $caregivers OR '[]' }}"
    ></manual-timesheets>
@endsection