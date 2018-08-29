@extends('layouts.app')

@section('title', 'Emails Report')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active">Emails Report</li>
@endsection

@section('content')
    <admin-emails-report></admin-emails-report>
@endsection
