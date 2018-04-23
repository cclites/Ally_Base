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
    <h1>Whoops! We couldn't find the page you were looking for.</h1>
    <button type="button" onclick="window.history.back()" class="btn btn-lg btn-primary">Go Back</button>
    <a href="/" class="btn btn-lg btn-warning">Return to Home Page</a>
    <a href="/logout" class="btn btn-lg btn-danger">Logout</a>
@endsection