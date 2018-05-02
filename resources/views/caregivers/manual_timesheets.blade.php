@extends('layouts.app')

@section('title', 'Manual Timesheets')

@section('content')
    <caregiver-manual-timesheets :caregiver="{{ auth()->user() }}"></caregiver-manual-timesheets>
@endsection