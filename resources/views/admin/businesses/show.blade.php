@extends('layouts.app')

@section('title', $business->name)

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.businesses.index') }}">Businesses</a></li>
    <li class="breadcrumb-item active">{{ $business->name }}</li>
@endsection

@section('content')
    <!-- Nav tabs -->
    <ul class="nav nav-pills with-padding-bottom hidden-lg-down" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" data-toggle="tab" href="#settings" role="tab">Settings</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#users" role="tab">Users</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#point_of_contact" role="tab">Main Point of Contact</a>
        </li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane active" id="settings" role="tabpanel">
            <div class="row">
                <div class="col-lg-12">
                    <business-edit :business="{{ $business OR '{}' }}"></business-edit>
                </div>
            </div>
        </div>

        <div class="tab-pane" id="users" role="tabpanel">
            <div class="row">
                <div class="col-lg-12">
                    <business-office-user-list :business="{{ $business OR '{}' }}"></business-office-user-list>
                </div>
            </div>
        </div>

        <div class="tab-pane" id="point_of_contact" role="tabpanel">
            <div class="row">
                <div class="col-lg-6">
                    <business-contact-info-tab :business="{{ $business }}"></business-contact-info-tab>
                </div>
            </div>
        </div>

    </div>
@endsection