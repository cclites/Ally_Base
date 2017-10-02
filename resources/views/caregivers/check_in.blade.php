@extends('layouts.app')

@section('title', 'Check In')

@section('content')
    @if(isset($_GET['checked_out']))
        <div class="row">
            <div class="col-lg-6">
                <div class="alert alert-success" role="alert">
                    <strong>Checked out.</strong> You have successfully checked out.
                </div>
            </div>
        </div>
    @endif
    <div class="row">
        <div class="col-lg-6">
            <check-in></check-in>
        </div>
    </div>
@endsection