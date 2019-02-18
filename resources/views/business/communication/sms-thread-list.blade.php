@extends('layouts.app')

@section('title', 'Sent Text History')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item">Communication</li>
    <li class="breadcrumb-item active">Sent Texts</li>
@endsection

@section('content')
    <business-sms-thread-list />
@endsection
