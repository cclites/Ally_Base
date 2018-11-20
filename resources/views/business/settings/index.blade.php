@extends('layouts.app')

@section('title', 'Provider Settings')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active">Provider Settings</li>
@endsection

@section('content')
    <business-settings></business-settings>
@endsection