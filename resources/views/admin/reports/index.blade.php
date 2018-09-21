@extends('layouts.app')

@section('title', 'Reports')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active">Reports</li>
@endsection

@section('content')
    <report-list :role="{{ $role }}" />
@endsection
