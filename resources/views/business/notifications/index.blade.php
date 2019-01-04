@extends('layouts.app')

@section('title', 'System Notifications')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active">Notifications</li>
@endsection

@section('content')
    <!-- Nav tabs -->
    <ul class="nav nav-pills with-padding-bottom" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" data-toggle="tab" href="#active-notifications" role="tab">Active Notifications</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#archived-notifications" role="tab">Acknowledged Notifications</a>
        </li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane active" id="active-notifications" role="tabpanel">
            <div class="row">
                <div class="col-lg-12">
                    <business-notification-list :notifications="{{ $notifications }}" title="Active Notifications" :hide-acknowledged="1" ></business-notification-list>
                </div>
            </div>
        </div>
        <div class="tab-pane" id="archived-notifications" role="tabpanel">
            <div class="row">
                <div class="col-lg-12">
                    <business-notification-list :notifications="{{ $archived }}" title="Acknowledged Notifications"></business-notification-list>
                </div>
            </div>
        </div>
    </div>
@endsection