@extends('layouts.app')

@section('title', 'My Clients')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/caregiver/clients">My Clients</a></li>
    <li class="breadcrumb-item active">Client Narrative</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <caregiver-client-narrative :client="{{ $client }}" />
        </div>
    </div>
@endsection
