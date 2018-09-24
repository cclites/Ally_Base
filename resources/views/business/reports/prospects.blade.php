@extends('layouts.app')

@section('title', 'Prospects')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/business/reports">Reports</a></li>
    <li class="breadcrumb-item active">Prospects</li>
@endsection

@section('content')
    <prospects-report />
@endsection