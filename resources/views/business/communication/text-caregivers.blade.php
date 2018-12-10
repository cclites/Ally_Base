@extends('layouts.app')

@section('title', 'SMS Caregivers')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item">Communication</li>
    <li class="breadcrumb-item active">Text Caregivers</li>
@endsection

@section('content')
    <business-text-caregivers
        fill-message="{{ $message }}"
        :fill-recipients="{{ $recipients ?? '[]' }}"
    />
@endsection
