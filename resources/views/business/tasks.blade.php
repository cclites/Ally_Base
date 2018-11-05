@extends('layouts.app')

@section('title', 'Tasks')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active">Tasks</li>
@endsection

@section('content')
    <business-task-list :office-users="{{ $users }}" :caregivers="{{ $caregivers }}" />
@endsection
