@extends('layouts.app')

@section('title', 'Audit Log')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active">Audit Log</li>
@endsection

@section('content')
    <admin-audit-log></admin-audit-log>
@endsection