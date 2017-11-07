@extends('layouts.app')

@section('title', 'Business Settings')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active">Business Settings</li>
@endsection

@section('content')
    <business-settings :business="{{ $business }}"></business-settings>
@endsection