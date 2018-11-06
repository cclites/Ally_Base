@extends('layouts.app')

@section('title', $client->name())

@section('avatar')
    <user-avatar src="{{ $client->avatar }}" title="{{ $client->name() }}" size="50"></user-avatar>
@endsection

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('business.clients.index') }}">Clients</a></li>
    <li class="breadcrumb-item"><a href="{{ route('business.clients.show', ['client' => $client->id]) }}">{{ $client->name }}</a></li>
    <li class="breadcrumb-item active">Onboarding</li>
@endsection

@section('content')
    <client-onboarding-wizard :client-data="{{ $clientData }}" :activities="{{ $activities }}" :onboarding-data="{{ json_encode($onboarding) }}"></client-onboarding-wizard>
@endsection

