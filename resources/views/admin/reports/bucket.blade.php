@extends('layouts.app')

@section('title', 'Bucket Report')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active">Bucket Report</li>
@endsection

@section('content')
    <admin-bucket-report>
    </admin-bucket-report>
@endsection