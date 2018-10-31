@extends('layouts.app')

@section('title', 'SMS Caregivers')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item">Communication</li>
    <li class="breadcrumb-item active">Text Caregivers</li>
@endsection

@section('content')
    @if($business->outgoing_sms_number)
        <business-text-caregivers fill-message="{{ $message }}" />
    @else
        <div class="alert alert-warning">
            Please contact Ally to enable SMS messages on your account.
        </div>
    @endif
@endsection
