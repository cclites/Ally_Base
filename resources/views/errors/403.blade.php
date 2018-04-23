@extends('layouts.errors')

@section('title', 'Access Forbidden')

@push('head')
    <style>
        .btn {
            margin-right: 15px;
        }
    </style>
@endpush

@section('content')
    <h1>Whoops! You don't have access to that.</h1>
    <button type="button" onclick="window.history.back()" class="btn btn-lg btn-primary">Go Back</button>
    <a href="/" class="btn btn-lg btn-warning">Return to Home Page</a>
    <a href="/logout" class="btn btn-lg btn-danger">Logout</a>
@endsection
