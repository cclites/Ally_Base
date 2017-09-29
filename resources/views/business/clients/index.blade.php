@extends('layouts.app')

@section('title', 'Client List')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active">Clients</li>
@endsection

@section('content')
    <client-list :clients="{{ $clients }}"></client-list>
@endsection