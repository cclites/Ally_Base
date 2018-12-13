@extends('layouts.app')

@section('title', 'Other Text Message Replies')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item">Communication</li>
    <li class="breadcrumb-item active"><a href="/business/communication/sms-threads">Sent Texts</a></li>
    <li class="breadcrumb-item active">Other Text Message Replies</li>
@endsection

@section('content')
    <b-card
        header="Other Text Message Replies"
        header-text-variant="white"
        header-bg-variant="info"
    >
        <business-sms-reply-table :replies="{{ $replies }}" />
    </b-card>
@endsection
