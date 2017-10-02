@extends('layouts.app')

@section('title', 'Caregiver List')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active">Caregivers</li>
@endsection

@section('content')
    <caregiver-list :caregivers="{{ $caregivers }}"></caregiver-list>
@endsection