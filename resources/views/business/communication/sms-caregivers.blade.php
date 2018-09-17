@extends('layouts.app')

@section('title', 'SMS Caregivers')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item">Communication</li>
    <li class="breadcrumb-item active">SMS Caregivers</li>
@endsection

@section('content')
    <business-sms-caregivers />
@endsection
