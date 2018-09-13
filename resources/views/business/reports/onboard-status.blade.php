@extends('layouts.app')

@section('title', 'Onboarded Status Report')

@section('content')
    <onboard-status-report type="{{ $type }}" />
@endsection
