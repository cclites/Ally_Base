@extends('layouts.errors')

@section('content')
    <h1>Whoops! The system said you tried to submit invalid data.  Please try again.</h1>
    <button type="button" onclick="window.history.back()" class="btn btn-lg btn-primary" style="margin-right: 15px">Go Back</button>
    <a href="/" class="btn btn-lg btn-warning">Return to Home Page</a>
@endsection
