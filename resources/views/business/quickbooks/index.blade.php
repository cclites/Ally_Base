@extends('layouts.app')

@section('title', 'Quickbooks')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active">Quickbooks</li>
@endsection

@section('content')
    <quickbooks :clients="{{ $clients }}" :caregivers="{{ $caregivers }}"></quickbooks>
@endsection