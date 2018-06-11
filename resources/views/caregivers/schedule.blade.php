@extends('layouts.app')

@section('title', 'Schedule')

@push('head')
    <style> .large-checkbox { zoom: 1.25; } </style>
@endpush

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

    <div class="row">
        <div class="col-lg-12">

            <h2>Checkbox Test</h2>
            <div class="form-group">
                <label class="custom-control custom-checkbox"  style="clear: left; float: left;">
                    <input type="checkbox" class="custom-control-input">
                    <span class="custom-control-indicator"></span>
                    <span class="custom-control-description">Bootstrap Checkbox</span>
                </label>
            </div>
            <div class="form-group">
                <label  style="clear: left; float: left;">
                    <input type="checkbox" class="large-checkbox">
                    <span class="custom-control-description">Zoomed Checkbox</span>
                </label>
            </div>
            <div class="form-group">
                <label  style="clear: left; float: left;">
                    <input type="checkbox">
                    <span class="custom-control-description">Default Checkbox</span>
                </label>
            </div>

        </div>
    </div>

    <caregiver-schedule></caregiver-schedule>
@endsection