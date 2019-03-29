@extends('layouts.app')

@section('title', $notification->title)

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('business.notifications.index') }}">Notifications</a></li>
    <li class="breadcrumb-item active">{{ $notification->title }}</li>
@endsection

@section('content')
    <business-notification :notification="{{ $notification }}" :acknowledger="{{ $notification->acknowledger OR 'null' }}"></business-notification>
@endsection