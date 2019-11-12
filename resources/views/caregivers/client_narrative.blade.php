@extends('layouts.app')

@section('title', 'My Clients')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/caregiver/clients?active=1">My Clients</a></li>
    <li class="breadcrumb-item active">Client Narrative</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <b-card>
                <div class="client-details mb-4">
                    <h1>Client: {{ $client->name }}</h1>
                </div>

                <client-narrative :client="{{ $client }}" mode="caregiver" />
            </b-card>
        </div>
    </div>
@endsection
