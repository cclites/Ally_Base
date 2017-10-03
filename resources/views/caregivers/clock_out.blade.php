@extends('layouts.app')

@section('title', 'Clock Out')

@section('content')
    <h4>You are currently clocked in.</h4>
    <div class="row">
        <div class="col-lg-6">
            <clock-out :shift="{{ $shift }}"></clock-out>
        </div>
        <div class="col-lg-6">

        </div>
    </div>
@endsection