@extends('layouts.app')

@section('title', 'Text Message Details')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item">Communication</li>
    <li class="breadcrumb-item active"><a href="/business/communication/sms-threads">Sent Texts</a></li>
    <li class="breadcrumb-item active">Text Message Details</li>
@endsection

@section('content')
    <business-sms-thread :thread="{{ $thread }}" />
@endsection
