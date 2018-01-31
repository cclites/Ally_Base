@extends('layouts.errors')

@section('content')
    <h1>Whoops! We couldn't find the page you were looking for.</h1>
    <button type="button" onclick="window.history.back()" class="btn btn-lg btn-primary" style="margin-right: 15px">Go Back</button>
    <a href="/" class="btn btn-lg btn-warning">Return to Home Page</a>
@endsection