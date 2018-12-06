@extends('layouts.app')

@section('title', 'Pending Shifts')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active">Pending Shifts</li>
@endsection

@section('content')
    <client-unconfirmed-shifts :shifts="{{ $shifts }}" :activities="{{ $activities }}" />
@endsection