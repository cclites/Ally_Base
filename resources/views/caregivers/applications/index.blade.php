@extends('layouts.app')

@section('title', 'Caregiver Applications')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active">Caregiver Applications</li>
@endsection

@section('content')
    <caregiver-application-list application-url="{{ $applicationUrl }}"></caregiver-application-list>
@endsection