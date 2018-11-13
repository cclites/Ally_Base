@extends('layouts.app')

@section('title', 'My Clients')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active">My Clients</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <caregiver-client-list :clients="{{ $clients }}"/>
        </div>
    </div>
@endsection
