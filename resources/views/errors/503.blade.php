@extends('layouts.errors')

@section('content')
    <h2>We couldn't wait</h2>
    <h4>We're rolling out something exciting!</h4>
    <h1>The system will be back online in a minute!</h1>
    <button onclick="window.location.reload(true)" class="btn btn-lg btn-warning with-padding-top" type="button">Refresh Now</button>
@endsection