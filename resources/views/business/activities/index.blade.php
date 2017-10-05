@extends('layouts.app')

@section('title', 'Activity List')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active">Activities</li>
@endsection

@section('content')
    <activity-list :activities="{{ $activities }}"></activity-list>
@endsection