@extends('layouts.app')

@section('title', 'EVV Report')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active">EVV Report</li>
@endsection

@section('content')
    <admin-evv-report>
    </admin-evv-report>
@endsection