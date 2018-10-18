@extends('layouts.app')

@section('title', 'Sent Text History')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item">Communication</li>
    <li class="breadcrumb-item active">Sent Texts</li>
@endsection

@section('content')
    @if($business->outgoing_sms_number)
        <business-sms-thread-list :threads="{{ $threads }}" />
    @else
        <div class="alert alert-warning">
            Please contact Ally to enable SMS messages on your account.
        </div>
    @endif
@endsection
