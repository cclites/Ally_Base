@extends('layouts.errors')

@section('title', 'Page not found')

@push('head')
    <style>
        .btn {
            margin-right: 15px;
        }
    </style>
@endpush

@section('content')
    <h1>Restricted</h1>
    <h4>This application is only permitted for caregivers.</h4>
    <a href="/logout" class="btn btn-lg btn-danger">Logout</a>
@endsection