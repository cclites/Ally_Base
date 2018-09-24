@extends('layouts.app')

@section('title', 'Past Timesheet')

@section('content')
    <caregiver-timesheet-list :timesheets="{{ $timesheets }}" :caregiver="{{ $caregiver }}"></caregiver-timesheet-list>
@endsection
