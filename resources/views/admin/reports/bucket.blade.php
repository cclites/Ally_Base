@extends('layouts.app')

@section('title', 'Bank Report')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active">Bank Report</li>
@endsection

@section('content')
    <admin-bucket-report>
    </admin-bucket-report>
@endsection