@extends('layouts.app')

@section('title', 'Schedule')

@section('content')
    @if(isset($_GET['clocked_out']))
        <div class="row">
            <div class="col-lg-6">
                <div class="alert alert-success" role="alert">
                    <strong>Clocked out.</strong> You have successfully clocked out.
                </div>
            </div>
        </div>
    @endif

    <caregiver-schedule></caregiver-schedule>
@endsection