@extends('layouts.app')

@section('title', 'Unconfirmed Shifts')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active">Unconfirmed Shifts</li>
@endsection

@section('content')
    <client-unconfirmed-shifts :shifts="{{ $shifts }}" :activities="{{ $activities }}" />
@endsection