@extends('layouts.app')

@section('title', 'Check Out')

@section('content')
    <div class="row">
        <div class="col-lg-6">
            <div class="alert alert-success" role="alert">
                <strong>Checked in.</strong> You are currently checked in.  Check out below.
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-6">
            <check-out></check-out>
        </div>
    </div>
@endsection