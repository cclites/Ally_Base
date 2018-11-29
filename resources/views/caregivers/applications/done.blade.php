@extends('layouts.blank')

@section('title', 'Application Submitted')

@section('content')
    <center style="padding-top: 20%">
        <h2>Application submitted</h2>
        <p>
            Your application has been submitted to {{ $businessChain->name }}.  Thank you!
        </p>
        <p>
            For your reference, your application ID is #{{ $application->id }}
        </p>
        <p>
            You may now close this window.
        </p>
    </center>
@endsection